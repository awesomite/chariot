<?php

/*
 * This file is part of the awesomite/chariot package.
 * (c) Bartłomiej Krukowski <bartlomiej@krukowski.me>
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Awesomite\Chariot;

class HttpMethods
{
    const METHOD_ANY     = '*';
    const METHOD_HEAD    = 'HEAD';
    const METHOD_GET     = 'GET';
    const METHOD_POST    = 'POST';
    const METHOD_PUT     = 'PUT';
    const METHOD_DELETE  = 'DELETE';
    const METHOD_PATCH   = 'PATCH';
    const METHOD_CONNECT = 'CONNECT';
    const METHOD_OPTIONS = 'OPTIONS';
    const METHOD_TRACE   = 'TRACE';

    const ALL_METHODS
        = [
            self::METHOD_ANY,
            self::METHOD_HEAD,
            self::METHOD_GET,
            self::METHOD_POST,
            self::METHOD_PUT,
            self::METHOD_DELETE,
            self::METHOD_PATCH,
            self::METHOD_CONNECT,
            self::METHOD_OPTIONS,
            self::METHOD_TRACE,
        ];
}
