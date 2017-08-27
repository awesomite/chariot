<?php

namespace Awesomite\Chariot\Pattern\StdPatterns;

use Awesomite\Chariot\Pattern\PatternInterface;
use Awesomite\Chariot\StringableObject;

/**
 * @internal
 */
class UnsignedFloatPatternTest extends AbstractPatternTest
{
    public function providerFromUrl()
    {
        return [
            ['0.153000', .153],
            ['123.000', 123.0],
            ['123.001', 123.001],
            ['100', 100.0],
        ];
    }

    public function providerToUrl()
    {
        $data = [
            ['0.00', '0'],
            [.0, '0'],
            [153, '153'],
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
            ['-2'],
            [-1],
            [-.000001],
        ];
    }

    public function getPattern(): PatternInterface
    {
        return new UnsignedFloatPattern();
    }
}
