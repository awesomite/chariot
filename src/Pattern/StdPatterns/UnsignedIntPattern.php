<?php

namespace Awesomite\Chariot\Pattern\StdPatterns;

use Awesomite\Chariot\Pattern\Patterns;

class UnsignedIntPattern extends IntPattern
{
    public function getRegex(): string
    {
        return Patterns::REGEX_UINT;
    }
}
