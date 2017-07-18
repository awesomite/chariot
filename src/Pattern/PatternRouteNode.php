<?php

namespace Awesomite\Chariot\Pattern;

use Awesomite\Chariot\ExportableTrait;

/**
 * @internal
 */
class PatternRouteNode
{
    use ExportableTrait;

    private $key;

    private $regex;

    public function __construct(string $key, bool $isRegex)
    {
        $this->key = $key;
        $this->regex = $isRegex;
    }

    public function isRegex(): bool
    {
        return $this->regex;
    }

    public function getKey(): string
    {
        return $this->key;
    }
}
