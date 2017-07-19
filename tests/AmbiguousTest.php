<?php

namespace Awesomite\Chariot;

use Awesomite\Chariot\Pattern\PatternRouter;
use Awesomite\Chariot\Pattern\Patterns;

/**
 * Examples of behavior in ambiguous cases, e.g.:
 *
 * $router
 *   ->get('/page-{{ page :uint }}', 'showPage')
 *   ->get('/show-first-page', 'showPage', ['page' => 1]);
 *
 * echo $router->linkTo('showPage')->withParam('page', 1);
 *
 * @internal
 */
class AmbiguousTest extends TestBase
{
    /**
     * @dataProvider providerLinkTo
     *
     * @param PatternRouter $router
     * @param bool          $reverse
     */
    public function testLinkTo(PatternRouter $router, bool $reverse)
    {
        $routes = [
            ['/show-first-page', 'showPage', ['page' => 1]],
            ['/show-page-{{ page :int }}', 'showPage', []],
        ];

        if ($reverse) {
            $routes = array_reverse($routes);
        }

        foreach ($routes as $route) {
            $router->get(...$route);
        }

        $this->assertSame('/show-first-page', (string) $router->linkTo('showPage')->withParam('page', 1));
        $this->assertSame('/show-first-page', (string) $router->linkTo('showPage')->withParam('page', '1'));
        $this->assertSame(
            '/show-first-page',
            (string) $router->linkTo('showPage')->withParam('page', $this->createStringable(1))
        );
    }

    public function providerLinkTo()
    {
        foreach ($this->createEmptyPatternRouters() as $router) {
            foreach ([true, false] as $reverse) {
                yield [$router, $reverse];
            }
        }
    }

    /**
     * @dataProvider providerMatch
     *
     * @param RouterInterface $router
     * @param string $method
     * @param string $path
     * @param InternalRouteInterface $expected
     */
    public function testMatch(RouterInterface $router, string $method, string $path, InternalRouteInterface $expected)
    {
        $route = $router->match($method, $path);
        $this->assertSame($expected->getHandler(), $route->getHandler());
        $this->assertSame($expected->getParams(), $route->getParams());
    }

    public function providerMatch()
    {
        foreach ($this->createEmptyPatternRouters() as $router) {
            /*
             * The same paths, different methods
             */
            $router
                ->get('/comment/{{ id :uint }}', 'getComment')
                ->delete('/comment/{{ id :uint }}', 'deleteComment');

            $path = '/comment/1';
            $params = ['id' => '1'];
            yield [$router, HttpMethods::METHOD_GET, $path, new InternalRoute('getComment', $params)];
            yield [$router, HttpMethods::METHOD_HEAD, $path, new InternalRoute('getComment', $params)];
            yield [$router, HttpMethods::METHOD_DELETE, $path, new InternalRoute('deleteComment', $params)];

            /*
             * Two matched routes, route without regex has bigger priority 
             */
            $router
                ->get('/pages/contact', 'showContact')
                ->get('/pages/{{ page }}', 'showPage');
            yield [$router, HttpMethods::METHOD_GET, '/pages/contact', new InternalRoute('showContact', [])];

            /*
             * Two matched routes, both contain regex, priority is determined by order
             */
            $router
                ->get('/item-{{ name :alphanum }}', 'showItemByName')
                ->get('/item-{{ id :uint }}', 'showItemById')
                ->get('/article-{{ id :uint }}', 'showArticleById')
                ->get('/article-{{ title [a-zA-Z0-9-]+ }}', 'showArticleByTitle');
            yield [$router, HttpMethods::METHOD_GET, '/item-5', new InternalRoute('showItemByName', ['name' => '5'])];
            yield [$router, HttpMethods::METHOD_GET, '/article-5', new InternalRoute('showArticleById', ['id' => '5'])];
            yield [
                $router,
                HttpMethods::METHOD_GET,
                '/article-hello-world',
                new InternalRoute('showArticleByTitle', ['title' => 'hello-world']),
            ];
        }
    }

    /**
     * @return PatternRouter[]
     */
    private function createEmptyPatternRouters()
    {
        return [
            new PatternRouter(Patterns::createDefault(), PatternRouter::STRATEGY_SEQUENTIALLY),
            new PatternRouter(Patterns::createDefault(), PatternRouter::STRATEGY_TREE),
        ];
    }

    private function createStringable($value)
    {
        return new class ($value)
        {
            private $value;

            public function __construct($value)
            {
                $this->value = $value;
            }

            public function __toString()
            {
                return (string) $this->value;
            }
        };
    }
}
