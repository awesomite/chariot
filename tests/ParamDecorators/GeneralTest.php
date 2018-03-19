<?php

namespace Awesomite\Chariot\ParamDecorators;

use Awesomite\Chariot\LinkInterface;
use Awesomite\Chariot\Pattern\PatternRouter;
use Awesomite\Chariot\TestBase;

/**
 * @internal
 */
class GeneralTest extends TestBase
{
    public function testReplace()
    {
        $router = PatternRouter::createDefault();
        $router->get('/user/{{ name }}', 'showUser');
        $mapping = [
            1 => 'first-user',
            2 => 'john-doe',
        ];
        $decorator = $this->createUserMappingDecorator($mapping);
        
        $router->addParamDecorator($decorator);
        $this->doTestReplace($router);
        
        /** @var PatternRouter $restoredRouter */
        $restoredRouter = eval('return ' . $router->exportToExecutable() . ';');
        $restoredRouter->addParamDecorator($decorator);
        $this->doTestReplace($restoredRouter);
    }
    
    private function doTestReplace(PatternRouter $router)
    {
        $this->assertSame(
            '/user/first-user',
            (string)$router->linkTo('showUser')->withParam('id', 1)
        );

        $this->assertSame(
            '/user/john-doe',
            (string)$router->linkTo('showUser')->withParam('id', 2)
        );

        $this->assertSame(
            LinkInterface::ERROR_CANNOT_GENERATE_LINK,
            (string)$router->linkTo('showUser')->withParam('id', 3)
        );
    }
    
    public function testAll()
    {
        $mapping = [
            1 => 'cats',
            2 => 'dogs',
        ];
        $router = PatternRouter::createDefault();
        $paramDecorator = $this->createCategoryMapingDecorator($mapping);
        $router->addParamDecorator($paramDecorator);
        $router->get('/category/{{ id }}-{{ name }}', 'showCategory');
        
        $this->doTestAll($router);
        
        /** @var PatternRouter $restoredRouter */
        $restoredRouter = eval('return ' . $router->exportToExecutable() . ';');
        $restoredRouter->addParamDecorator($paramDecorator);
        $this->doTestAll($restoredRouter);
    }
    
    private function doTestAll(PatternRouter $router)
    {
        $this->assertSame(
            '/category/1-cats',
            (string)$router->linkTo('showCategory')->withParam('id', 1)
        );
        $this->assertSame(
            '/category/2-dogs',
            (string)$router->linkTo('showCategory')->withParams(['id' => 2])
        );
        $this->assertSame(
            '/category/2-dogs',
            (string)$router->linkTo('showCategory')->withParam('id', 2)->withParam('name', 'animals')
        );
        $this->assertSame(
            '/category/3-animals',
            (string)$router->linkTo('showCategory')->withParam('id', 3)->withParam('name', 'animals')
        );
        $this->assertSame(
            LinkInterface::ERROR_CANNOT_GENERATE_LINK,
            (string)$router->linkTo('showCategory')->withParam('id', 3)
        );
    }
    
    private function createUserMappingDecorator(array $mapping): ParamDecoratorInterface
    {
        return new class($mapping) implements ParamDecoratorInterface {
            private $mapping;
            
            public function __construct(array $mapping)
            {
                $this->mapping = $mapping;
            }

            public function decorate(ContextInterface $context)
            {
                if ('showUser' !== $context->getHandler()) {
                    return;
                }

                $params = $context->getParams();
                $id = $params['id'] ?? null;

                if (null === $id) {
                    return;
                }

                if (!array_key_exists($id, $this->mapping)) {
                    return;
                }

                $context
                    ->removeParam('id')
                    ->setParam('name', $this->mapping[$id])
                ;
            }
        };
    }
    
    private function createCategoryMapingDecorator(array $mapping): ParamDecoratorInterface
    {
        return new class($mapping) implements ParamDecoratorInterface {
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
                $id = $params['id'] ?? null;
                
                if (null === $id) {
                    return;
                }

                if (!array_key_exists($id, $this->mapping)) {
                    return;
                }

                $context->setParam('name', $this->mapping[$id]);
            }
        };
    }
}
