<?php

namespace Awesomite\Chariot\Pattern;

use Awesomite\Chariot\Exceptions\InvalidArgumentException;

interface PatternsInterface extends \ArrayAccess
{
    /**
     * @param string $name
     * @param string $regex
     *
     * @return PatternsInterface
     *
     * @throws InvalidArgumentException
     */
    public function addPattern(string $name, string $regex): PatternsInterface;

    /**
     * @param string   $name
     * @param string[] $values
     *
     * @return PatternsInterface
     *
     * @throws InvalidArgumentException
     */
    public function addEnumPattern(
        string $name,
        array $values
    ): PatternsInterface;

    /**
     * @param string $pattern
     *
     * @return PatternsInterface
     *
     * @throws InvalidArgumentException
     */
    public function setDefaultPattern(string $pattern): PatternsInterface;

    public function getDefaultPattern();
}
