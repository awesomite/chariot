<?php

namespace Awesomite\Chariot;

/**
 * @internal
 */
class StringableObject
{
    private $input;

    public function __construct(string $input)
    {
        $this->input = $input;
    }

    public function __toString()
    {
        return $this->input;
    }
}
