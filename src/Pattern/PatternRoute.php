<?php

/*
 * This file is part of the awesomite/chariot package.
 * (c) Bartłomiej Krukowski <bartlomiej@krukowski.me>
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Awesomite\Chariot\Pattern;

use Awesomite\Chariot\Exceptions\InvalidArgumentException;
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
        $this->compilePattern();
        $this->extractParams();
    }
    
    public function getRequiredParams(): array
    {
        return \array_keys($this->explodedParams);
    }

    private function compilePattern()
    {
        $originalPattern = $this->pattern;

        /** @var array $preProcessedParams [['{{ id \d+ }}', '(?<id>\d+)'], ...] */
        $preProcessedParams = [];

        \preg_replace_callback(
            static::PATTERN_VAR,
            function ($matches) use ($originalPattern, &$preProcessedParams) {
                $str = \substr($matches[0], 2, -2);
                $arr = \array_filter(\preg_split('/\\s+/', $str), function ($a) {
                    return '' !== \trim($a);
                });
                $arr = \array_values($arr);
                switch (\count($arr)) {
                    case 1:
                    case 2:
                    case 3:
                        while (\count($arr) < 2) {
                            $arr[] = null;
                        }

                        list($name, $pattern) = $arr;

                        if (\is_null($pattern)) {
                            $pattern = $this->patterns->getDefaultPattern();
                        }
                        break;

                    default:
                        throw new InvalidArgumentException("Invalid url pattern {$originalPattern}");
                }

                if (!\preg_match('/^[a-zA-Z0-9_]+$/', $name)) {
                    throw new InvalidArgumentException("Invalid param name “{$name}” (source: {$originalPattern})");
                }

                if ($patternObj = $this->patterns[$pattern] ?? null) {
                    /** @var PatternInterface $patternObj */
                    $pattern = $patternObj->getRegex();
                }

                if (!(new RegexTester())->isSubregex($pattern)) {
                    throw new InvalidArgumentException("Invalid regex: {$pattern} (source: {$originalPattern})");
                }

                $preProcessedParams[] = [$matches[0], "(?<{$name}>{$pattern})"];

                return $matches[0];
            },
            $this->pattern
        );

        $uriPattern = $this->pattern;
        $resultParts = [];

        foreach ($preProcessedParams as list($original, $replacement)) {
            $exploded = \explode($original, $uriPattern, 2);
            $resultParts[] = \preg_quote($exploded[0], Patterns::DELIMITER);
            $resultParts[] = $replacement;
            $uriPattern = $exploded[1];
        }
        if ('' !== $uriPattern) {
            $resultParts[] = \preg_quote($uriPattern, Patterns::DELIMITER);
        }

        $d = Patterns::DELIMITER;
        $this->compiledPattern = $d . '^' . \implode('', $resultParts) . '$' . $d;
    }

    private function extractParams()
    {
        $params = &$this->explodedParams;
        $params = [];
        $inputPattern = $this->pattern;
        $this->simplePattern = \preg_replace_callback(
            static::PATTERN_VAR,
            function ($matches) use (&$params, $inputPattern) {
                $str = \substr($matches[0], 2, -2);
                $arr = \array_filter(\preg_split('/\\s+/', $str), function ($a) {
                    return '' !== \trim($a);
                });
                $arr = \array_values($arr);
                switch (\count($arr)) {
                    case 1:
                    case 2:
                    case 3:
                        while (\count($arr) < 3) {
                            $arr[] = null;
                        }
                        list($name, $pattern, $default) = $arr;
                        if (\is_null($pattern)) {
                            $pattern = $this->patterns->getDefaultPattern();
                        }
                        break;

                    default:
                        // @codeCoverageIgnoreStart
                        throw new InvalidArgumentException("Invalid url pattern {$inputPattern}");
                    // @codeCoverageIgnoreEnd
                }

                $patternName = null;
                if ($patternObj = $this->patterns[$pattern] ?? null) {
                    $patternName = $pattern;
                    /** @var PatternInterface $patternObj */
                    $pattern = $patternObj->getRegex();
                }

                $params[$name] = [
                    $default,
                    Patterns::DELIMITER . '^(' . $pattern . ')$' . Patterns::DELIMITER,
                    $patternName,
                ];

                return '{{' . $name . '}}';
            },
            $this->pattern
        );
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
