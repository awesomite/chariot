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

    /**
     * [[$patternRoute, $extraParams], ...]
     *
     * @var array
     */
    private $routes;

    public function __construct(array $routes)
    {
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
        foreach ($this->routes as list($route, $extraParams)) {
            $currentParams = $this->params;
            /** @var PatternRoute $route */
            /** @var array $extraParams */
            foreach ($extraParams as $key => $value) {
                if (!array_key_exists($key, $this->params) || $this->params[$key] != $value) {
                    continue 2;
                }
                unset($currentParams[$key]);
            }
            if ($route->matchParams($currentParams)) {
                return $this->prefix . (string)$route->bindParams($currentParams);
            }
        }

        throw new CannotGenerateLinkException();
    }
}
