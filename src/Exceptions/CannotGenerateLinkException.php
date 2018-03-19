<?php

/*
 * This file is part of the awesomite/chariot package.
 * (c) Bartłomiej Krukowski <bartlomiej@krukowski.me>
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Awesomite\Chariot\Exceptions;

class CannotGenerateLinkException extends LogicException
{
    public function __construct(string $handler, array $params, $code = 0, \Throwable $previous = null)
    {
        $message = "Cannot generate link for {$handler}";
        if ($params) {
            $message .= '?' . \urldecode(\http_build_query($params));
        }
        parent::__construct($message, $code, $previous);
    }
}
