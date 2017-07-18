<?php

namespace Awesomite\Chariot\Pattern;

use Awesomite\Chariot\Exceptions\InvalidArgumentException;
use Awesomite\Chariot\Reflections\Objects;
use Awesomite\Chariot\TestBase;

/**
 * @internal
 */
class PatternRouteTest extends TestBase
{
    /**
     * @dataProvider providerAll
     *
     * @param PatternsInterface $patterns
     * @param                   $pattern
     * @param                   $compiledPattern
     * @param string            $simplePattern
     */
    public function testAll(PatternsInterface $patterns, $pattern, $compiledPattern, string $simplePattern)
    {
        $route = new PatternRoute($pattern, $patterns);
        $this->assertSame($pattern, Objects::getProperty($route, 'pattern'));
        $this->assertSame($compiledPattern, Objects::getProperty($route, 'compiledPattern'));
        $this->assertSame($simplePattern, Objects::getProperty($route, 'simplePattern'));
    }

    public function providerAll()
    {
        $patterns = Patterns::createDefault();
        $patterns->addPattern(':id', '[0-9]+');

        yield [
            $patterns,
            '/{{ name :alphanum }}',
            '#^/(?<name>' . Patterns::REGEX_ALPHANUM . ')$#',
            '/{{name}}',
        ];
        yield [
            $patterns,
            '/{{ name :alphanum defaultName }}',
            '#^/(?<name>' . Patterns::REGEX_ALPHANUM . ')$#',
            '/{{name}}',
        ];
        yield [
            $patterns,
            '/get-item-{{ id :id }}',
            '#^/get-item-(?<id>[0-9]+)$#',
            '/get-item-{{id}}',
        ];
        yield [
            $patterns,
            '/{{ name }}',
            '#^/(?<name>' . Patterns::REGEX_DEFAULT . ')$#',
            '/{{name}}',
        ];
    }

    /**
     * @dataProvider providerMatch
     *
     * @param PatternRoute $route
     * @param string       $path
     * @param bool         $expectedMatch
     * @param array|null   $expectedParams
     */
    public function testMatch(
        PatternRoute $route,
        string $path,
        bool $expectedMatch,
        array $expectedParams = null
    ) {
        $this->assertSame($expectedMatch, $route->match($path, $params));
        if ($expectedMatch) {
            $this->assertSame($expectedParams, $params);
        }
    }

    public function providerMatch()
    {
        $patterns = Patterns::createDefault();

        $route = new PatternRoute('/', $patterns);
        yield [$route, '/', true, []];

        $route = new PatternRoute('/article/{{ id :uint }}', $patterns);
        yield [$route, '/article/123', true, ['id' => '123']];
        yield [$route, '/article/300', true, ['id' => '300']];
        yield [$route, '/article/-2', false];
    }

    /**
     * @dataProvider providerBindParams
     *
     * @param PatternRoute $route
     * @param array        $params
     * @param string       $expected
     */
    public function testBindParams(PatternRoute $route, array $params, string $expected)
    {
        $this->assertSame($expected, (string)$route->bindParams($params));
    }

    public function providerBindParams()
    {
        $route = new PatternRoute('/year-{{ year :int }}/month-{{ month :int }}', Patterns::createDefault());

        $params = [
            'year'  => 2016,
            'month' => 4,
        ];
        yield [$route, $params, '/year-2016/month-4'];

        $secondParams = [
            'year'  => '2018',
            'month' => '5',
            'foo'   => 'bar',
        ];
        yield [$route, $secondParams, '/year-2018/month-5?foo=bar'];

        $thirdParams = [
            'year'  => '2020',
            'month' => 3,
            'foo'   => 'bar',
            'hello' => 'world',
        ];
        yield [$route, $thirdParams, '/year-2020/month-3?foo=bar&hello=world'];

        $fourthParams = [
            'year'  => 2021,
            'month' => 1,
            'array' => [1, 2, 3],
        ];
        yield [
            $route,
            $fourthParams,
            '/year-2021/month-1?array[0]=1&array[1]=2&array[2]=3',
        ];

        $fifthParams = [
            'year'  => '2022',
            'month' => '02',
            'a'     => [1, 'b'],
            'b'     => ['foo' => 'bar'],
        ];
        yield [
            $route,
            $fifthParams,
            '/year-2022/month-02?a[0]=1&a[1]=b&b[foo]=bar',
        ];
    }

    public function testInvalidConstructor()
    {
        $patterns = Patterns::createDefault();
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageRegExp('#^.* is not valid regular expression, source pattern is equal to .*$#');
        new PatternRoute('#', $patterns);
    }

    /**
     * @dataProvider providerGetNodes
     *
     * @param string $pattern
     * @param array  $expectedNodes
     */
    public function testGetNodes(string $pattern, array $expectedNodes)
    {
        $patterns = Patterns::createDefault();
        $route = new PatternRoute($pattern, $patterns);
        $nodes = $route->getNodes();
        $this->assertSame(count($expectedNodes), count($nodes));
        foreach ($nodes as $key => $node) {
            list($expectedKey, $expectedIsRegex) = $expectedNodes[$key];
            $this->assertSame($expectedKey, $node->getKey());
            $this->assertSame($expectedIsRegex, $node->isRegex());
        }
    }

    public function providerGetNodes()
    {
        $pattern = 'ab{{ id :int }}cd';
        $expectedNodes = [
            ['a', false],
            ['b', false],
            ['{{ id :int }}cd', true],
        ];

        yield $pattern => [$pattern, $expectedNodes];

        $secondParam = '{{ id2 :int }}';
        $pattern .= $secondParam;
        $expectedNodes[2][0] .= $secondParam;

        yield $pattern => [$pattern, $expectedNodes];

        $pattern = 'abc';
        $expectedNodes = [
            ['a', false],
            ['b', false],
            ['c', false],
        ];

        yield $pattern => [$pattern, $expectedNodes];
    }

    /**
     * @dataProvider providerMatchParams
     *
     * @param PatternRoute $route
     * @param array        $params
     * @param bool         $expected
     */
    public function testMatchParams(PatternRoute $route, array $params, bool $expected)
    {
        $this->assertSame($expected, $route->matchParams($params));
    }

    public function providerMatchParams()
    {
        $patterns = Patterns::createDefault();

        $route = new PatternRoute('/category-{{ categoryId :uint }}/item-{{ itemId :uint }}', $patterns);

        yield [
            $route,
            ['categoryId' => 1, 'itemId' => 1],
            true,
        ];
        yield [
            $route,
            ['categoryId' => '1', 'itemId' => 1],
            true,
        ];
        yield [
            $route,
            ['categoryId' => '-1', 'itemId' => 1],
            false,
        ];
        yield [
            $route,
            [],
            false,
        ];
    }
}
