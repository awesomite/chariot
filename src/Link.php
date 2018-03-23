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
class Link implements LinkInterface
{
    use LinkParamsTrait;

    private $base;

    public function __construct(string $base)
    {
        $this->base = $base;
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    public function toString(): string
    {
        return $this->prefix . $this->base .
            ($this->params ? '?' . \urldecode(\http_build_query($this->normalizeParams($this->params))) : '');
    }

    private function normalizeParams($params)
    {
        \array_walk_recursive($params, function (&$value) {
            if (\is_object($value)) {
                if (\method_exists($value, '__toString')) {
                    $value = (string) $value;

                    return;
                }

                if ($value instanceof \Traversable) {
                    $value = $this->normalizeParams(\iterator_to_array($value));

                    return;
                }
            }
        });

        return $params;
    }
}
