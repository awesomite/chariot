<?php

namespace Awesomite\Chariot\Pattern;

use Awesomite\Chariot\TestBase;

/**
 * @internal
 */
class RegexTesterTest extends TestBase
{
    /**
     * @dataProvider providerRegex
     *
     * @param RegexTester $tester
     * @param string      $regex
     * @param bool        $expected
     */
    public function testRegex(RegexTester $tester, string $regex, bool $expected)
    {
        $lastError = error_get_last();
        $this->assertSame($expected, $tester->isRegex($regex));
        $this->assertSame($lastError, error_get_last());
    }

    public function providerRegex()
    {
        $tester = new RegexTester();

        return [
            [$tester, '^.*#', false],
            [$tester, '#^.*$#', true],
            [$tester, '{^.*$}', true],
        ];
    }
}
