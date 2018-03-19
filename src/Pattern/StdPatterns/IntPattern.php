<?php

/*
 * This file is part of the awesomite/chariot package.
 * (c) Bartłomiej Krukowski <bartlomiej@krukowski.me>
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

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
        if (\is_scalar($data) || (\is_object($data) && \method_exists($data, '__toString'))) {
            $result = (string) $data;
            if ($this->match($result)) {
                return $result;
            }
        }

        throw $this->newInvalidToUrl($data);
    }

    public function fromUrl(string $param)
    {
        return (int) $param;
    }
}
