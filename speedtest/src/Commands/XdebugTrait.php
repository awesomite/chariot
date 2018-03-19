<?php

namespace Awesomite\Chariot\Speedtest\Commands;

/**
 * @internal
 */
trait XdebugTrait
{
    private function checkXdebug()
    {
        if (extension_loaded('xdebug')) {
            throw new \RuntimeException('Do not execute performance tests with enabled xdebug (add -n option)');
        }
    }
}
