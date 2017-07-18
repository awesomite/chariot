<?php

namespace Awesomite\Chariot;

/**
 * @internal
 */
class InternalRoute implements InternalRouteInterface
{
    private $handler;

    private $extraParams;

    public function __construct(string $handler, array $extraParams)
    {
        $this->handler = $handler;
        $this->extraParams = $extraParams;
    }

    public function getParams(): array
    {
        return $this->extraParams;
    }

    public function getHandler(): string
    {
        return $this->handler;
    }
}
