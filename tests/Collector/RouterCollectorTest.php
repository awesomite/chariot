<?php

/*
 * This file is part of the awesomite/chariot package.
 * (c) Bartłomiej Krukowski <bartlomiej@krukowski.me>
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Awesomite\Chariot\Collector;

use Awesomite\Chariot\Exceptions\CannotGenerateLinkException;
use Awesomite\Chariot\Exceptions\HttpException;
use Awesomite\Chariot\HttpMethods;
use Awesomite\Chariot\LinkInterface;
use Awesomite\Chariot\Pattern\PatternRouter;
use Awesomite\Chariot\TestBase;

/**
 * @internal
 */
class RouterCollectorTest extends TestBase
{
    public function testAll()
    {
        $routerHome = PatternRouter::createDefault();
        $routerHome->addRoute(HttpMethods::METHOD_GET, '/', 'home');

        $routerAdmin = PatternRouter::createDefault();
        $routerAdmin->addRoute(HttpMethods::METHOD_GET, '/admin', 'admin', ['page' => 1]);

        $routerCollector = new RouterCollector();
        $routerCollector
            ->addRouter($routerHome)
            ->addRouter($routerAdmin);

        $home = $routerCollector->match(HttpMethods::METHOD_GET, '/');
        $admin = $routerCollector->match(HttpMethods::METHOD_GET, '/admin');

        $this->assertSame('home', $home->getHandler());
        $this->assertSame([], $home->getParams());

        $this->assertSame('admin', $admin->getHandler());
        $this->assertSame(['page' => 1], $admin->getParams());

        $this->assertSame('/', (string) $routerCollector->linkTo('home'));
        $this->assertSame('/', $routerCollector->linkTo('home')->toString());
        $this->assertSame('/admin', (string) $routerCollector->linkTo('admin')->withParam('page', '1'));
        $this->assertSame('/admin', $routerCollector->linkTo('admin')->withParam('page', '1')->toString());
        $this->assertSame(LinkInterface::ERROR_CANNOT_GENERATE_LINK, (string) $routerCollector->linkTo('admin'));
        $executed = false;
        try {
            $routerCollector->linkTo('admin')->toString();
        } catch (CannotGenerateLinkException $exception) {
            $executed = true;
        }
        $this->assertTrue($executed);
    }

    public function testNotFound()
    {
        $this->expectException(HttpException::class);
        $router = new RouterCollector();
        $router->match(HttpMethods::METHOD_GET, 'handler');
    }

    public function testCannotGenerateLink()
    {
        $this->expectException(CannotGenerateLinkException::class);
        $router = new RouterCollector();
        $router->linkTo('handler')->toString();
    }
}
