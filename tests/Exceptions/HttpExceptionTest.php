<?php

/*
 * This file is part of the awesomite/chariot package.
 * (c) Bartłomiej Krukowski <bartlomiej@krukowski.me>
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

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
        new HttpException('GET', '/', 200);
    }
}
