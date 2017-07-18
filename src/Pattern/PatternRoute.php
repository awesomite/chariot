<?php

namespace Awesomite\Chariot\Pattern;

use Awesomite\Chariot\Exceptions\InvalidArgumentException;
use Awesomite\Chariot\ExportableTrait;
use Awesomite\Chariot\Link;
use Awesomite\Chariot\LinkInterface;

/**
 * @internal
 */
class PatternRoute
{
    use ExportableTrait;

    const PATTERN_VAR = '/{{.*?}}/';

    /**
     * Human-friendly pattern, i.e. "/{{ controller }}"
     *
     * @var string
     */
    private $pattern;

    /**
     * Regex, i.e. "#^/(?<controller>[^\/]*)$#"
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
     * [$name = [$default, $pattern], ...]
     * i.e. ['id' => [null, '#^-?[0-9]+$#'], ...]
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

        if (!(new RegexTester())->isRegex($this->compiledPattern)) {
            throw new InvalidArgumentException(sprintf(
                '%s is not valid regular expression, source pattern is equal to %s',
                $this->compiledPattern,
                $this->pattern
            ));
        }
    }

    private function compilePattern()
    {
        $originalPattern = $this->pattern;

        $this->compiledPattern = preg_replace_callback(
            static::PATTERN_VAR,
            function ($matches) use ($originalPattern) {
                $str = substr($matches[0], 2, -2);
                $arr = array_filter(preg_split('/\\s+/', $str), function ($a) {
                    return trim($a) !== '';
                });
                $arr = array_values($arr);
                switch (count($arr)) {
                    case 1:
                    case 2:
                    case 3:
                        while (count($arr) < 2) {
                            $arr[] = null;
                        }

                        list($name, $pattern) = $arr;

                        if (is_null($pattern)) {
                            $pattern = $this->patterns->getDefaultPattern();
                        }
                        break;

                    default:
                        throw new InvalidArgumentException("Invalid url pattern {$originalPattern}!");
                }

                if (!preg_match('/^[a-zA-Z0-9_]+$/', $name)) {
                    throw new InvalidArgumentException("Invalid param name “{$name}” (source: {$originalPattern}).");
                }

                $pattern = $this->patterns[$pattern] ?? $pattern;

                if (!(new RegexTester())->isRegex("#{$pattern}#")) {
                    throw new InvalidArgumentException("Incorrect regex {$pattern} (source: {$originalPattern}).");
                }

                return is_null($name) ? "({$pattern})" : "(?<{$name}>{$pattern})";
            },
            $this->pattern
        );

        $this->compiledPattern = "#^{$this->compiledPattern}$#";
    }

    private function extractParams()
    {
        $params = &$this->explodedParams;
        $params = [];
        $inputPattern = $this->pattern;
        $this->simplePattern = preg_replace_callback(
            static::PATTERN_VAR,
            function ($matches) use (&$params, $inputPattern) {
                $str = substr($matches[0], 2, -2);
                $arr = array_filter(preg_split('/\\s+/', $str), function ($a) {
                    return trim($a) !== '';
                });
                $arr = array_values($arr);
                switch (count($arr)) {
                    case 1:
                    case 2:
                    case 3:
                        while (count($arr) < 3) {
                            $arr[] = null;
                        }
                        list($name, $pattern, $default) = $arr;
                        if (is_null($pattern)) {
                            $pattern = $this->patterns->getDefaultPattern();
                        }
                        break;

                    default:
                        throw new InvalidArgumentException("Invalid url pattern {$inputPattern}!");
                }

                $pattern = $this->patterns[$pattern] ?? $pattern;

                $params[$name] = [$default, '#^(' . $pattern . ')$#'];

                return '{{' . $name . '}}';
            },
            $this->pattern
        );
    }

    public function match(string $path, &$params): bool
    {
        if ($result = (bool)preg_match($this->compiledPattern, $path, $matches)) {
            $params = array_filter(
                $matches,
                function ($key) {
                    return !is_int($key);
                },
                ARRAY_FILTER_USE_KEY
            );
        }

        return $result;
    }

    /**
     * @param string[] $params
     *
     * @return bool
     */
    public function matchParams(array $params): bool
    {
        foreach ($this->explodedParams as $name => list($default, $pattern)) {
            $exists = array_key_exists($name, $params);

            if ((is_null($default) && !$exists) || ($exists && !preg_match($pattern, $params[$name]))) {
                return false;
            }
        }

        return true;
    }

    public function bindParams(array $params): LinkInterface
    {
        $result = $this->simplePattern;
        foreach ($this->explodedParams as $name => list($default, $pattern)) {
            $value = $params[$name] ?? $default;
            $result = str_replace('{{' . $name . '}}', $value, $result);
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

        while (strlen($pattern) > 0) {
            if (substr($pattern, 0, 2) === '{{') {
                $result[] = new PatternRouteNode($pattern, true);
                break;
            }

            $result[] = new PatternRouteNode(substr($pattern, 0, 1), false);
            $pattern = substr($pattern, 1);
        }

        return $result;
    }
}
