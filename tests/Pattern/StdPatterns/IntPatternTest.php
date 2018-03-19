<?php

/*
 * This file is part of the awesomite/chariot package.
 * (c) Bartłomiej Krukowski <bartlomiej@krukowski.me>
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Awesomite\Chariot\Pattern\StdPatterns;

use Awesomite\Chariot\Pattern\PatternInterface;
use Awesomite\Chariot\StringableObject;

/**
 * @internal
 */
class IntPatternTest extends AbstractPatternTest
{
    public function getPattern(): PatternInterface
    {
        return new IntPattern();
    }

    public function providerToUrl()
    {
        return [
            [-0xf, '-15'],
            [-23, '-23'],
            [0, '0'],
            [PHP_INT_MAX, (string) PHP_INT_MAX],
            [new StringableObject('15'), '15'],
            [new StringableObject('-15'), '-15'],
        ];
    }

    public function providerInvalidToUrl()
    {
        return [
            ['1.0'],
            [\pi()],
            [new \stdClass()],
        ];
    }

    public function providerFromUrl()
    {
        return [
            ['5', 5],
            ['-5', -5],
        ];
    }
}
