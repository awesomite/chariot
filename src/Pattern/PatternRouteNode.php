<?php

/*
 * This file is part of the awesomite/chariot package.
 * (c) Bartłomiej Krukowski <bartlomiej@krukowski.me>
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

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
