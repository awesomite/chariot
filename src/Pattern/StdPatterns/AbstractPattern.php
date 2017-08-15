<?php

namespace Awesomite\Chariot\Pattern\StdPatterns;

use Awesomite\Chariot\Exceptions\PatternException;
use Awesomite\Chariot\ExportableTrait;
use Awesomite\Chariot\Pattern\PatternInterface;

abstract class AbstractPattern implements PatternInterface
{
    use ExportableTrait;

    public function serialize()
    {
        return '';
    }

    public function unserialize($serialized)
    {
    }

    /**
     * @param $data
     *
     * @throws PatternException
     */
    protected function throwInvalidToUrl($data)
    {
        switch (gettype($data)) {
            case 'string':
                $type = sprintf('(string) %s', var_export($data, true));
                break;

            case 'object':
                $type = sprintf('(object) %s', get_class($data));
                break;

            case 'integer':
            case 'double':
            case 'float':
                $type = sprintf('(%s) %s', gettype($data), var_export($data, true));
                break;

            default:
                $type = gettype($data);
                break;
        }

        throw new PatternException(sprintf('Value %s cannot be converted to url param (%s)', $type, static::class));
    }

    /**
     * @param string $param
     *
     * @throws PatternException
     */
    protected function throwInvalidFromUrl(string $param)
    {
        throw new PatternException(sprintf('Value %s is invalid (%s)', $param, static::class));
    }

    protected function match(string $data) : bool
    {
        return (bool) preg_match('#^' . $this->getRegex() . '$#', $data);
    }
}
