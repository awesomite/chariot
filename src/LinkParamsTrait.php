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
trait LinkParamsTrait
{
    private $params = [];

    private $prefix = '';

    public function withPrefix(string $prefix): LinkInterface
    {
        $this->prefix = $prefix;

        return $this;
    }

    public function withParams(array $params): LinkInterface
    {
        foreach ($params as $key => $value) {
            $this->withParam($key, $value);
        }

        return $this;
    }

    public function withParam(string $key, $value): LinkInterface
    {
        $this->params[$key] = $value;

        return $this;
    }
}
