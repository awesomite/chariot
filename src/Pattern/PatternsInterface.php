<?php

namespace Awesomite\Chariot\Pattern;

use Awesomite\Chariot\Exceptions\InvalidArgumentException;

interface PatternsInterface extends \ArrayAccess, \Serializable
{
    /**
     * @param string                  $name
     * @param string|PatternInterface $pattern Acceptable also stringable object (with method __toString)
     *
     * @return PatternsInterface
     *
     * @throws InvalidArgumentException
     */
    public function addPattern(string $name, $pattern): PatternsInterface;

    /**
     * @param string   $name
     * @param string[] $values
     *
     * @return PatternsInterface
     *
     * @throws InvalidArgumentException
     */
    public function addEnumPattern(string $name, array $values): PatternsInterface;

    /**
     * @param string|PatternInterface $pattern
     *
     * @return PatternsInterface
     *
     * @throws InvalidArgumentException
     */
    public function setDefaultPattern($pattern): PatternsInterface;

    public function getDefaultPattern();

    /**
     * @param mixed $offset
     *
     * @return PatternInterface
     */
    public function offsetGet($offset);
}
