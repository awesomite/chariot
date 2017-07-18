<?php

namespace Awesomite\Chariot\Pattern;

use Awesomite\Chariot\Exceptions\InvalidArgumentException;
use Awesomite\Chariot\Exceptions\LogicException;
use Awesomite\Chariot\ExportableTrait;

class Patterns implements PatternsInterface
{
    const REGEX_INT = '(-?[1-9][0-9]*)|0';
    const REGEX_UINT = '([1-9][0-9]*)|0';
    const REGEX_ALPHANUM = '[a-zA-Z0-9]+';
    const REGEX_DEFAULT = '[^\\/]+';

    const STANDARD_PATTERNS = [
        ':int'      => self::REGEX_INT,
        ':uint'     => self::REGEX_UINT,
        ':alphanum' => self::REGEX_ALPHANUM,
    ];

    use ExportableTrait;

    private $patterns = [];

    private $defaultPattern;

    public function __construct(array $patterns = [], string $defaultPattern = null)
    {
        foreach ($patterns as $name => $pattern) {
            $this->addPattern($name, $pattern);
        }

        $this->setDefaultPattern(is_null($defaultPattern) ? static::REGEX_DEFAULT : $defaultPattern);
    }

    public function addPattern(string $name, string $regex): PatternsInterface
    {
        if (isset($this[$name])) {
            throw new LogicException(sprintf('Pattern %s is already added', $name));
        }

        if (!(new RegexTester())->isSubregex($regex)) {
            throw new InvalidArgumentException('Invalid regex: ' . $regex);
        }

        $this->patterns[$name] = $regex;

        return $this;
    }

    public static function createDefault(): Patterns
    {
        return new self(static::STANDARD_PATTERNS, static::REGEX_DEFAULT);
    }

    public function addEnumPattern(string $name, array $values): PatternsInterface
    {
        $processed = [];
        foreach ($values as $value) {
            $processed[] = preg_quote($value, '#');
        }

        return $this->addPattern($name, implode('|', $processed));
    }

    public function getDefaultPattern()
    {
        return $this->defaultPattern;
    }

    public function setDefaultPattern(string $pattern): PatternsInterface
    {
        if (!(new RegexTester())->isSubregex($pattern)) {
            throw new InvalidArgumentException('Invalid regex: ' . $pattern);
        }
        $this->defaultPattern = $pattern;

        return $this;
    }

    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->patterns);
    }

    public function offsetGet($offset)
    {
        return $this->patterns[$offset];
    }

    public function offsetSet($offset, $value)
    {
        $this->addPattern($offset, $value);
    }

    public function offsetUnset($offset)
    {
        throw new LogicException('Operation forbidden');
    }
}
