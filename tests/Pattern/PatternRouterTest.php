<?php

/*
 * This file is part of the awesomite/chariot package.
 * (c) Bartłomiej Krukowski <bartlomiej@krukowski.me>
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Awesomite\Chariot\Pattern;

use Awesomite\Chariot\Exceptions\CannotGenerateLinkException;
use Awesomite\Chariot\Exceptions\HttpException;
use Awesomite\Chariot\Exceptions\InvalidArgumentException;
use Awesomite\Chariot\HttpMethods;
use Awesomite\Chariot\InternalRoute;
use Awesomite\Chariot\InternalRouteInterface;
use Awesomite\Chariot\LinkInterface;
use Awesomite\Chariot\StringableObject;
use Awesomite\Chariot\TestBase;

/**
 * @internal
 */
class PatternRouterTest extends TestBase
{
    use ProviderEmptyRouterTrait;

    public function testInvalidConstructor()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid strategy: 4');
        new PatternRouter(Patterns::createDefault(), 4);
    }

    public function testInvalidMethod()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageRegExp('/^Method is equal to .*, but must be equal to one of the following: .*$/');
        $router = PatternRouter::createDefault();
        $router->addRoute('NEW_METHOD', '/', 'home');
    }

    public function testUnnamedCannotMatch()
    {
        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('404 Not Found: GET /category-books');
        $this->expectExceptionCode(404);

        $router = PatternRouter::createDefault();
        $router->get('/category-{{ id \\d+ }}', 'showCategory');
        $router->match('GET', '/category-books');
    }

    public function testUnnamedLinkTo()
    {
        $router = PatternRouter::createDefault();
        $router->get('/category-{{ id \\d+ 1 }}', 'showCategory');

        $invalidLink = (string) $router->linkTo('showCategory')->withParam('id', 'books');
        $this->assertSame(LinkInterface::ERROR_CANNOT_GENERATE_LINK, $invalidLink);

        $invalidLink2 = (string) $router->linkTo('showCategory')->withParam('id', new \stdClass());
        $this->assertSame(LinkInterface::ERROR_CANNOT_GENERATE_LINK, $invalidLink2);

        $validLink = (string) $router->linkTo('showCategory');
        $this->assertSame('/category-1', $validLink);

        $validLink2 = (string) $router->linkTo('showCategory')->withParam('id', 2);
        $this->assertSame('/category-2', $validLink2);
    }

    public function testGetPatterns()
    {
        $patterns = new Patterns();
        $router = new PatternRouter($patterns);
        $this->assertSame($patterns, $router->getPatterns());
    }

    /**
     * @dataProvider providerMatch
     *
     * @param PatternRouter          $router
     * @param array                  $methods
     * @param string                 $path
     * @param InternalRouteInterface $expectedRoute
     */
    public function testMatch(
        PatternRouter $router,
        array $methods,
        string $path,
        InternalRouteInterface $expectedRoute
    ) {
        foreach ($methods as $method) {
            $route = $router->match($method, $path);
            $this->assertSame($expectedRoute->getHandler(), $route->getHandler());
            $this->assertSame($expectedRoute->getParams(), $route->getParams());
        }
    }

    public function providerMatch()
    {
        foreach ($this->getEmptyRouters() as $router) {
            $this->decorateRouter($router);

            yield [
                $router,
                [HttpMethods::METHOD_GET, HttpMethods::METHOD_POST, HttpMethods::METHOD_HEAD],
                '/article-7',
                new InternalRoute('getPostArticle', ['id' => 7]),
            ];

            yield [
                $router,
                [HttpMethods::METHOD_GET, HttpMethods::METHOD_PUT, HttpMethods::METHOD_DELETE],
                '/show-first-page',
                new InternalRoute('showPage', ['page' => 1]),
            ];

            yield [
                $router,
                [HttpMethods::METHOD_GET, HttpMethods::METHOD_PUT, HttpMethods::METHOD_DELETE],
                '/show-page-15',
                new InternalRoute('showPage', ['page' => 15]),
            ];
        }
    }

    /**
     * @dataProvider providerMethodNotAllowed
     *
     * @param PatternRouter $router
     * @param string        $method
     * @param string        $path
     * @param array         $allowedMethods
     */
    public function testMethodNotAllowed(PatternRouter $router, string $method, string $path, array $allowedMethods)
    {
        $this->assertArraysWithSameElements($allowedMethods, $router->getAllowedMethods($path));

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage(\sprintf('405 Method Not Allowed: %s %s', $method, $path));
        $this->expectExceptionCode(HttpException::HTTP_METHOD_NOT_ALLOWED);
        $router->match($method, $path);
    }

    public function providerMethodNotAllowed()
    {
        foreach ($this->getEmptyRouters() as $router) {
            $this->decorateRouter($router);

            yield [
                $router,
                HttpMethods::METHOD_PUT,
                '/comment/15',
                [
                    HttpMethods::METHOD_GET,
                    HttpMethods::METHOD_HEAD,
                    HttpMethods::METHOD_POST,
                    HttpMethods::METHOD_DELETE,
                ],
            ];

            yield [
                $router,
                HttpMethods::METHOD_HEAD,
                '/comment',
                [HttpMethods::METHOD_PUT],
            ];
        }
    }

    /**
     * @dataProvider providerNotFound
     *
     * @param PatternRouter $router
     * @param string        $method
     * @param string        $path
     */
    public function testNotFound(PatternRouter $router, string $method, string $path)
    {
        $this->expectException(HttpException::class);
        $this->expectExceptionCode(HttpException::HTTP_NOT_FOUND);
        $this->expectExceptionMessage(\sprintf('404 Not Found: %s %s', $method, $path));
        $router->match($method, $path);
    }

    public function providerNotFound()
    {
        foreach ($this->getEmptyRouters() as $router) {
            $this->decorateRouter($router);
            foreach (HttpMethods::ALL_METHODS as $method) {
                yield [
                    $router,
                    $method,
                    '/not-found',
                ];

                yield [
                    $router,
                    $method,
                    '/comment/first',
                ];
            }
        }
    }

    /**
     * @dataProvider providerAllAllowedMethods
     *
     * @param PatternRouter $router
     * @param               $path
     */
    public function testAllAllowedMethods(PatternRouter $router, $path)
    {
        $this->assertArraysWithSameElements(
            \array_diff(HttpMethods::ALL_METHODS, [HttpMethods::METHOD_ANY]),
            $router->getAllowedMethods($path)
        );
    }

    public function providerAllAllowedMethods()
    {
        foreach ($this->getEmptyRouters() as $router) {
            $this->decorateRouter($router);

            yield [$router, '/show-first-page'];
            yield [$router, '/show-page-75'];
            yield [$router, '/any'];
        };
    }

    /**
     * @dataProvider providerLinkTo
     *
     * @param PatternRouter $router
     * @param string|null   $method
     * @param string        $handler
     * @param array         $params
     * @param string        $expectedLink
     */
    public function testLinkTo(PatternRouter $router, $method, string $handler, array $params, string $expectedLink)
    {
        $link = \is_null($method)
            ? $router->linkTo($handler)
            : $router->linkTo($handler, $method);
        $this->assertInstanceOf(LinkInterface::class, $link);
        $link = $link->withParams($params);
        $this->assertInstanceOf(LinkInterface::class, $link);
        $generatedLink = (string) $link;
        $this->assertSame($expectedLink, $generatedLink);

        if (LinkInterface::ERROR_CANNOT_GENERATE_LINK === $generatedLink) {
            $this->expectException(CannotGenerateLinkException::class);
            $this->expectExceptionMessageRegExp('#^' . \preg_quote('Cannot generate link for ', '.*#') . '#');
        }

        $this->assertSame($expectedLink, $link->toString());
    }

    public function providerLinkTo()
    {
        foreach ($this->getEmptyRouters() as $router) {
            $this->decorateRouter($router);

            yield [$router, null, 'showPage', ['page' => 1], '/show-first-page'];
            yield [$router, HttpMethods::METHOD_GET, 'showPage', ['page' => 1], '/show-first-page'];
            yield [$router, HttpMethods::METHOD_GET, 'getPostArticle', ['id' => 1], '/article-1'];
            yield [$router, HttpMethods::METHOD_HEAD, 'getPostArticle', ['id' => 1], '/article-1'];
            yield [$router, HttpMethods::METHOD_POST, 'getPostArticle', ['id' => 1], '/article-1'];
            yield [$router, null, 'getPostArticle', ['id' => 1], '/article-1'];
            yield [
                $router,
                HttpMethods::METHOD_PUT,
                'getPostArticle',
                ['id' => 1],
                LinkInterface::ERROR_CANNOT_GENERATE_LINK,
            ];
            yield [$router, HttpMethods::METHOD_HEAD, 'invalidHandler', [], LinkInterface::ERROR_CANNOT_GENERATE_LINK];

            $router->get('/test-array-object', 'arrayObject', ['array' => new \ArrayObject(['foo' => 'bar'])]);
            yield [
                $router,
                HttpMethods::METHOD_GET,
                'arrayObject',
                ['array' => new \ArrayObject(['foo' => 'bar'])],
                '/test-array-object',
            ];

            $router->get('/test-stringable', 'stringable', ['text' => new StringableObject('my-string')]);
            yield [
                $router,
                HttpMethods::METHOD_GET,
                'stringable',
                ['text' => new StringableObject('my-string')],
                '/test-stringable',
            ];
        }

        $router = PatternRouter::createDefault();
        $router
            ->get('/page/{{page \d+ 1}}', 'showPage');
        yield [
            $router,
            null,
            'showPage',
            ['page' => 1],
            '/page/1',
        ];
    }

    /**
     * @dataProvider providerInvalidExtraParams
     *
     * @param $param
     */
    public function testInvalidExtraParams($param)
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageRegExp(
            '#Additional parameters can contain only scalar or null values, ".*" given#'
        );
        $this->expectExceptionCode(0);

        $router = PatternRouter::createDefault();
        $router->get('/', 'home', [$param]);
    }

    public function providerInvalidExtraParams()
    {
        return [
            [\tmpfile()],
            [new \stdClass()],
        ];
    }

    private function decorateRouter(PatternRouter $router)
    {
        $router
            ->any('/show-first-page', 'showPage', ['page' => 1])
            ->any('/show-page-{{ page :uint }}', 'showPage')
            ->get('/comment/{{ id :int }}', 'getComment')
            ->post('/comment/{{ id :int }}', 'editComment')
            ->put('/comment', 'createComment')
            ->delete('/comment/{{ id :int }}', 'editComment')
            ->get('/article-{{ id :uint }}', 'getPostArticle')
            ->post('/article-{{ id :uint }}', 'getPostArticle')
            ->any('/any', 'showAny');
    }

    public function testRegisterRouteWithObjects()
    {
        $router = PatternRouter::createDefault();
        $extraParamsBooks = [
            'data' => new \ArrayObject(['categoryId' => new StringableObject('15')]),
        ];
        $router->get('/category-books', 'showCategory', $extraParamsBooks);
        foreach (['15', 15, new StringableObject('15')] as $categoryId) {
            $this->assertSame(
                '/category-books',
                (string) $router->linkTo('showCategory')->withParam('data', ['categoryId' => $categoryId])
            );
        }
    }

    /**
     * @dataProvider providerMethodAlias
     *
     * @param string $httpMethod
     * @param string $phpMethod
     */
    public function testMethodAlias(string $httpMethod, string $phpMethod)
    {
        $router = PatternRouter::createDefault();
        $router->$phpMethod('/show-something', 'showSomething');
        $this->assertSame('showSomething', $router->match($httpMethod, '/show-something')->getHandler());
    }

    public function providerMethodAlias()
    {
        return [
            [HttpMethods::METHOD_TRACE, 'trace'],
            [HttpMethods::METHOD_OPTIONS, 'options'],
            [HttpMethods::METHOD_CONNECT, 'connect'],
        ];
    }
}
