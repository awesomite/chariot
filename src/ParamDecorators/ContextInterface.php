<?php

/*
 * This file is part of the awesomite/chariot package.
 * (c) Bartłomiej Krukowski <bartlomiej@krukowski.me>
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Awesomite\Chariot\ParamDecorators;

interface ContextInterface
{
    /**
     * e.g. 'showHomepage'
     *
     * @return string
     */
    public function getHandler(): string;

    /**
     * e.g. 'GET'
     *
     * @return string
     */
    public function getMethod(): string;

    /**
     * e.g. ['id' => 5, 'name' => 'chariot']
     *
     * @return array
     */
    public function getParams(): array;
    
    public function setParam(string $key, $value): self;
    
    public function removeParam(string $key): self;

    /**
     * e.g. ['id', 'name']
     *
     * @return array
     */
    public function getRequiredParams(): array;
}
