<?php

namespace Awesomite\Chariot\Pattern\StdPatterns;

use Awesomite\Chariot\Exceptions\InvalidArgumentException;
use Awesomite\Chariot\Pattern\RegexTester;

class RegexPattern extends AbstractPattern
{
    protected $regex = '';

    public function __construct(string $regex)
    {
        if (!(new RegexTester())->isSubregex($regex)) {
            throw new InvalidArgumentException('Invalid regex: ' . $regex);
        }

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
            $this->throwInvalidToUrl($data);
        }

        if (!$this->match((string) $data)) {
            $this->throwInvalidToUrl($data);
        }

        return (string) $data;
    }

    public function fromUrl(string $param)
    {
        if (!$this->match($param)) {
            $this->throwInvalidFromUrl($param);
        }

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
