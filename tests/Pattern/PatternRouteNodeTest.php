<?php

/*
 * This file is part of the awesomite/chariot package.
 * (c) Bartłomiej Krukowski <bartlomiej@krukowski.me>
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Awesomite\Chariot\Pattern;

use Awesomite\Chariot\TestBase;

/**
 * @internal
 */
class PatternRouteNodeTest extends TestBase
{
    /**
     * @dataProvider providerGeneral
     *
     * @param string $key
     * @param bool   $isRegex
     */
    public function testGeneral(string $key, bool $isRegex)
    {
        $node = new PatternRouteNode($key, $isRegex);
        $this->assertSame($key, $node->getKey());
        $this->assertSame($isRegex, $node->isRegex());
    }

    public function providerGeneral()
    {
        return [
            ['a', false],
            ['a{{ id :int }}', true],
        ];
    }
}
