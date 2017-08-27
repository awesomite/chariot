<?php

namespace Awesomite\Chariot\Pattern\StdPatterns;

use Awesomite\Chariot\Exceptions\PatternException;
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

    public function providerFromUrl()
    {
        return $this->providerToUrl();
    }

    public function getPattern(): PatternInterface
    {
        return unserialize(serialize(new Ip4Pattern(true, false)));
    }

    /**
     * @dataProvider providerInvalidFromUrl
     *
     * @param string $param
     */
    public function testInvalidFromUrl(string $param)
    {
        $this->expectException(PatternException::class);
        $this->expectExceptionCode(PatternException::CODE_FROM_URL);
        $this->getPattern()->fromUrl($param);
    }

    public function providerInvalidFromUrl()
    {
        return $this->providerInvalidToUrl();
    }
}
