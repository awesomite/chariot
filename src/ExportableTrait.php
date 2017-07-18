<?php

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
