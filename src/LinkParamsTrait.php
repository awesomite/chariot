<?php

namespace Awesomite\Chariot;

/**
 * @internal
 */
trait LinkParamsTrait
{
    private $params = [];

    private $prefix = '';

    public function withPrefix(string $prefix): LinkInterface
    {
        $this->prefix = $prefix;

        return $this;
    }

    public function withParams(array $params): LinkInterface
    {
        foreach ($params as $key => $value) {
            $this->withParam($key, $value);
        }

        return $this;
    }

    public function withParam(string $key, $value): LinkInterface
    {
        $this->params[$key] = $value;

        return $this;
    }
}
