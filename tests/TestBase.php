<?php

/*
 * This file is part of the awesomite/chariot package.
 * (c) Bartłomiej Krukowski <bartlomiej@krukowski.me>
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Awesomite\Chariot;

use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class TestBase extends TestCase
{
    protected function setUp()
    {
        $this->expectOutputString('');
    }

    protected function assertArraysWithSameElements(array $expected, array $actual, string $message = '')
    {
        \sort($expected);
        \sort($actual);
        $this->assertSame($expected, $actual, $message);
    }
}
