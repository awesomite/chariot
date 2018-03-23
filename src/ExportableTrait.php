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
trait ExportableTrait
{
    /**
     * @internal
     *
     * @param $data
     *
     * @return object
     */
    public static function __set_state($data)
    {
        $reflection = new \ReflectionClass(static::class);
        $result = $reflection->newInstanceWithoutConstructor();
        foreach ($data as $key => $value) {
            $result->$key = $value;
        }

        return $result;
    }
}
