<?php

namespace Awesomite\Chariot\Exceptions;

use Awesomite\Chariot\TestBase;

/**
 * @internal
 */
class HttpExceptionTest extends TestBase
{
    public function testInvalidConstructor()
    {
        $this->expectException(InvalidArgumentException::class);
        new HttpException("GET /", 200);
    }
}
