<?php

/*
 * This file is part of the awesomite/chariot package.
 * (c) Bartłomiej Krukowski <bartlomiej@krukowski.me>
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Awesomite\Chariot\Pattern;

use Awesomite\Chariot\Exceptions\InvalidArgumentException;

interface PatternsInterface extends \ArrayAccess, \Serializable
{
    /**
     * @param string                  $name
     * @param string|PatternInterface $pattern Acceptable also stringable object (with method __toString)
     *
     * @return self
     *
     * @throws InvalidArgumentException
     */
    public function addPattern(string $name, $pattern): self;

    /**
     * @param string   $name
     * @param string[] $values
     *
     * @return self
     *
     * @throws InvalidArgumentException
     */
    public function addEnumPattern(string $name, array $values): self;

    /**
     * @param string|PatternInterface $pattern
     *
     * @return self
     *
     * @throws InvalidArgumentException
     */
    public function setDefaultPattern($pattern): self;

    public function getDefaultPattern();

    /**
     * @param mixed $offset
     *
     * @return self
     */
    public function offsetGet($offset);
}
