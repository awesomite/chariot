<?php

namespace Awesomite\Chariot\Pattern\StdPatterns;

use Awesomite\Chariot\Pattern\Patterns;

class IntPattern extends AbstractPattern
{
    public function getRegex(): string
    {
        return Patterns::REGEX_INT;
    }

    public function toUrl($data): string
    {
        if (!$this->match((string) $data)) {
            throw $this->newInvalidToUrl($data);
        }

        return (string) $data;
    }

    public function fromUrl(string $param)
    {
        return (int) $param;
    }
}
