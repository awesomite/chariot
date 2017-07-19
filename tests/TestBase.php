<?php

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
        sort($expected);
        sort($actual);
        $this->assertSame($expected, $actual, $message);
    }
}
