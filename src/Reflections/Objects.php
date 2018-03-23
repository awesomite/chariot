<?php

/*
 * This file is part of the awesomite/chariot package.
 * (c) Bartłomiej Krukowski <bartlomiej@krukowski.me>
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Awesomite\Chariot\Reflections;

/**
 * @internal
 */
class Objects
{
    /**
     * Reads also private and protected properties
     *
     * @param        $object
     * @param string $propertyName
     *
     * @return mixed
     */
    public static function getProperty($object, string $propertyName)
    {
        $reflection = new \ReflectionObject($object);
        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);

        return $property->getValue($object);
    }
}
