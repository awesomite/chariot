<?php

/*
 * This file is part of the awesomite/chariot package.
 * (c) Bartłomiej Krukowski <bartlomiej@krukowski.me>
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

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
