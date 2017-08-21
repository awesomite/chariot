<?php

namespace Awesomite\Chariot\Pattern\StdPatterns;

use Awesomite\Chariot\Exceptions\PatternException;
use Awesomite\Chariot\Pattern\StringableObject;
use Awesomite\Chariot\TestBase;

/**
 * @internal
 */
class IntPatternTest extends TestBase
{
    protected function getPattern()
    {
        return new IntPattern();
    }

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

    public function providerToUrl()
    {
        return [
            [-0xf, '-15'],
            [-23, '-23'],
            [0, '0'],
            [PHP_INT_MAX, (string) PHP_INT_MAX],
            [new StringableObject('15'), '15'],
            [new StringableObject('-15'), '-15'],
        ];
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

    public function providerInvalidToUrl()
    {
        return [
            ['1.0'],
            [pi()],
            [new \stdClass()],
        ];
    }
}
