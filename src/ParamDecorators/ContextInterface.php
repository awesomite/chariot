<?php

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
     * e.g. ['id' => 5, 'name' => 'awesomite']
     *
     * @return array
     */
    public function getParams(): array;
    
    public function setParam(string $key, $value): self;
    
    public function removeParam(string $key): self;

    /**
     * e.g. ['id', 'title']
     * 
     * @return array
     */
    public function getRequiredParams(): array;
}
