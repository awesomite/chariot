<?php

/*
 * This file is part of the awesomite/chariot package.
 * (c) Bartłomiej Krukowski <bartlomiej@krukowski.me>
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Awesomite\Chariot\Pattern\StdPatterns;

use Awesomite\Chariot\Pattern\Patterns;

class Ip4Pattern extends AbstractPattern
{
    private static $maxIp4Int = 4294967295;

    private $options = FILTER_FLAG_IPV4;

    public function __construct(bool $allowPrivateRange = true, bool $allowReservedRange = true)
    {
        if (!$allowPrivateRange) {
            $this->options |= FILTER_FLAG_NO_PRIV_RANGE;
        }

        if (!$allowReservedRange) {
            $this->options |= FILTER_FLAG_NO_RES_RANGE;
        }
    }

    public function getRegex(): string
    {
        return Patterns::REGEX_IP;
    }

    public function toUrl($data): string
    {
        if (\is_object($data) && \method_exists($data, '__toString')) {
            $data = (string) $data;
        }

        if (\is_string($data)) {
            if ($this->match($data)) {
                return $data;
            }

            $d = Patterns::DELIMITER;
            if (\preg_match($d . '^(' . Patterns::REGEX_INT . ')$' . $d, $data)) {
                $data = (int) $data;
            }
        }

        if (\is_int($data)) {
            if ($data >= 0 && $data <= self::$maxIp4Int && $this->match($result = \long2ip($data))) {
                return $result;
            }
        }

        throw $this->newInvalidToUrl($data);
    }

    public function fromUrl(string $param)
    {
        if ($this->match($param)) {
            return $param;
        }

        throw $this->newInvalidFromUrl($param);
    }

    public function serialize()
    {
        return \serialize($this->options);
    }

    public function unserialize($serialized)
    {
        $this->options = \unserialize($serialized);
    }

    protected function match(string $data): bool
    {
        if (false === \filter_var($data, FILTER_VALIDATE_IP, $this->options)) {
            return false;
        }

        return parent::match($data);
    }
}
