<?php

/*
 * This file is part of the awesomite/chariot package.
 * (c) Bartłomiej Krukowski <bartlomiej@krukowski.me>
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Awesomite\Chariot\Pattern\StdPatterns;

use Awesomite\Chariot\Pattern\Patterns;

class UnsignedIntPattern extends IntPattern
{
    public function getRegex(): string
    {
        return Patterns::REGEX_UINT;
    }
}
