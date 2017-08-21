<?php

namespace Awesomite\Chariot\Pattern\StdPatterns;

/**
 * @internal
 */
class RegexPattern extends AbstractPattern
{
    protected $regex = '';

    public function __construct(string $regex)
    {
        $this->regex = $regex;
    }

    public function getRegex(): string
    {
        return $this->regex;
    }

    public function toUrl($data): string
    {
        if (
            !is_scalar($data)
            && !is_null($data)
            && !(is_object($data) && method_exists($data, '__toString'))
        ) {
            throw $this->newInvalidToUrl($data);
        }

        if (!$this->match((string) $data)) {
            throw $this->newInvalidToUrl($data);
        }

        return (string) $data;
    }

    public function fromUrl(string $param)
    {
        return $param;
    }

    public function serialize()
    {
        return $this->regex;
    }

    public function unserialize($serialized)
    {
        $this->regex = $serialized;
    }
}
