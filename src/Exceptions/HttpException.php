<?php

namespace Awesomite\Chariot\Exceptions;

class HttpException extends \Exception implements ChariotException
{
    const HTTP_NOT_FOUND          = 404;
    const HTTP_METHOD_NOT_ALLOWED = 405;

    private static $translations
        = [
            self::HTTP_NOT_FOUND          => '404 Not Found',
            self::HTTP_METHOD_NOT_ALLOWED => '405 Method Not Allowed',
        ];

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
        if (isset(self::$translations[$code])) {
            return self::$translations[$code];
        }

        throw new InvalidArgumentException("Code {$code} cannot be translated");
    }
}
