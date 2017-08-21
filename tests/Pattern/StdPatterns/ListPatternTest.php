<?php

namespace Awesomite\Chariot\Pattern\StdPatterns;

use Awesomite\Chariot\Exceptions\PatternException;
use Awesomite\Chariot\Pattern\StringableObject;
use Awesomite\Chariot\TestBase;

/**
 * @internal
 */
class ListPatternTest extends TestBase
{
    /**
     * @dataProvider providerInvalidToUrl
     *
     * @param      $list
     * @param bool $isInvalid
     */
    public function testInvalidToUrl($list, bool $isInvalid)
    {
        $pattern = new ListPattern();

        if ($isInvalid) {
            $this->expectException(PatternException::class);
            $this->expectExceptionCode(PatternException::CODE_TO_URL);
        }

        $pattern->toUrl($list);
    }

    public function providerInvalidToUrl()
    {
        return [
            'int'               => [1, false],
            \stdClass::class    => [new \stdClass(), true],
            'resource'          => [tmpfile(), true],
            'emptyArray'        => [[], true],
            'emptyTraversable'  => [new \ArrayObject(), true],
            'array'             => [['foo'], false],
            'traversable'       => [new \ArrayObject(['foo']), false],
            'invalidStringable' => [new StringableObject(''), true],
            'stringable'        => [new StringableObject('x,y,z'), false],
            'invalidString'     => ['', true],
            'string'            => ['foo', false],
            'invalidCharacters' => [['foo/', 'bar'], true],
        ];
    }

    /**
     * @dataProvider providerToUrl
     *
     * @param             $data
     * @param string      $expected
     */
    public function testToUrl($data, string $expected)
    {
        $this->assertSame($expected, (new ListPattern())->toUrl($data));
    }

    public function providerToUrl()
    {
        yield [['foo', 'bar'], 'foo,bar'];
        yield [['foo'], 'foo'];
        yield [['a', 'b', 'c'], 'a,b,c'];
        yield [new \ArrayObject(['x', 'y', 'z']), 'x,y,z'];
        yield ['foo', 'foo'];
        yield [5, '5'];
    }

    /**
     * @dataProvider providerFromUrl
     *
     * @param string $urlPart
     * @param array  $expected
     */
    public function testFromUrl(string $urlPart, array $expected)
    {
        $this->assertSame($expected, (new ListPattern())->fromUrl($urlPart));
    }

    public function providerFromUrl()
    {
        return [
            ['a,b,c', ['a', 'b', 'c']],
            ['hello-world', ['hello-world']],
        ];
    }
}
