<?php

namespace Awesomite\Chariot\Exceptions;

class CannotGenerateLinkException extends LogicException
{
    public function __construct(string $handler, array $params, $code = 0, \Throwable $previous = null)
    {
        $message = "Cannot generate link for {$handler}";
        if ($params) {
            $message .= '?' . urldecode(http_build_query($params));
        }
        parent::__construct($message, $code, $previous);
    }
}
