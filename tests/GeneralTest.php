<?php

namespace Awesomite\Chariot;

use Awesomite\Chariot\Collector\RouterCollector;
use Awesomite\Chariot\Exceptions\CannotGenerateLinkException;
use Awesomite\Chariot\Exceptions\HttpException;
use Awesomite\Chariot\Pattern\PatternRouter;
use Awesomite\Chariot\Pattern\Patterns;
use Awesomite\Chariot\Pattern\StringableObject;

/**
 * @internal
 */
class GeneralTest extends TestBase
{
    /**
     * @dataProvider providerMatchExtended
     *
     * @param RouterInterface $router
     * @param string          $method
     * @param string          $path
     * @param string          $expectedHandler
     * @param array           $expectedParams
     */
    public function testMatch(
        RouterInterface $router,
        string $method,
        string $path,
        string $expectedHandler,
        array $expectedParams
    ) {
        $route = $router->match($method, $path);
        $this->assertSame($expectedHandler, $route->getHandler());
        $this->assertSame($expectedParams, $route->getParams());
    }

    public function providerMatchExtended()
    {
        foreach ($this->providerMatch() as $row) {
            yield $row;
            /** @var PatternRouter $router */
            $router = $row[0];

            $routerCollector = new RouterCollector();
            $routerCollector->addRouter($router);
            $row[0] = $routerCollector;
            yield $row;

            $row[0] = eval('return ' . $router->exportToExecutable() . ';');
            yield $row;
        }
    }

    private function providerMatch()
    {
        $router = PatternRouter::createDefault();

        $router->addRoute(HttpMethods::METHOD_ANY, '/', 'home');
        yield [$router, HttpMethods::METHOD_DELETE, '/', 'home', []];
        yield [$router, HttpMethods::METHOD_GET, '/', 'home', []];

        $router->addRoute(HttpMethods::METHOD_GET, '/hello', 'getHello');
        $router->addRoute(HttpMethods::METHOD_PUT, '/hello', 'putHello');

        yield [$router, HttpMethods::METHOD_GET, '/hello', 'getHello', []];
        yield [$router, HttpMethods::METHOD_PUT, '/hello', 'putHello', []];

        $router->addRoute(
            HttpMethods::METHOD_GET,
            '/month-{{ month [1-9]|1[0-2] }}',
            'monthSelector',
            ['lang' => 'en']
        );
        yield [
            $router,
            HttpMethods::METHOD_GET,
            '/month-12',
            'monthSelector',
            ['lang' => 'en', 'month' => '12'],
        ];

        $router->addRoute(
            HttpMethods::METHOD_GET,
            '/miesiac-{{ month [1-9]|1[0-2] }}',
            'monthSelector',
            ['lang' => 'pl']
        );
        yield [
            $router,
            HttpMethods::METHOD_GET,
            '/miesiac-7',
            'monthSelector',
            ['lang' => 'pl', 'month' => '7'],
        ];

        $customRouter = new PatternRouter(new Patterns());
        $customRouter->getPatterns()->addPattern(':month', '[1-9]|1[0-2]');
        $customRouter->addRoute(HttpMethods::METHOD_GET, '/month/{{ month :month }}', 'showMonth');
        yield [
            $customRouter,
            HttpMethods::METHOD_GET,
            '/month/7',
            'showMonth',
            ['month' => '7'],
        ];
        yield [
            $customRouter,
            HttpMethods::METHOD_GET,
            '/month/10',
            'showMonth',
            ['month' => '10'],
        ];
    }

    /**
     * @dataProvider providerLinkToExtended
     *
     * @param RouterInterface $router
     * @param string          $handler
     * @param array           $params
     * @param string          $expected
     */
    public function testLinkTo(RouterInterface $router, string $handler, array $params, string $expected)
    {
        $link = (string) $router->linkTo($handler)->withParams($params);
        $this->assertSame($expected, $link);
    }

    public function providerLinkToExtended()
    {
        foreach ($this->providerLinkTo() as $row) {
            yield $row;
            /** @var PatternRouter $router */
            $router = $row[0];

            $routerCollector = new RouterCollector();
            $routerCollector->addRouter($router);
            $row[0] = $routerCollector;
            yield $row;

            $row[0] = eval('return ' . $router->exportToExecutable() . ';');
            yield $row;
        }
    }

    private function providerLinkTo()
    {
        $router = PatternRouter::createDefault();

        $router->addRoute(HttpMethods::METHOD_GET, '/', 'home', ['lang' => 'en']);
        $router->addRoute(HttpMethods::METHOD_GET, '/pl', 'home', ['lang' => 'pl']);
        $router->addRoute(HttpMethods::METHOD_GET, '/category-{{ categoryId \\d+ }}', 'showCategory');
        yield [$router, 'home', ['lang' => 'en'], '/'];
        yield [$router, 'home', ['lang' => 'pl'], '/pl'];
        yield [$router, 'showCategory', ['categoryId' => new StringableObject('15')], '/category-15'];
        yield [$router, 'showCategory', ['categoryId' => '15'], '/category-15'];
        yield [$router, 'showCategory', ['categoryId' => 15], '/category-15'];
    }

    /**
     * @dataProvider providerEmptyRouter
     *
     * @param RouterInterface $router
     */
    public function testNotFound(RouterInterface $router)
    {
        $this->expectException(HttpException::class);
        $router->match(HttpMethods::METHOD_GET, 'foo');
    }

    /**
     * @dataProvider providerEmptyRouter
     *
     * @param RouterInterface $router
     */
    public function testCannotGenerateLink(RouterInterface $router)
    {
        $this->assertSame(LinkInterface::ERROR_CANNOT_GENERATE_LINK, (string) $router->linkTo('foo'));

        $this->expectException(CannotGenerateLinkException::class);
        $router->linkTo('foo')->toString();
    }

    public function providerEmptyRouter()
    {
        $router = PatternRouter::createDefault();

        yield [$router];
        yield [(new RouterCollector())->addRouter($router)];
        yield [eval('return ' . $router->exportToExecutable() . ';')];
    }

    /**
     * @dataProvider  providerHttpExceptionExtended
     *
     * @param RouterInterface $router
     * @param string          $method
     * @param string          $path
     * @param int             $code
     */
    public function testHttpException(RouterInterface $router, string $method, string $path, int $code)
    {
        $this->expectException(HttpException::class);
        $this->expectExceptionCode($code);
        $router->match($method, $path);
    }

    public function providerHttpExceptionExtended()
    {
        foreach ($this->providerHttpException() as $data) {
            /** @var PatternRouter $router */
            $router = $data[0];

            yield $data;

            $data[0] = eval('return ' . $router->exportToExecutable() . ';');
            yield $data;

            $data[0] = (new RouterCollector())->addRouter($router);
            yield $data;

            $data[0] = unserialize(serialize($router));
            yield $data;
        }
    }

    private function providerHttpException()
    {
        foreach ([PatternRouter::STRATEGY_TREE, PatternRouter::STRATEGY_SEQUENTIALLY] as $strategy) {
            $router = new PatternRouter(Patterns::createDefault(), $strategy);
            $router
                ->get('/', 'home')
                ->get('/category-{{ categoryId :int }}', 'category')
                ->put('/new-category', 'newCategory');

            yield [$router, HttpMethods::METHOD_PATCH, '/', HttpException::HTTP_METHOD_NOT_ALLOWED];
            yield [$router, HttpMethods::METHOD_PATCH, '/category-15', HttpException::HTTP_METHOD_NOT_ALLOWED];
            yield [$router, HttpMethods::METHOD_GET, '/new-category', HttpException::HTTP_METHOD_NOT_ALLOWED];
            yield [$router, HttpMethods::METHOD_PATCH, '/ping', HttpException::HTTP_NOT_FOUND];
            yield [$router, HttpMethods::METHOD_PATCH, '/category-books', HttpException::HTTP_NOT_FOUND];
        }
    }

    public function testGetAllowedMethods()
    {
        foreach ($this->providerGetAllowedMethods() as $data) {
            $this->checkAllowedMethods(...$data);
        }
    }

    private function checkAllowedMethods(RouterInterface $router, string $path, array $allowedMethods)
    {
        $this->assertArraysWithSameElements($allowedMethods, $router->getAllowedMethods($path));
    }

    /**
     * @expectedException \Awesomite\Chariot\Exceptions\HttpException
     * @expectedExceptionCode    404
     * @expectedExceptionMessage 404 Not Found: GET /article/title-cannot-contain/slash/
     */
    public function testDefaultParam()
    {
        $router = PatternRouter::createDefault()
            ->get('/article/{{ title }}/', 'showArticle');

        $routeA = $router->match(HttpMethods::METHOD_GET, '/article/hello-world/');
        $this->assertSame('showArticle', $routeA->getHandler());
        $this->assertSame(['title' => 'hello-world'], $routeA->getParams());

        $routeB = $router->match(HttpMethods::METHOD_GET, '/article/hello\\world/');
        $this->assertSame('showArticle', $routeB->getHandler());
        $this->assertSame(['title' => 'hello\\world'], $routeB->getParams());

        $router->match(HttpMethods::METHOD_GET, '/article/title-cannot-contain/slash/');
    }

    private function providerGetAllowedMethods()
    {
        foreach ([PatternRouter::STRATEGY_SEQUENTIALLY, PatternRouter::STRATEGY_TREE] as $strategy) {
            $router = (new PatternRouter(Patterns::createDefault(), $strategy))
                ->get('/', 'home')
                ->get('/articles', 'articles')
                ->put('/new-article', 'newArticle')
                ->get('/ping', 'ping')
                ->post('/ping', 'ping')
                ->delete('/delete', 'deleteSomething')
                ->patch('/patch', 'patchSomething')
                ->any('/any', 'anythingHandler');

            $data = [
                [$router, '/', [HttpMethods::METHOD_GET, HttpMethods::METHOD_HEAD]],
                [$router, '/articles', [HttpMethods::METHOD_GET, HttpMethods::METHOD_HEAD]],
                [$router, '/ping', [HttpMethods::METHOD_GET, HttpMethods::METHOD_HEAD, HttpMethods::METHOD_POST]],
                [$router, '/new-article', [HttpMethods::METHOD_PUT]],
                [$router, '/patch', [HttpMethods::METHOD_PATCH]],
                [$router, '/any', array_diff(HttpMethods::ALL_METHODS, [HttpMethods::METHOD_ANY])],
            ];

            foreach ($data as $row) {
                yield $row;

                $row[0] = eval('return ' . $router->exportToExecutable() . ';');
                yield $row;

                $row[0] = (new RouterCollector())->addRouter($router);
                yield $row;

                $row[0] = unserialize(serialize($router));
                yield $row;
            }
        }
    }
}
