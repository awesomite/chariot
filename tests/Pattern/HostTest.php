<?php

namespace Awesomite\Chariot\Pattern;

use Awesomite\Chariot\HttpMethods;
use Awesomite\Chariot\TestBase;

/**
 * @internal
 */
class HostTest extends TestBase
{
    /**
     * @dataProvider providerGeneral
     *
     * @param PatternRouter $router
     */
    public function testGeneral(PatternRouter $router)
    {
        $router->get('{{ subdomain }}.local', 'subdomainHandler');
        $route = $router->match(HttpMethods::METHOD_GET, 'jane.local');
        $this->assertSame('jane', $route->getParams()['subdomain']);
        $this->assertSame('subdomainHandler', $route->getHandler());
    }

    public function providerGeneral()
    {
        return [
            [new PatternRouter(Patterns::createDefault(), PatternRouter::STRATEGY_TREE)],
            [new PatternRouter(Patterns::createDefault(), PatternRouter::STRATEGY_SEQUENTIALLY)],
        ];
    }
}
