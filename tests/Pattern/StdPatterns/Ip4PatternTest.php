<?php

/*
 * This file is part of the awesomite/chariot package.
 * (c) Bartłomiej Krukowski <bartlomiej@krukowski.me>
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Awesomite\Chariot\Pattern\StdPatterns;

use Awesomite\Chariot\Pattern\PatternInterface;
use Awesomite\Chariot\StringableObject;

/**
 * @internal
 */
class Ip4PatternTest extends AbstractPatternTest
{
    public function providerToUrl()
    {
        return [
            [0, '0.0.0.0'],
            ['0', '0.0.0.0'],
            ['0.0.0.0', '0.0.0.0'],
            [new StringableObject('0'), '0.0.0.0'],
            ['127.0.0.1', '127.0.0.1'],
            [4294967295, '255.255.255.255'],
        ];
    }

    public function providerFromUrl()
    {
        yield ['127.0.0.1', '127.0.0.1'];
    }

    public function providerInvalidToUrl()
    {
        $maxIntIp = 4294967295;
        if (PHP_INT_MAX > $maxIntIp) {
            yield [$maxIntIp + 1];
            yield [$maxIntIp + 2];
            yield [$maxIntIp + 3];
        }
        yield [-1];
        yield [new \stdClass()];
        yield ['255.255.255.255.255'];
        yield ['256.255.255.255'];
        yield ['08.08.08.08'];
    }

    public function getPattern(): PatternInterface
    {
        return new Ip4Pattern();
    }
}
