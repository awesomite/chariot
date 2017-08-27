<?php

namespace Awesomite\Chariot\Pattern\StdPatterns;

use Awesomite\Chariot\Exceptions\PatternException;
use Awesomite\Chariot\Pattern\PatternInterface;
use Awesomite\Chariot\TestBase;

/**
 * @internal
 */
abstract class AbstractPatternTest extends TestBase
{
    /**
     * @dataProvider providerToUrl
     *
     * @param        $data
     * @param string $expected
     */
    public function testToUrl($data, string $expected)
    {
        $this->assertSame(
            $expected,
            $this->getPattern()->toUrl($data)
        );
    }

    /**
     * @dataProvider providerInvalidToUrl
     *
     * @param $data
     */
    public function testInvalidToUrl($data)
    {
        $this->expectException(PatternException::class);
        $this->expectExceptionCode(PatternException::CODE_TO_URL);
        $this->getPattern()->toUrl($data);
    }

    /**
     * @dataProvider providerFromUrl
     *
     * @param string $param
     * @param        $expected
     */
    public function testFromUrl(string $param, $expected)
    {
        $this->assertSame(
            $expected,
            $this->getPattern()->fromUrl($param)
        );
    }

    /**
     * @return array|\Traversable [[$param, $expected], ...]
     */
    abstract public function providerFromUrl();

    /**
     * @return array|\Traversable [[$input, $expected], ...]
     */
    abstract public function providerToUrl();

    /**
     * @return array|\Traversable [[$input], ...]
     */
    abstract public function providerInvalidToUrl();

    abstract public function getPattern(): PatternInterface;
}
