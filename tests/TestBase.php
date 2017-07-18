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
}
