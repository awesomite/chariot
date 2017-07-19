<?php

namespace Awesomite\Chariot\Exceptions;

class CannotGenerateLinkException extends LogicException
{
    public function __construct(string $handler, array $params, $code = 0, \Throwable $previous = null)
    {
        parent::__construct(
            sprintf('Cannot generate link for %s?%s', $handler, urldecode(http_build_query($params))),
            $code,
            $previous
        );
    }
}
