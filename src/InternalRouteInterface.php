<?php

namespace Awesomite\Chariot;

interface InternalRouteInterface
{
    public function getHandler(): string;

    public function getParams(): array;
}
