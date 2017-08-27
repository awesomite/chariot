<?php

namespace Awesomite\Chariot\Pattern\StdPatterns;

class ListPattern extends AbstractPattern
{
    public function getRegex(): string
    {
        return '[^/]+(,[^/]+)*';
    }

    public function toUrl($data): string
    {
        $normalized = $this->normalizeToUrl($data);

        if (is_string($normalized)) {
            if (!$this->match($normalized)) {
                throw $this->newInvalidToUrl($data);
            }

            return $normalized;
        }

        if (is_array($normalized) && !empty($normalized)) {
            $result = implode(',', $normalized);
            if ($this->match($result)) {
                return $result;
            }
        }

        throw $this->newInvalidToUrl($data);
    }

    public function fromUrl(string $param)
    {
        return explode(',', $param);
    }

    private function normalizeToUrl($data)
    {
        if (is_object($data)) {
            if ($data instanceof \Traversable) {
                return iterator_to_array($data);
            }

            if (method_exists($data, '__toString')) {
                return (string) $data;
            }
        }

        if (is_array($data)) {
            return $data;
        }

        if (is_scalar($data)) {
            return (string) $data;
        }

        throw $this->newInvalidToUrl($data);
    }
}
