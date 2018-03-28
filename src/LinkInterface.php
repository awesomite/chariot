<?php

/*
 * This file is part of the awesomite/chariot package.
 * (c) Bartłomiej Krukowski <bartlomiej@krukowski.me>
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Awesomite\Chariot;

use Awesomite\Chariot\Exceptions\CannotGenerateLinkException;

interface LinkInterface
{
    const ERROR_CANNOT_GENERATE_LINK = '__ERROR_CANNOT_GENERATE_LINK';

    public function withParam(string $key, $value): self;

    public function withParams(array $params): self;

    /**
     * Works same as __toString() method with one exception:
     * throws exception in case of error.
     *
     * @return string
     *
     * @throws CannotGenerateLinkException
     */
    public function toString(): string;

    /**
     * PHP does not allow to throw an exception from within __toString() method.
     * Because of this fact __toString() method returns LinkInterface::ERROR_CANNOT_GENERATE_LINK in case of error.
     *
     * @see http://php.net/manual/en/language.oop5.magic.php#object.tostring
     * @see LinkInterface::ERROR_CANNOT_GENERATE_LINK
     *
     * @return string
     */
    public function __toString(): string;

    public function withPrefix(string $prefix): self;
}
