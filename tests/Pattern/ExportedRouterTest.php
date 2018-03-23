<?php

/*
 * This file is part of the awesomite/chariot package.
 * (c) Bartłomiej Krukowski <bartlomiej@krukowski.me>
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Awesomite\Chariot\Pattern;

use Awesomite\Chariot\HttpMethods;
use Awesomite\Chariot\RouterInterface;
use Awesomite\Chariot\TestBase;

/**
 * @internal
 */
class ExportedRouterTest extends TestBase
{
    public function testExportRouter()
    {
        $router = PatternRouter::createDefault();
        $router->addRoute(HttpMethods::METHOD_GET, '/foo', 'handleFoo');
        $router->addRoute(HttpMethods::METHOD_GET, '/bar', 'handleBar');

        /** @var PatternRouter $restoredRouter */
        $restoredRouter = eval('return ' . $router->exportToExecutable() . ';');

        $this->assertInstanceOf(RouterInterface::class, $restoredRouter);
        $this->assertNotSame($router, $restoredRouter);
        $link = (string) $restoredRouter->linkTo('handleBar');
        $this->assertSame('/bar', $link);
    }
}
