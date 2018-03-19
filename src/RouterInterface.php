<?php

/*
 * This file is part of the awesomite/chariot package.
 * (c) Bartłomiej Krukowski <bartlomiej@krukowski.me>
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

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
     * @param string $url
     *
     * @return array e.g. ['GET', 'POST']
     */
    public function getAllowedMethods(string $url): array;

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
