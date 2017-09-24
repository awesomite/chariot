<?php

namespace Awesomite\Chariot\Pattern;

use Awesomite\Chariot\Exceptions\HttpException;
use Awesomite\Chariot\Exceptions\InvalidArgumentException;
use Awesomite\Chariot\ExportableTrait;
use Awesomite\Chariot\HttpMethods;
use Awesomite\Chariot\InternalRoute;
use Awesomite\Chariot\InternalRouteInterface;
use Awesomite\Chariot\LinkInterface;
use Awesomite\Chariot\RouterInterface;

class PatternRouter implements RouterInterface
{
    const STRATEGY_SEQUENTIALLY = 1;
    const STRATEGY_TREE         = 2;

    use ExportableTrait;

    /**
     * Example:
     * list($patternRoute, $extraParams) = $routes['GET'][$handler][0];
     *
     * @var array
     */
    private $routes = [];

    /**
     * Example:
     * list($handler, $extraParams) = $routes['GET']['/contact'];
     *
     * @var array
     */
    private $keyValueRoutes = [];

    private $nodesTree = [];

    private $strategy;

    /**
     * @var PatternsInterface
     */
    private $patterns;

    public function __construct(PatternsInterface $patterns, int $strategy = self::STRATEGY_TREE)
    {
        $this->patterns = $patterns;
        if (!in_array($strategy, [static::STRATEGY_TREE, static::STRATEGY_SEQUENTIALLY], true)) {
            throw new InvalidArgumentException("Invalid strategy: {$strategy}");
        }
        $this->strategy = $strategy;
    }

    public static function createDefault()
    {
        return new static(Patterns::createDefault());
    }

    public function getPatterns(): PatternsInterface
    {
        return $this->patterns;
    }

    public function addRoute(string $method, string $pattern, string $handler, array $extraParams = []): PatternRouter
    {
        $this->processExtraParams($extraParams);

        if (!in_array($method, HttpMethods::ALL_METHODS, true)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Method is equal to %s, but must be equal to one of the following: %s',
                    $method,
                    implode(', ', HttpMethods::ALL_METHODS)
                )
            );
        }

        $route = new PatternRoute($pattern, $this->patterns);
        $this->routes[$method][$handler][] = [$route, $extraParams];

        if (false === strpos($pattern, '{{')) {
            $this->keyValueRoutes[$method][$pattern] = [$handler, $extraParams];
        } elseif ($this->strategy === static::STRATEGY_TREE) {
            $currentNode = &$this->nodesTree;
            foreach ($route->getNodes() as $node) {
                if ($node->isRegex()) {
                    break;
                }
                if (!isset($currentNode[$node->getKey()])) {
                    $currentNode[$node->getKey()] = [];
                }
                $currentNode = &$currentNode[$node->getKey()];
            }

            /** @var PatternRouteNode $node */
            $lastKey = $node->isRegex() ? 'regex' : 'all';
            $currentNode[$lastKey][] = [
                $route,
                $handler,
                $extraParams,
                $method,
            ];
        }

        return $this;
    }

    public function any(string $pattern, string $handler, array $extraParams = []): PatternRouter
    {
        return $this->addRoute(HttpMethods::METHOD_ANY, $pattern, $handler, $extraParams);
    }

    public function get(string $pattern, string $handler, array $extraParams = []): PatternRouter
    {
        return $this->addRoute(HttpMethods::METHOD_GET, $pattern, $handler, $extraParams);
    }

    public function post(string $pattern, string $handler, array $extraParams = []): PatternRouter
    {
        return $this->addRoute(HttpMethods::METHOD_POST, $pattern, $handler, $extraParams);
    }

    public function put(string $pattern, string $handler, array $extraParams = []): PatternRouter
    {
        return $this->addRoute(HttpMethods::METHOD_PUT, $pattern, $handler, $extraParams);
    }

    public function delete(string $pattern, string $handler, array $extraParams = []): PatternRouter
    {
        return $this->addRoute(HttpMethods::METHOD_DELETE, $pattern, $handler, $extraParams);
    }

    public function patch(string $pattern, string $handler, array $extraParams = []): PatternRouter
    {
        return $this->addRoute(HttpMethods::METHOD_PATCH, $pattern, $handler, $extraParams);
    }

    public function connect(string $pattern, string $handler, array $extraParams = []): PatternRouter
    {
        return $this->addRoute(HttpMethods::METHOD_CONNECT, $pattern, $handler, $extraParams);
    }

    public function options(string $pattern, string $handler, array $extraParams = []): PatternRouter
    {
        return $this->addRoute(HttpMethods::METHOD_OPTIONS, $pattern, $handler, $extraParams);
    }

    public function trace(string $pattern, string $handler, array $extraParams = []): PatternRouter
    {
        return $this->addRoute(HttpMethods::METHOD_TRACE, $pattern, $handler, $extraParams);
    }

    public function match(string $method, string $path): InternalRouteInterface
    {
        switch ($this->strategy) {
            case static::STRATEGY_SEQUENTIALLY:
                return $this->matchSequentially($method, $path);

            case static::STRATEGY_TREE:
                return $this->matchTree($method, $path);
        }
        // @codeCoverageIgnoreStart
    }

    // @codeCoverageIgnoreEnd

    public function getAllowedMethods(string $url): array
    {
        $allRealMethods = array_diff(HttpMethods::ALL_METHODS, [HttpMethods::METHOD_ANY]);


        switch ($this->strategy) {
            case static::STRATEGY_SEQUENTIALLY:
                if ($this->matchSequentiallyForMethods([HttpMethods::METHOD_ANY], $url)) {
                    return $allRealMethods;
                }
                break;

            case static::STRATEGY_TREE:
                if ($this->matchTreeForMethods([HttpMethods::METHOD_ANY], $url)) {
                    return $allRealMethods;
                }
                break;
        }

        $result = [];
        foreach (array_diff($allRealMethods, [HttpMethods::METHOD_HEAD]) as $method) {
            switch ($this->strategy) {
                case static::STRATEGY_SEQUENTIALLY:
                    if ($this->matchSequentiallyForMethods([$method], $url)) {
                        $result[] = $method;
                        if (HttpMethods::METHOD_GET === $method) {
                            $result[] = HttpMethods::METHOD_HEAD;
                        }
                    }
                    break;

                case static::STRATEGY_TREE:
                    if ($this->matchTreeForMethods([$method], $url)) {
                        $result[] = $method;
                        if (HttpMethods::METHOD_GET === $method) {
                            $result[] = HttpMethods::METHOD_HEAD;
                        }
                    }
                    break;
            }
        }

        return $result;
    }

    /**
     * @param string $path
     * @param array  $methods
     *
     * @return InternalRouteInterface|null
     */
    private function matchKeyValue(string $path, array $methods)
    {
        foreach ($methods as $method) {
            $keyValue = $this->keyValueRoutes[$method][$path] ?? null;
            if (!is_null($keyValue)) {
                list($handler, $extraParams) = $keyValue;

                return new InternalRoute($handler, $extraParams);
            }
        }

        return null;
    }

    private function methodMapping(string $method): array
    {
        switch ($method) {
            case HttpMethods::METHOD_ANY:
                return HttpMethods::ALL_METHODS;

            case HttpMethods::METHOD_HEAD:
                return [
                    HttpMethods::METHOD_ANY,
                    HttpMethods::METHOD_GET,
                    HttpMethods::METHOD_HEAD,
                ];

            default:
                return [$method, HttpMethods::METHOD_ANY];
        }
    }

    private function matchSequentially(string $method, string $path): InternalRouteInterface
    {
        $methods = $this->methodMapping($method);

        if ($result = $this->matchSequentiallyForMethods($methods, $path)) {
            return $result;
        }

        if ($this->matchSequentiallyForMethods(array_diff(HttpMethods::ALL_METHODS, $methods), $path)) {
            throw new HttpException($method, $path, HttpException::HTTP_METHOD_NOT_ALLOWED);
        }

        throw new HttpException($method, $path, HttpException::HTTP_NOT_FOUND);
    }

    /**
     * @param array  $methods
     * @param string $path
     *
     * @return InternalRouteInterface|null
     */
    private function matchSequentiallyForMethods(array $methods, string $path)
    {
        if ($result = $this->matchKeyValue($path, $methods)) {
            return $result;
        }

        foreach ($methods as $currentMethod) {
            foreach ($this->routes[$currentMethod] ?? [] as $handler => $handlerData) {
                foreach ($handlerData as list($patternRoute, $extraParams)) {
                    /** @var PatternRoute $patternRoute */
                    /** @var array $extraParams */
                    if ($patternRoute->match($path, $queryParams)) {
                        return new InternalRoute($handler, array_replace($extraParams, $queryParams));
                    }
                }
            }
        }

        return null;
    }

    private function matchTree(string $method, string $path): InternalRouteInterface
    {
        $methods = $this->methodMapping($method);

        if ($result = $this->matchTreeForMethods($methods, $path)) {
            return $result;
        }

        if ($this->matchTreeForMethods(array_diff(HttpMethods::ALL_METHODS, $methods), $path)) {
            throw new HttpException($method, $path, HttpException::HTTP_METHOD_NOT_ALLOWED);
        }

        throw new HttpException($method, $path, HttpException::HTTP_NOT_FOUND);
    }

    /**
     * @param array  $methods
     * @param string $path
     *
     * @return InternalRouteInterface|null
     */
    private function matchTreeForMethods(array $methods, string $path)
    {
        if ($result = $this->matchKeyValue($path, $methods)) {
            return $result;
        }

        $nodesPointer = &$this->nodesTree;
        $chars = str_split($path);

        while (1) {
            $candidates = array_merge(
                $nodesPointer['regex'] ?? [],
                $nodesPointer['all'] ?? []
            );

            foreach ($candidates as list($route, $handler, $params, $method)) {
                if (!in_array($method, $methods, true)) {
                    continue;
                }
                /** @var PatternRoute $route */
                if ($route->match($path, $queryParams)) {
                    return new InternalRoute($handler, array_replace($params, $queryParams));
                }
            }

            $char = array_shift($chars);
            if (!is_string($char) || !isset($nodesPointer[$char])) {
                break;
            }
            $nodesPointer = &$nodesPointer[$char];
        }

        return null;
    }

    public function linkTo(string $handler, string $method = HttpMethods::METHOD_ANY): LinkInterface
    {
        $routes = [];

        foreach ($this->methodMapping($method) as $currentMethod) {
            $routes = array_merge($routes, $this->routes[$currentMethod][$handler] ?? []);
        }

        return new PatternLink($handler, $routes);
    }

    /**
     * Export to executable php code
     *
     * Example:
     * file_put_contents('router.cache', '<?php return ' . $router->export() . ';');
     * $router = require 'router.cache';
     *
     * @return string
     */
    public function exportToExecutable(): string
    {
        return (new SourceCodeExporter())->exportPatternRouter($this);
    }

    private function processExtraParams(array &$data)
    {
        array_walk_recursive($data, function (&$element) {
            if (is_scalar($element) || is_null($element)) {
                return;
            }

            if (is_object($element)) {
                if ($element instanceof \Traversable) {
                    $element = iterator_to_array($element);
                    $this->processExtraParams($element);

                    return;
                }

                if (method_exists($element, '__toString')) {
                    $element = (string) $element;

                    return;
                }
            }

            $message = sprintf(
                'Additional parameters can contain only scalar or null values, "%s" given',
                is_object($element) ? get_class($element) : gettype($element)
            );

            throw new InvalidArgumentException($message);
        });
    }
}
