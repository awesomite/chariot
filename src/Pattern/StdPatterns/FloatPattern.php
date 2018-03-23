<?php

/*
 * This file is part of the awesomite/chariot package.
 * (c) Bartłomiej Krukowski <bartlomiej@krukowski.me>
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Awesomite\Chariot\Pattern\StdPatterns;

use Awesomite\Chariot\Pattern\Patterns;

class FloatPattern extends AbstractPattern
{
    public function getRegex(): string
    {
        return Patterns::REGEX_FLOAT;
    }

    public function toUrl($data): string
    {
        $normalized = $this->normalizeInput($data);

        if ($this->isValidFloat($normalized)) {
            return $this->convertFloatToString($normalized);
        }

        if (\is_string($normalized) && $this->match($normalized)) {
            return $normalized;
        }

        throw $this->newInvalidToUrl($data);
    }

    public function fromUrl(string $param)
    {
        return (float) $param;
    }

    protected function isValidFloat($data): bool
    {
        return \is_float($data);
    }

    private function rtrimRedundantZeros(string $float): string
    {
        if (false !== \strpos($float, '.')) {
            return \rtrim(\rtrim($float, '0'), '.');
        }

        return $float;
    }

    private function normalizeInput($input)
    {
        if (\is_string($input) || (\is_object($input) && \method_exists($input, '__toString'))) {
            return $this->rtrimRedundantZeros((string) $input);
        }

        if (\is_int($input) || \is_float($input)) {
            return (float) $input;
        }

        return $input;
    }

    private function convertFloatToString(float $input): string
    {
        return $this->rtrimRedundantZeros(\sprintf('%.20f', $input));
    }
}
