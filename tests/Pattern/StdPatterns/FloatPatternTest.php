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
class FloatPatternTest extends AbstractPatternTest
{
    public function providerFromUrl()
    {
        return [
            ['0.153000', .153],
            ['123.000', 123.0],
            ['123.001', 123.001],
            ['100', 100.0],
            ['-0', 0.0],
        ];
    }

    public function providerToUrl()
    {
        $data = [
            ['0.00', '0'],
            [.0, '0'],
            [153, '153'],
            ['-1.20', '-1.2'],
        ];

        foreach ($data as list($input, $expected)) {
            yield [$input, $expected];
            yield [new StringableObject((string) $input), $expected];
        }
    }

    public function providerInvalidToUrl()
    {
        return [
            ['0b1111'],
            [new \stdClass()],
        ];
    }

    public function getPattern(): PatternInterface
    {
        return new FloatPattern();
    }
}
