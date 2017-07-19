<?php

namespace Awesomite\Chariot\Exceptions;

class HttpException extends \Exception implements ChariotException
{
    const HTTP_NOT_FOUND = 404;
    const HTTP_METHOD_NOT_ALLOWED = 405;

    public function __construct(string $method, string $path, int $code, \Throwable $previous = null)
    {
        parent::__construct(
            $this->translateCode($code) . ": {$method} {$path}",
            $code,
            $previous
        );
    }

    private function translateCode(int $code): string
    {
        switch ($code) {
            case static::HTTP_NOT_FOUND:
                return '404 Not Found';

            case static::HTTP_METHOD_NOT_ALLOWED:
                return '405 Method Not Allowed';
        }

        throw new InvalidArgumentException("Code {$code} cannot be translated");
    }
}
