<?php

/*
 * This file is part of the awesomite/chariot package.
 * (c) Bartłomiej Krukowski <bartlomiej@krukowski.me>
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Awesomite\Chariot\Pattern;

/**
 * @internal
 */
class RegexTester
{
    public function isSubregex(string $subregex, string $delimiter = Patterns::DELIMITER): bool
    {
        return $this->isRegex($delimiter . $subregex . $delimiter);
    }

    public function isRegex(string $regex): bool
    {
        \set_error_handler(function () {
        }, E_ALL);
        $test = @\preg_match($regex, '');
        \restore_error_handler();

        return false !== $test;
    }
}
