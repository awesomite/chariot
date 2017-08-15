<?php

namespace Awesomite\Chariot\Pattern;

use Awesomite\Chariot\Exceptions\InvalidArgumentException;
use Awesomite\Chariot\Exceptions\LogicException;
use Awesomite\Chariot\TestBase;

/**
 * @internal
 */
class PatternsTest extends TestBase
{
    public function testDuplicatedPattern()
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Pattern :int is already added');
        $pattern = new Patterns();
        for ($i = 0; $i < 2; $i++) {
            $pattern->addPattern(':int', Patterns::REGEX_INT);
        }
    }

    public function testInvalidPattern()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid regex: *');
        (new Patterns())->addPattern(':myPattern', '*');
    }

    public function testInvalidDefaultPattern()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid regex: *');
        (new Patterns())->setDefaultPattern('*');
    }

    /**
     * @dataProvider providerSetPattern
     *
     * @param string $name
     * @param string $pattern
     */
    public function testSetPattern(string $name, string $pattern)
    {
        $patterns = new Patterns();
        $patterns->addPattern($name, $pattern);
        $this->assertSame($pattern, $patterns[$name]->getRegex());
    }

    /**
     * @dataProvider providerSetPattern
     *
     * @param string $name
     * @param string $pattern
     */
    public function testArraySetPattern(string $name, string $pattern)
    {
        $patterns = new Patterns();
        $patterns[$name] = $pattern;
        $this->assertSame($pattern, $patterns[$name]->getRegex());
    }

    public function providerSetPattern()
    {
        return [
            [':int', Patterns::REGEX_INT],
            [':uint', Patterns::REGEX_UINT],
        ];
    }

    /**
     * @dataProvider providerDefaultPattern
     *
     * @param string $pattern
     */
    public function testDefaultPattern(string $pattern)
    {
        $patterns = new Patterns();
        $this->assertSame(Patterns::REGEX_DEFAULT, $patterns->getDefaultPattern());
        $patterns->setDefaultPattern($pattern);
        $this->assertSame($pattern, $patterns->getDefaultPattern());
    }

    public function providerDefaultPattern()
    {
        return [
            [Patterns::REGEX_DEFAULT],
            [Patterns::REGEX_INT],
        ];
    }

    /**
     * @dataProvider providerEnumPattern
     *
     * @param array  $enum
     * @param string $expected
     */
    public function testEnumPattern(array $enum, string $expected)
    {
        $patterns = new Patterns();
        $patterns->addEnumPattern(':enum', $enum);
        $this->assertSame($expected, $patterns[':enum']->getRegex());
    }

    public function providerEnumPattern()
    {
        return [
            [['apple', 'banana', 'orange'], 'apple|banana|orange'],
            [['!', '#'], '\\!|\\#'],
        ];
    }

    public function testUnset()
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Operation forbidden');
        $patterns = new Patterns();
        unset($patterns[':pattern']);
    }
}
