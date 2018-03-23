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
use Awesomite\Chariot\Exceptions\PatternException;
use Awesomite\Chariot\ExportableTrait;
use Awesomite\Chariot\Link;
use Awesomite\Chariot\LinkInterface;

/**
 * @internal
 */
class PatternRoute
{
    use ExportableTrait;

    const PATTERN_VAR = '/{{.*?}?}}/';

    /**
     * Human-friendly pattern, i.e. "/{{ controller }}"
     *
     * @var string
     */
    private $pattern;

    /**
     * Regex, e.g. "#^/(?<controller>[^/]+)$#"
     *
     * @var string
     */
    private $compiledPattern;

    /**
     * Pattern without types declaration, i.e. /controller/{{action}}
     *
     * @var string
     */
    private $simplePattern;

    /**
     * [$name = [$default, $pattern, $patternName|null], ...]
     * e.g. ['id' => [null, '#^-?[0-9]+$#', ':int'], ...]
     *
     * @var array
     */
    private $explodedParams;

    /**
     * @var PatternsInterface
     */
    private $patterns;

    public function __construct(string $pattern, PatternsInterface $patterns)
    {
        $this->pattern = $pattern;
        $this->patterns = $patterns;
        $this->processPattern();
    }
    
    public function getRequiredParams(): array
    {
        return \array_keys($this->explodedParams);
    }

    private function processPattern()
    {
        $simplePattern = $this->pattern;

        $toCompile = $this->pattern;
        $compiledParts = [];
        $explodedParams = [];

        $usedNames = [];

        foreach ($this->processTokens() as list($token, $name, $pattern, $default, $patternName)) {
            if (\in_array($name, $usedNames, true)) {
                throw new LogicException(\sprintf(
                    'Parameter %s has been redeclared (source: %s)',
                    $name,
                    $this->pattern
                ));
            }
            $usedNames[] = $name;

            // simple pattern /item-{{id}}
            $simplePattern = $this->replaceFirst($token, '{{' . $name . '}}', $simplePattern);

            // compiled pattern #^/item-(?<id>([1-9][0-9]*)|0)$#"
            $exploded = \explode($token, $toCompile, 2);
            $compiledParts[] = \preg_quote($exploded[0], Patterns::DELIMITER);
            $compiledParts[] = "(?<{$name}>{$pattern})";
            $compiledParts[] = \preg_quote($exploded[1], Patterns::DELIMITER);

            $explodedParams[$name] = [
                $default,
                Patterns::DELIMITER . '^(' . $pattern . ')$' . Patterns::DELIMITER,
                $patternName
            ];
        }


        if (0 === \count($compiledParts)) {
            $simplePattern = $this->pattern;
            $compiledParts[] = \preg_quote($this->pattern, Patterns::DELIMITER);
        }
        $d = Patterns::DELIMITER;
        $compiledPattern = $d . '^' . \implode('', $compiledParts) . '$' . $d;

        $this->simplePattern = $simplePattern;
        $this->compiledPattern = $compiledPattern;
        $this->explodedParams = $explodedParams;
    }

    private function replaceFirst(string $search, string $replace, string $subject): string
    {
        $exploded = \explode($search, $subject, 2);

        return $exploded[0] . $replace . $exploded[1];
    }

    /**
     * Validates, change pattern name to regex and add pattern's name or null
     *
     * e.g. ['{{ month :int }}', 'name', '(-?[1-9][0-9]*)|0', null, ':int']
     *
     * @return \Generator
     */
    private function processTokens()
    {
        foreach ($this->getTokensStream() as list($string, $name, $pattern, $default)) {
            if (!\preg_match('/^[a-zA-Z0-9_]+$/', $name)) {
                throw new InvalidArgumentException("Invalid param name “{$name}” (source: {$this->pattern})");
            }

            Patterns::validatePatternName($name);

            $patternName = null;
            if ($patternObj = $this->patterns[$pattern] ?? null) {
                $patternName = $pattern;
                /** @var PatternInterface $patternObj */
                $pattern = $patternObj->getRegex();
            }

            if (!(new RegexTester())->isSubregex($pattern)) {
                throw new InvalidArgumentException("Invalid regex: {$pattern} (source: {$this->pattern})");
            }

            yield [
                $string,
                $name,
                $pattern,
                $default,
                $patternName,
            ];
        }
    }

    /**
     * e.g. [
     *     // [text,            name,    pattern,  default]
     *     ['{{ month :int }}', 'month', ':int', null],
     * ]
     *
     * @return array
     */
    private function getTokensStream(): array
    {
        $preProcessed = [];
        \preg_replace_callback(
            static::PATTERN_VAR,
            function ($matches) use (&$preProcessed) {
                $arr = $this->paramStrToArr($matches[0]);

                if (\count($arr) > 3) {
                    throw new InvalidArgumentException("Invalid url pattern {$this->pattern}");
                }

                $name = $arr[0];
                $pattern = $arr[1] ?? $this->patterns->getDefaultPattern();
                $default = $arr[2] ?? null;

                $preProcessed[] = [
                    $matches[0],
                    $name,
                    $pattern,
                    $default
                ];
            },
            $this->pattern
        );

        return $preProcessed;
    }

    /**
     * @param string $paramString e.g. {{ id :int }}
     *
     * @return string[] e.g. ['id', 'int']
     */
    private function paramStrToArr(string $paramString)
    {
        $str = \substr($paramString, 2, -2);
        $result = \array_filter(\preg_split('/\\s+/', $str), function ($a) {
            return '' !== \trim($a);
        });

        return \array_values($result);
    }

    public function match(string $path, &$params): bool
    {
        if ($result = (bool) \preg_match($this->compiledPattern, $path, $matches)) {
            $resultParams = \array_filter(
                $matches,
                function ($key) {
                    return !\is_int($key);
                },
                ARRAY_FILTER_USE_KEY
            );

            foreach ($resultParams as $key => $value) {
                $patternName = $this->explodedParams[$key][2];
                if (!\is_null($patternName)) {
                    $pattern = $this->patterns[$patternName];
                    try {
                        $resultParams[$key] = $pattern->fromUrl($value);
                    } catch (PatternException $exception) {
                        return false;
                    }
                }
            }

            $params = $resultParams;

            return true;
        }

        return false;
    }

    /**
     * Returns false or converted params
     *
     * @param string[] $params
     *
     * @return bool|array
     */
    public function matchParams(array $params)
    {
        $result = [];
        foreach ($this->explodedParams as $name => list($default, $pattern, $patternName)) {
            if (!\array_key_exists($name, $params)) {
                if (\is_null($default)) {
                    return false;
                }
                $currentParam = $default;
            } else {
                $currentParam = $params[$name];
            }

            if (!\is_null($patternName)) {
                $patternObj = $this->patterns[$patternName];

                try {
                    $result[$name] = $patternObj->toUrl($currentParam);
                } catch (PatternException $exception) {
                    return false;
                }
            } else {
                if (\is_object($currentParam) && \method_exists($currentParam, '__toString')) {
                    $currentParam = (string) $currentParam;
                }
                if (!$this->pregMatchMultiType($pattern, $currentParam)) {
                    return false;
                }
                $result[$name] = $currentParam;
            }
        }

        return $result;
    }

    public function bindParams(array $params): LinkInterface
    {
        $result = $this->simplePattern;
        foreach ($this->explodedParams as $name => list($default)) {
            $value = $params[$name] ?? $default;
            $result = \str_replace('{{' . $name . '}}', $value, $result);
            unset($params[$name]);
        }

        return (new Link($result))->withParams($params);
    }

    /**
     * @return PatternRouteNode[]
     */
    public function getNodes(): array
    {
        $result = [];
        $pattern = $this->pattern;

        while (\strlen($pattern) > 0) {
            if ('{{' === \substr($pattern, 0, 2)) {
                $result[] = new PatternRouteNode($pattern, true);
                break;
            }

            $result[] = new PatternRouteNode(\substr($pattern, 0, 1), false);
            $pattern = \substr($pattern, 1);
        }

        return $result;
    }

    private function pregMatchMultiType(string $pattern, $subject): int
    {
        if (
            \is_string($subject)
            || \is_scalar($subject)
            || \is_null($subject)
        ) {
            return \preg_match($pattern, (string) $subject);
        }

        return 0;
    }
}
