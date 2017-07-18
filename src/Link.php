<?php

namespace Awesomite\Chariot;

/**
 * @internal
 */
class Link implements LinkInterface
{
    use LinkParamsTrait;

    private $base;

    public function __construct(string $base)
    {
        $this->base = $base;
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    public function toString(): string
    {
        return $this->prefix . $this->base . ($this->params ? '?' . urldecode(http_build_query($this->params)) : '');
    }
}
