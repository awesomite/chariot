<?php

/*
 * This file is part of the awesomite/chariot package.
 * (c) Bartłomiej Krukowski <bartlomiej@krukowski.me>
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

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
