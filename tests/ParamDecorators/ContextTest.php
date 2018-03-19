<?php

namespace Awesomite\Chariot\ParamDecorators;

use Awesomite\Chariot\TestBase;

/**
 * @internal
 */
class ContextTest extends TestBase
{
    public function testContext()
    {
        $handler = 'testHandler';
        $method = 'PUT';
        $params = ['foo' => 'bar'];
        $required = ['firstParam', 'secondParam'];
        $context = new Context($handler, $method, $params, $required);
        
        $this->assertSame($handler, $context->getHandler());
        $this->assertSame($method, $context->getMethod());
        $this->assertSame($params, $context->getParams());
        $this->assertSame($required, $context->getRequiredParams());
        
        $context->setParam('foo', 'bar2');
        $this->assertNotSame($params, $context->getParams());
        $this->assertSame(['foo' => 'bar2'], $context->getParams());
        
        $context->removeParam('foo');
        $this->assertSame([], $context->getParams());
    }
}
