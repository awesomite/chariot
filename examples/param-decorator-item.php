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
class TitleProvider implements ParamDecoratorInterface
{
    private $mapping;
    
    public function __construct(array $mapping)
    {
        $this->mapping = $mapping;
    }

    public function decorate(ContextInterface $context)
    {
        if ('showItem' !== $context->getHandler()) {
            return;
        }
        
        $id = $context->getParams()['id'] ?? null;
        $title = $this->mapping[$id] ?? null;
        
        if (null !== $title) {
            $context->setParam('title', $title);
        }
    }
}

$titleMapping = [
    1 => 'my-first-item',
    2 => 'my-second-item',
    3 => 'my-third-item',
];
$router = PatternRouter::createDefault();
$router->get('/items/{{ id :int }}-{{ title }}', 'showItem');

/*
 * Error, because title is not defined
 */
echo 'Without provider: ';
echo $router->linkTo('showItem')->withParam('id', 1), PHP_EOL;

/*
 * Valid URL, because title will be provided automatically
 */
$router->addParamDecorator(new TitleProvider($titleMapping));
echo 'With provider: ';
echo $router->linkTo('showItem')->withParam('id', 1), PHP_EOL;

/*
 * Output:
 *
 * Without provider: __ERROR_CANNOT_GENERATE_LINK
 * With provider: /items/1-my-first-item
 */
