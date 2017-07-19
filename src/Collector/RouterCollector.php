<?php

namespace Awesomite\Chariot\Collector;

use Awesomite\Chariot\Exceptions\HttpException;
use Awesomite\Chariot\HttpMethods;
use Awesomite\Chariot\InternalRouteInterface;
use Awesomite\Chariot\LinkInterface;
use Awesomite\Chariot\RouterInterface;

class RouterCollector implements RouterInterface
{
    /**
     * @var RouterInterface[]
     */
    private $routers = [];

    public function addRouter(RouterInterface $router): RouterCollector
    {
        $this->routers[] = $router;

        return $this;
    }

    public function match(string $method, string $path): InternalRouteInterface
    {
        $errorCode = HttpException::HTTP_NOT_FOUND;
        foreach ($this->routers as $router) {
            try {
                return $router->match($method, $path);
            } catch (HttpException $exception) {
                if ($exception->getCode() === HttpException::HTTP_METHOD_NOT_ALLOWED) {
                    $errorCode = HttpException::HTTP_METHOD_NOT_ALLOWED;
                }
            }
        }

        throw new HttpException($method, $path, $errorCode);
    }

    public function getAllowedMethods(string $path): array
    {
        $result = [];
        foreach ($this->routers as $router) {
            $result = array_merge($result, $router->getAllowedMethods($path));
        }

        return array_unique($result);
    }

    public function linkTo(string $handler, string $method = HttpMethods::METHOD_ANY): LinkInterface
    {
        $getters = [];
        foreach ($this->routers as $router) {
            $getters[] = function () use ($router, $handler, $method) {
                return $router->linkTo($handler, $method);
            };
        }

        return new LinkCollector($handler, $getters);
    }
}
