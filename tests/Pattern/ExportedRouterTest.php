<?php

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
