<?php

namespace Awesomite\Chariot;

class HttpMethods
{
    const METHOD_ANY = '*';
    const METHOD_HEAD = 'HEAD';
    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';
    const METHOD_PUT = 'PUT';
    const METHOD_DELETE = 'DELETE';
    const METHOD_PATCH = 'PATCH';

    const ALL_METHODS
        = [
            self::METHOD_ANY,
            self::METHOD_HEAD,
            self::METHOD_GET,
            self::METHOD_POST,
            self::METHOD_PUT,
            self::METHOD_DELETE,
            self::METHOD_PATCH,
        ];
}
