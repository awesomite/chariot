<?php

use Awesomite\Chariot\ParamDecorators\ParamDecoratorInterface;
use Awesomite\Chariot\Pattern\PatternRouter;
use Awesomite\Chariot\ParamDecorators\ContextInterface;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'init.php';

/**
 * @internal
 */
class MyCategoryDecorator implements ParamDecoratorInterface
{
    private $mapping;
    
    public function __construct(array $mapping)
    {
        $this->mapping = $mapping;
    }

    public function decorate(ContextInterface $context)
    {
        if ('showCategory' !== $context->getHandler()) {
            return;
        }
        
        $params = $context->getParams();
        if (!array_key_exists('id', $params)) {
            return;
        }
        
        $id = $params['id'];
        if (array_key_exists($id, $this->mapping)) {
            $context->setParam('name', $this->mapping[$id]);
        }
    }
}

$mapping = [
    1 => 'books',
    2 => 'games',
    3 => 'cars',
];
$router = PatternRouter::createDefault();
$router->addParamDecorator(new MyCategoryDecorator($mapping));

$router->get('/category/{{ id :int }}-{{ name }}', 'showCategory');

// books
echo $router->linkTo('showCategory')->withParam('id', 1), PHP_EOL;
// games
echo $router->linkTo('showCategory')->withParam('id', 2), PHP_EOL;
// cars
echo $router->linkTo('showCategory')->withParam('id', 3), PHP_EOL;
// does not exist
echo $router->linkTo('showCategory')->withParam('id', 4), PHP_EOL;

/*
 * Output:
 *
 * /category/1-books
 * /category/2-games
 * /category/3-cars
 * __ERROR_CANNOT_GENERATE_LINK
 */
