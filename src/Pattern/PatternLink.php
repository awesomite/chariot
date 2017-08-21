<?php

namespace Awesomite\Chariot\Pattern;

use Awesomite\Chariot\Exceptions\CannotGenerateLinkException;
use Awesomite\Chariot\LinkInterface;
use Awesomite\Chariot\LinkParamsTrait;

/**
 * @internal
 */
class PatternLink implements LinkInterface
{
    use LinkParamsTrait;

    private $handler;

    /**
     * [[$patternRoute, $extraParams], ...]
     *
     * @var array
     */
    private $routes;

    private $sorted = false;

    public function __construct(string $handler, array $routes)
    {
        $this->handler = $handler;
        $this->routes = $routes;
    }

    public function __toString(): string
    {
        try {
            return $this->toString();
        } catch (CannotGenerateLinkException $exception) {
            return static::ERROR_CANNOT_GENERATE_LINK;
        }
    }

    public function toString(): string
    {
        $this->sortIfNeed();
        foreach ($this->routes as list($route, $extraParams)) {
            $currentParams = $this->params;
            /** @var PatternRoute $route */
            /** @var array $extraParams */
            foreach ($extraParams as $key => $value) {
                if (!array_key_exists($key, $this->params) || $this->normalizeVar($this->params[$key]) != $value) {
                    continue 2;
                }
                unset($currentParams[$key]);
            }
            $convertedParams = $route->matchParams($currentParams);
            if (is_array($convertedParams)) {
                return $this->prefix . (string) $route->bindParams(array_replace($currentParams, $convertedParams));
            }
        }

        throw new CannotGenerateLinkException($this->handler, $this->params);
    }

    private function normalizeVar($var)
    {
        if (is_object($var)) {
            if ($var instanceof \Traversable) {
                return $this->normalizeVar(iterator_to_array($var));
            }

            if (method_exists($var, '__toString')) {
                return (string) $var;
            }
        }

        if (is_array($var)) {
            array_walk_recursive($var, function ($element) {
                return $this->normalizeVar($element);
            });

            return $var;
        }

        return $var;
    }

    private function sortIfNeed()
    {
        if ($this->sorted) {
            return;
        }

        usort($this->routes, function ($left, $right) {
            return count($right[1]) <=> count($left[1]);
        });
        $this->sorted = true;
    }
}
