<?php

namespace Awesomite\Chariot\Pattern\StdPatterns;

use Awesomite\Chariot\Pattern\Patterns;

class UnsignedFloatPattern extends FloatPattern
{
    public function getRegex(): string
    {
        return Patterns::REGEX_UFLOAT;
    }

    protected function isValidFloat($data): bool
    {
        return parent::isValidFloat($data) && 0 <= $data;
    }
}
