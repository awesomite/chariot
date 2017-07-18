<?php

namespace Awesomite\Chariot;

use Awesomite\Chariot\Exceptions\HttpException;

interface RouterInterface
{
    /**
     * @param string $method
     * @param string $path
     *
     * @return InternalRouteInterface
     *
     * @throws HttpException
     */
    public function match(string $method, string $path): InternalRouteInterface;

    /**
     * @param string $handler
     * @param string $method
     *
     * @return LinkInterface
     *
     * @throws HttpException
     */
    public function linkTo(string $handler, string $method = HttpMethods::METHOD_ANY): LinkInterface;
}
