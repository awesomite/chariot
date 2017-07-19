<?php

namespace Awesomite\Chariot\Pattern;

use Awesomite\Chariot\Exceptions\CannotGenerateLinkException;
use Awesomite\Chariot\Exceptions\HttpException;
use Awesomite\Chariot\Exceptions\InvalidArgumentException;
use Awesomite\Chariot\HttpMethods;
use Awesomite\Chariot\InternalRoute;
use Awesomite\Chariot\InternalRouteInterface;
use Awesomite\Chariot\LinkInterface;
use Awesomite\Chariot\TestBase;

/**
 * @internal
 */
class PatternRouterTest extends TestBase
{
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

    public function testGetPatterns()
    {
        $patterns = new Patterns();
        $router = new PatternRouter($patterns);
        $this->assertSame($patterns, $router->getPatterns());
    }

    /**
     * @dataProvider providerMatch
     * 
     * @param PatternRouter $router
     * @param array $methods
     * @param string $path
     * @param InternalRouteInterface $expectedRoute
     */
    public function testMatch(PatternRouter $router, array $methods, string $path, InternalRouteInterface $expectedRoute)
    {
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
                new InternalRoute('getPostArticle', ['id' => '7']),
            ];

            yield [
                $router,
                [HttpMethods::METHOD_GET, HttpMethods::METHOD_PUT, HttpMethods::METHOD_DELETE],
                '/show-first-page',
                new InternalRoute('showPage', ['page' => 1])
            ];

            yield [
                $router,
                [HttpMethods::METHOD_GET, HttpMethods::METHOD_PUT, HttpMethods::METHOD_DELETE],
                '/show-page-15',
                new InternalRoute('showPage', ['page' => '15'])
            ];
        }
    }

    /**
     * @dataProvider providerMethodNotAllowed
     * 
     * @param PatternRouter $router
     * @param string $method
     * @param string $path
     * @param array $allowedMethods
     */
    public function testMethodNotAllowed(PatternRouter $router, string $method, string $path, array $allowedMethods)
    {
        $this->assertArraysWithSameElements($allowedMethods, $router->getAllowedMethods($path));
        
        $this->expectException(HttpException::class);
        $this->expectExceptionMessage(sprintf('405 Method Not Allowed: %s %s', $method, $path));
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
                [HttpMethods::METHOD_GET, HttpMethods::METHOD_HEAD, HttpMethods::METHOD_POST, HttpMethods::METHOD_DELETE],
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
     * @param string $method
     * @param string $path
     */
    public function testNotFound(PatternRouter $router, string $method, string $path)
    {
        $this->expectException(HttpException::class);
        $this->expectExceptionCode(HttpException::HTTP_NOT_FOUND);
        $this->expectExceptionMessage(sprintf('404 Not Found: %s %s', $method, $path));
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
                    '/not-found'
                ];

                yield [
                    $router,
                    $method,
                    '/comment/first'
                ];
            }   
        }
    }

    /**
     * @dataProvider providerAllAllowedMethods
     * 
     * @param PatternRouter $router
     * @param $path
     */
    public function testAllAllowedMethods(PatternRouter $router, $path)
    {
        $this->assertArraysWithSameElements(
            array_diff(HttpMethods::ALL_METHODS, [HttpMethods::METHOD_ANY]),
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
     * @param string|null $method
     * @param string $handler
     * @param array $params
     * @param string $expectedLink
     */
    public function testLinkTo(PatternRouter $router, $method, string $handler, array $params, string $expectedLink)
    {
        $link = is_null($method) 
            ? $router->linkTo($handler)
            : $router->linkTo($handler, $method);
        $this->assertInstanceOf(LinkInterface::class, $link);
        $link = $link->withParams($params);
        $this->assertInstanceOf(LinkInterface::class, $link);
        $generatedLink = (string) $link;
        $this->assertSame($expectedLink, $generatedLink);
        
        if ($generatedLink === LinkInterface::ERROR_CANNOT_GENERATE_LINK) {
            $this->expectException(CannotGenerateLinkException::class);
            $this->expectExceptionMessageRegExp('#^' . preg_quote('Cannot generate link for ', '.*#') . '#');
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
                LinkInterface::ERROR_CANNOT_GENERATE_LINK
            ];
            yield [$router, HttpMethods::METHOD_HEAD, 'invalidHandler', [], LinkInterface::ERROR_CANNOT_GENERATE_LINK];
        }
        
        $router = PatternRouter::createDefault();
        $router
            ->get('/page/{{page \d+ 1}}', 'showPage');
        yield [
            $router,
            null,
            'showPage',
            ['page' => 1],
            '/page/1'
        ];
    }

    private function getEmptyRouters()
    {
        return [
            new PatternRouter(Patterns::createDefault(), PatternRouter::STRATEGY_SEQUENTIALLY),
            new PatternRouter(Patterns::createDefault(), PatternRouter::STRATEGY_TREE),
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
}
