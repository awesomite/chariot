<?php

/*
 * This file is part of the awesomite/chariot package.
 * (c) Bartłomiej Krukowski <bartlomiej@krukowski.me>
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Awesomite\Chariot\Pattern;

use Awesomite\Chariot\Exceptions\InvalidArgumentException;
use Awesomite\Chariot\Exceptions\LogicException;
use Awesomite\Chariot\Pattern\StdPatterns\DatePattern;
use Awesomite\Chariot\Pattern\StdPatterns\FloatPattern;
use Awesomite\Chariot\Pattern\StdPatterns\IntPattern;
use Awesomite\Chariot\Pattern\StdPatterns\Ip4Pattern;
use Awesomite\Chariot\Pattern\StdPatterns\ListPattern;
use Awesomite\Chariot\Pattern\StdPatterns\RegexPattern;
use Awesomite\Chariot\Pattern\StdPatterns\UnsignedFloatPattern;
use Awesomite\Chariot\Pattern\StdPatterns\UnsignedIntPattern;

class Patterns implements PatternsInterface
{
    const DELIMITER = '#';

    const REGEX_INT      = '(-?[1-9][0-9]*)|0';
    const REGEX_UINT     = '([1-9][0-9]*)|0';
    const REGEX_FLOAT    = '((-?[1-9][0-9]*)|0)(\.[0-9]*[1-9]+)?';
    const REGEX_UFLOAT   = '(([1-9][0-9]*)|0)(\.[0-9]*[1-9]+)?';
    const REGEX_ALPHANUM = '[a-zA-Z0-9]+';
    const REGEX_DATE     = '[0-9]{4}-[0-9]{2}-[0-9]{2}';
    const REGEX_IP       = '((25[0-5])|(2[0-4][0-9])|(1[0-9][0-9])|([1-9]?[0-9]))(\.((25[0-5])|(2[0-4][0-9])|(1[0-9][0-9])|([1-9]?[0-9]))){3}';
    const REGEX_DEFAULT  = '[^/]+';

    const STANDARD_PATTERNS
        = [
            ':int'      => IntPattern::class,
            ':uint'     => UnsignedIntPattern::class,
            ':float'    => FloatPattern::class,
            ':ufloat'   => UnsignedFloatPattern::class,
            ':date'     => DatePattern::class,
            ':list'     => ListPattern::class,
            ':ip4'      => Ip4Pattern::class,
            ':alphanum' => self::REGEX_ALPHANUM,
        ];

    private $patterns = [];

    private $defaultPattern;

    private $frozen = false;

    /**
     * @internal
     * @param string $name
     */
    public static function validatePatternName(string $name)
    {
        $max = 32;
        if (\strlen($name) > $max) {
            throw new InvalidArgumentException(\sprintf(
                'Compilation failed: subpattern name is too long (maximum %d characters) [%s]',
                $max,
                $name
            ));
        }
    }

    public function __construct(array $patterns = [], string $defaultPattern = null)
    {
        foreach ($patterns as $name => $pattern) {
            if (\is_array($pattern)) {
                $this->addEnumPattern($name, $pattern);
            } else {
                $this->addPattern($name, $pattern);
            }
        }

        $this->setDefaultPattern(\is_null($defaultPattern) ? static::REGEX_DEFAULT : $defaultPattern);
    }

    public function addPattern(string $name, $pattern): PatternsInterface
    {
        if ($this->frozen) {
            throw new LogicException(\sprintf('Object `%s` is frozen, cannot add new patterns', static::class));
        }

        static::validatePatternName($name);

        if (isset($this[$name])) {
            throw new LogicException(\sprintf('Pattern %s is already added', $name));
        }

        if (':' !== ($name[0] ?? null)) {
            throw new InvalidArgumentException(\sprintf(
                'Method %s() requires first parameter prefixed by ":", "%s" given',
                __METHOD__,
                $name
            ));
        }

        if (\substr_count($name, ':') > 1) {
            throw new InvalidArgumentException(\sprintf(
                'Pattern\'s name cannot contain `:`, except first character, given `%s`',
                $name
            ));
        }

        if (
            \is_string($pattern)
            || (\is_object($pattern) && \method_exists($pattern, '__toString') && !$pattern instanceof PatternInterface)
        ) {
            $pattern = new RegexPattern((string) $pattern);
        }

        if (\is_object($pattern) && $pattern instanceof PatternInterface) {
            if (!(new RegexTester())->isSubregex($pattern->getRegex())) {
                throw new InvalidArgumentException('Invalid regex: ' . $pattern->getRegex());
            }
            $this->patterns[$name] = $pattern;
        } else {
            throw new InvalidArgumentException(\sprintf(
                'Method %s() expects string or %s, %s given',
                __METHOD__,
                PatternInterface::class,
                \is_object($pattern) ? \get_class($pattern) : \gettype($pattern)
            ));
        }

        return $this;
    }

    public static function createDefault(): self
    {
        $result = new self();
        foreach (self::STANDARD_PATTERNS as $name => $pattern) {
            if (\class_exists($pattern)) {
                $pattern = new $pattern();
            }
            $result->addPattern($name, $pattern);
        }

        return $result;
    }

    public function addEnumPattern(string $name, array $values): PatternsInterface
    {
        $processed = [];
        foreach ($values as $value) {
            $processed[] = \preg_quote($value, static::DELIMITER);
        }

        return $this->addPattern($name, \implode('|', $processed));
    }

    public function getDefaultPattern()
    {
        return $this->defaultPattern;
    }

    public function setDefaultPattern($pattern): PatternsInterface
    {
        if (!(new RegexTester())->isSubregex($pattern)) {
            throw new InvalidArgumentException('Invalid regex: ' . $pattern);
        }
        $this->defaultPattern = $pattern;

        return $this;
    }

    public function offsetExists($offset)
    {
        return \array_key_exists($offset, $this->patterns);
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

    public function serialize()
    {
        return \serialize([
            'patterns'       => $this->patterns,
            'defaultPattern' => $this->defaultPattern,
        ]);
    }

    public function unserialize($serialized)
    {
        $data = \unserialize($serialized);
        $this->patterns = $data['patterns'];
        $this->defaultPattern = $data['defaultPattern'];
        $this->frozen = true;
    }
}
