<?php

namespace Awesomite\Chariot\Pattern\StdPatterns;

use Awesomite\Chariot\LinkInterface;
use Awesomite\Chariot\Pattern\PatternRouter;
use Awesomite\Chariot\Pattern\Patterns;
use Awesomite\Chariot\Pattern\StringableObject;
use Awesomite\Chariot\RouterInterface;
use Awesomite\Chariot\TestBase;

/**
 * @internal
 */
class RegexPatternTest extends TestBase
{
    /**
     * @dataProvider providerInvalidToUrl
     *
     * @param $nonDigit
     */
    public function testInvalidToUrl($nonDigit)
    {
        $this->assertSame(
            LinkInterface::ERROR_CANNOT_GENERATE_LINK,
            (string) $this->createRouter()->linkTo('getItem')->withParam('id', $nonDigit)
        );
    }

    public function providerInvalidToUrl()
    {
        return [
            [new \stdClass()],
            [[]],
            [new StringableObject('eleven')],
        ];
    }

    /**
     * @dataProvider providerToUrl
     *
     * @param        $digitable
     * @param string $expectedUrl
     */
    public function testToUrl($digitable, string $expectedUrl)
    {
        $this->assertSame(
            $expectedUrl,
            (string) $this->createRouter()->linkTo('getItem')->withParam('id', $digitable)
        );
    }

    public function providerToUrl()
    {
        return [
            [new StringableObject('1'), '/item-1'],
            [11, '/item-11'],
            ['123', '/item-123'],
        ];
    }

    private function createRouter(): RouterInterface
    {
        $router = new PatternRouter(new Patterns());
        $router->getPatterns()->addPattern(':number', '\\d+');
        $router->get('/item-{{ id :number }}', 'getItem');

        return $router;
    }
}
