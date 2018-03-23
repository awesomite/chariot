<?php

/*
 * This file is part of the awesomite/chariot package.
 * (c) Bartłomiej Krukowski <bartlomiej@krukowski.me>
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

use Awesomite\Chariot\ParamDecorators\ParamDecoratorInterface;
use Awesomite\Chariot\Pattern\PatternRouter;
use Awesomite\Chariot\ParamDecorators\ContextInterface;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'init.php';

/**
 * @internal
 */
class UsernameDecorator implements ParamDecoratorInterface
{
    public function decorate(ContextInterface $context)
    {
        if ('showUser' !== $context->getHandler()) {
            return;
        }
        
        $name = $context->getParams()['name'] ?? null;
        if (null === $name) {
            return;
        }

        $value = \mb_strtolower($name);
        $value = \str_replace(' ', '-', $value);
        $context->setParam('name', $value);
    }
}

$router = PatternRouter::createDefault();
$router->addParamDecorator(new UsernameDecorator());

$router->get('/user/{{ name }}', 'showUser');

// transform 'John Doe' to 'john-doe'
echo $router->linkTo('showUser')->withParam('name', 'John Doe'), PHP_EOL;
// transform 'William Shakespeare' to 'william-shakespeare'
echo $router->linkTo('showUser')->withParam('name', 'william-shakespeare'), PHP_EOL;

/*
 * Output:
 *
 * /user/john-doe
 * /user/william-shakespeare
 */
