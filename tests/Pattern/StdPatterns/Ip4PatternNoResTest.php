<?php

namespace Awesomite\Chariot\Pattern\StdPatterns;

use Awesomite\Chariot\Pattern\PatternInterface;

/**
 * @internal
 */
class Ip4PatternNoResTest extends AbstractPatternTest
{
    public function providerToUrl()
    {
        yield ['1.1.1.1', '1.1.1.1'];
    }

    public function providerInvalidToUrl()
    {
        yield ['127.0.0.1'];
        yield ['0.0.0.0'];
    }

    public function getPattern(): PatternInterface
    {
        return unserialize(serialize(new Ip4Pattern(true, false)));
    }
}
