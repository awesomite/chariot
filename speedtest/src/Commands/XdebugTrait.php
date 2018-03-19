<?php

/*
 * This file is part of the awesomite/chariot package.
 * (c) Bartłomiej Krukowski <bartlomiej@krukowski.me>
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Awesomite\Chariot\Speedtest\Commands;

/**
 * @internal
 */
trait XdebugTrait
{
    private function checkXdebug()
    {
        if (\extension_loaded('xdebug')) {
            throw new \RuntimeException('Do not execute performance tests with enabled xdebug (add -n option)');
        }
    }
}
