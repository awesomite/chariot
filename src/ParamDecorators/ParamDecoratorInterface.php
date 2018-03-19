<?php

namespace Awesomite\Chariot\ParamDecorators;

interface ParamDecoratorInterface
{
    public function decorate(ContextInterface $context);
}
