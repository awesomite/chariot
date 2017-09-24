<?php

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
        set_error_handler(function () {
        }, E_ALL);
        $test = @preg_match($regex, '');
        restore_error_handler();

        return false !== $test;
    }
}
