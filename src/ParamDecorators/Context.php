<?php

/*
 * This file is part of the awesomite/chariot package.
 * (c) Bartłomiej Krukowski <bartlomiej@krukowski.me>
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Awesomite\Chariot\ParamDecorators;

/**
 * @internal
 */
class Context implements ContextInterface
{
    private $handler;
    
    private $method;
    
    private $params;
    
    private $requiredParams;
    
    public function __construct(string $handler, string $method, array $params, array $requiredParams)
    {
        $this->handler = $handler;
        $this->method = $method;
        $this->params = $params;
        $this->requiredParams = $requiredParams;
    }

    public function getHandler(): string
    {
        return $this->handler;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getParams(): array
    {
        return $this->params;
    }

    public function setParam(string $key, $value): ContextInterface
    {
        $this->params[$key] = $value;

        return $this;
    }

    public function removeParam(string $key): ContextInterface
    {
        unset($this->params[$key]);
        
        return $this;
    }

    public function getRequiredParams(): array
    {
        return $this->requiredParams;
    }
}
