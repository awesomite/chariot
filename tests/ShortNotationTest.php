<?php

/*
 * This file is part of the awesomite/chariot package.
 * (c) Bartłomiej Krukowski <bartlomiej@krukowski.me>
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Awesomite\Chariot;

use Awesomite\Chariot\Exceptions\InvalidArgumentException;
use Awesomite\Chariot\Pattern\PatternRouter;

/**
 * @internal
 */
class ShortNotationTest extends TestBase
{
    public function testShortNotation()
    {
        $router = PatternRouter::createDefault();
        $router->get('/category-{{id:uint}}', 'category');
        $router->get('/item-{{ id:int }}', 'item');

        $this->assertSame('/category-5', (string)$router->linkTo('category')->withParam('id', 5));
        $this->assertSame('/item-27', (string)$router->linkTo('item')->withParam('id', 27));
    }

    /**
     * @dataProvider providerInvalidShortNotation
     *
     * @param string $pattern
     * @param string $expectedMsg
     */
    public function testInvalidShortNotation(string $pattern, string $expectedMsg)
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedMsg);

        $router = PatternRouter::createDefault();
        $router->get($pattern, 'category');
    }

    public function providerInvalidShortNotation()
    {
        return [
            ['/category-{{id[0-9]+}}', 'Invalid param name “id[0-9]+” (source: /category-{{id[0-9]+}})'],
            ['/category-{{ id[0-9]+ }}', 'Invalid param name “id[0-9]+” (source: /category-{{ id[0-9]+ }})'],
        ];
    }
}
