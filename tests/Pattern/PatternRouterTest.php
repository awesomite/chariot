<?php

namespace Awesomite\Chariot\Pattern;

use Awesomite\Chariot\Exceptions\HttpException;
use Awesomite\Chariot\Exceptions\InvalidArgumentException;
use Awesomite\Chariot\HttpMethods;
use Awesomite\Chariot\LinkInterface;
use Awesomite\Chariot\TestBase;

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
     * @dataProvider getRouters
     *
     * @param PatternRouter $router
     */
    public function testGeneral(PatternRouter $router)
    {
        $router
            ->any('/show-first-page', 'showPage', ['page' => 1])
            ->any('/show-page-{{ page :uint }}', 'showPage')
            ->get('/comment/{{ id :int }}', 'getComment')
            ->post('/comment/{{ id :int }}', 'editComment')
            ->put('/comment', 'createComment')
            ->delete('/comment/{{ id :int }}', 'editComment')
            ->get('/article-{{ id :uint }}', 'getPostArticle')
            ->post('/article-{{ id :uint }}', 'getPostArticle');

        foreach ([HttpMethods::METHOD_GET, HttpMethods::METHOD_POST, HttpMethods::METHOD_HEAD] as $method) {
            $route = $router->match($method, '/article-7');
            $this->assertSame(['id' => '7'], $route->getParams());
            $this->assertSame('getPostArticle', $route->getHandler());
        }

        foreach ([HttpMethods::METHOD_GET, HttpMethods::METHOD_PUT, HttpMethods::METHOD_DELETE] as $method) {
            $route = $router->match($method, '/show-first-page');
            $this->assertSame(['page' => 1], $route->getParams());
            $this->assertSame('showPage', $route->getHandler());

            $route2 = $router->match($method, '/show-page-15');
            $this->assertSame(['page' => '15'], $route2->getParams());
            $this->assertSame('showPage', $route2->getHandler());
        }

        $thrown405 = false;
        try {
            $router->match(HttpMethods::METHOD_PUT, '/comment/15');
        } catch (HttpException $exception) {
            if ($exception->getCode() === HttpException::HTTP_METHOD_NOT_ALLOWED) {
                $thrown405 = true;
            }
        }
        $this->assertTrue($thrown405);

        $thrown404 = false;
        try {
            $router->match(HttpMethods::METHOD_GET, '/error-404');
        } catch (HttpException $exception) {
            if ($exception->getCode() === HttpException::HTTP_NOT_FOUND) {
                $thrown404 = true;
            }
        }
        $this->assertTrue($thrown404);

        $this->assertSame(
            '/comment/7',
            (string)$router->linkTo('getComment')->withParam('id', 7)
        );
        $this->assertSame(
            '/comment/10',
            (string)$router->linkTo('getComment')->withParam('id', $this->createStringable(10))
        );
        $this->assertSame(
            '/comment/7',
            (string)$router->linkTo('getComment', HttpMethods::METHOD_GET)->withParam('id', 7)
        );
        $this->assertSame(
            LinkInterface::ERROR_CANNOT_GENERATE_LINK,
            (string)$router->linkTo('getComment', HttpMethods::METHOD_PUT)->withParam('id', 7)
        );
        $this->assertSame(
            LinkInterface::ERROR_CANNOT_GENERATE_LINK,
            (string)$router->linkTo('getComment')
        );
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
                return (string)$this->value;
            }
        };
    }

    public function getRouters()
    {
        return [
            [new PatternRouter(Patterns::createDefault(), PatternRouter::STRATEGY_SEQUENTIALLY)],
            [new PatternRouter(Patterns::createDefault(), PatternRouter::STRATEGY_TREE)],
        ];
    }
}
