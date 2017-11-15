<?php

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
        ];
    }

    public function providerFromUrl()
    {
        yield ['127.0.0.1', '127.0.0.1'];
    }

    public function providerInvalidToUrl()
    {
        if (PHP_INT_MAX > 4294967295) {
            yield [PHP_INT_MAX];
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
