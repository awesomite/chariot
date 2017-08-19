<?php

namespace Awesomite\Chariot\Reflections;

use Awesomite\Chariot\TestBase;

/**
 * @internal
 */
class ObjectsTest extends TestBase
{
    public function testGetProperty()
    {
        $value = mt_rand();
        $this->assertSame(
            $value,
            Objects::getProperty($this->createClass($value), 'privateValue')
        );
    }

    private function createClass($privateValue)
    {
        return new class ($privateValue)
        {
            private $privateValue;

            public function __construct($privateValue)
            {
                $this->privateValue = $privateValue;
            }
        };
    }
}
