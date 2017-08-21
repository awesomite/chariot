<?php

namespace Awesomite\Chariot;

use Awesomite\Chariot\Exceptions\CannotGenerateLinkException;
use Awesomite\Chariot\Pattern\PatternRouter;
use Awesomite\Chariot\Pattern\StringableObject;

/**
 * @intrnal
 */
class EdgeCaseTest extends TestBase
{
    public function testNonScalar()
    {
        $router = PatternRouter::createDefault();
        $router->get('/article/{{ id :int }}', 'showArticle');
        $this->expectException(CannotGenerateLinkException::class);
        $this->expectExceptionMessage('Cannot generate link for showArticle');
        $router->linkTo('showArticle')->withParam('id', [])->toString();
    }

    /**
     * @dataProvider providerArrayObjectAndStringable
     *
     * @param array  $params
     * @param string $expectedLink
     */
    public function testArrayObjectAndStringable(array $params, string $expectedLink)
    {
        $router = PatternRouter::createDefault();
        $router->get('/', 'home');
        $this->assertSame(
            $expectedLink,
            (string) $router->linkTo('home')->withParams($params)
        );

    }

    public function providerArrayObjectAndStringable()
    {
        yield [['params' => new \ArrayObject(['foo' => 'bar'])], '/?params[foo]=bar'];
        yield [['params' => new \ArrayObject(['foo' => new StringableObject('bar')])], '/?params[foo]=bar'];
    }
}
