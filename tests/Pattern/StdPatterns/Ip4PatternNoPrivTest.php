<?php

namespace Awesomite\Chariot\Pattern\StdPatterns;

use Awesomite\Chariot\Pattern\PatternInterface;

/**
 * @internal
 */
class Ip4PatternNoPrivTest extends AbstractPatternTest
{
    public function getPattern(): PatternInterface
    {
        return unserialize(serialize(new Ip4Pattern(false)));
    }

    public function providerInvalidToUrl()
    {
        yield ['192.168.1.1'];
        yield ['192.168.0.0'];
        yield ['192.168.255.255'];
    }

    public function providerToUrl()
    {
        return [
            ['8.8.8.8', '8.8.8.8'],
            ['127.0.0.1', '127.0.0.1'],
            ['0.0.0.0', '0.0.0.0'],
            ['192.169.0.0', '192.169.0.0'],
        ];
    }
}
