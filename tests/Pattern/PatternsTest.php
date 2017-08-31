<?php

namespace Awesomite\Chariot\Pattern;

use Awesomite\Chariot\Exceptions\HttpException;
use Awesomite\Chariot\Exceptions\InvalidArgumentException;
use Awesomite\Chariot\Exceptions\LogicException;
use Awesomite\Chariot\HttpMethods;
use Awesomite\Chariot\Reflections\Objects;
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

    /**
     * @dataProvider providerInvalidName
     *
     * @param string $paramName
     */
    public function testInvalidName(string $paramName)
    {
        $this->expectException(InvalidArgumentException::class);
        $message = sprintf(
            'Method %s::addPattern() requires first parameter prefixed by ":", "%s" given',
            Patterns::class,
            $paramName
        );
        $this->expectExceptionMessage($message);
        $patterns = new Patterns();
        $patterns->addPattern($paramName, Patterns::REGEX_INT);
    }

    public function providerInvalidName()
    {
        return [
            ['name'],
            [''],
        ];
    }

    /**
     * @dataProvider providerInvalidPatternArgument
     *
     * @param        $invalidPattern
     * @param string $typeOfPattern
     */
    public function testInvalidPatternArgument($invalidPattern, string $typeOfPattern)
    {
        $this->expectExceptionMessage(InvalidArgumentException::class);
        $message = sprintf(
            'Method %s::addPattern() expects string or %s, %s given',
            Patterns::class,
            PatternInterface::class,
            $typeOfPattern
        );
        $this->expectExceptionMessage($message);
        (new Patterns())->addPattern(':foo', $invalidPattern);
    }

    public function providerInvalidPatternArgument()
    {
        return [
            [new \stdClass(), 'stdClass'],
            [null, 'NULL'],
            [tmpfile(), 'resource'],
        ];
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

    public function testAddEnumInConstructor()
    {
        $data = [
            ':enum' => ['hello', 'world'],
        ];
        $patterns = new Patterns($data);
        $this->assertSame('hello|world', $patterns[':enum']->getRegex());
    }

    /**
     * @dataProvider providerDefaultPatterns
     *
     * @param string   $type
     * @param string[] $valid
     * @param string[] $invalid
     */
    public function testDefaultPatterns(string $type, array $valid, array $invalid)
    {
        $router = PatternRouter::createDefault();
        $router->addRoute(HttpMethods::METHOD_GET, "/{{ value {$type} }}", 'myhandler');

        foreach ($valid as $value) {
            try {
                $router->match(HttpMethods::METHOD_GET, '/' . $value);
            } catch (HttpException $exception) {
                /** @var PatternRoute $patternRoute */
                $patternRoute = Objects::getProperty($router, 'routes')['GET']['myhandler'][0][0];
                $regex = Objects::getProperty($patternRoute, 'compiledPattern');
                $this->fail(sprintf('Path: %s, regex: %s', '/' . $value, $regex));
            }
        }

        foreach ($invalid as $value) {
            try {
                $router->match(HttpMethods::METHOD_GET, '/' . $value);
            } catch (HttpException $exception) {
                $this->assertSame(HttpException::HTTP_NOT_FOUND, $exception->getCode());
                continue;
            }
            $this->fail();
        }
    }

    public function providerDefaultPatterns()
    {
        yield [
            ':int',
            ['-1', '0', '1', '5000'],
            ['hello', '1.0'],
        ];

        yield [
            ':uint',
            ['0', '1', '5000'],
            ['-1', '-1', '1.0', '1.1'],
        ];

        yield [
            ':float',
            ['1', '0', '-1', '-123.45', '-123.45', '500.15'],
            ['1,1', 'hello', '1.0'],
        ];

        yield [
            ':ufloat',
            ['1', '1.1', '1.23', '0'],
            ['-1', 'foo', '1.0', '1.20', '-1.1'],
        ];

        yield [
            ':date',
            ['2017-01-01'],
            ['2017-01-32'],
        ];

        yield [
            ':list',
            ['foo,bar'],
            ['foo/bar'],
        ];

        yield [
            ':ip4',
            ['127.0.0.1'],
            ['256.255.255.255'],
        ];

        yield [
            ':alphanum',
            ['nickname2000'],
            ['hello/world'],
        ];
    }
}
