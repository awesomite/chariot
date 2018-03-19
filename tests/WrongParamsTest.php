<?php

namespace Awesomite\Chariot;

use Awesomite\Chariot\Exceptions\InvalidArgumentException;
use Awesomite\Chariot\Pattern\PatternRouter;

/**
 * @internal
 */
class WrongParamsTest extends TestBase
{
    public function testCorrect()
    {
        $router = PatternRouter::createDefault();

        $router->get('/article-{{ id :int }}', 'showArticle', ['name' => null]);
        $router->get('/article-{{ id :int }}-{{ name }}', 'showArticle');

        $this->assertSame(
            '/article-5',
            (string)$router->linkTo('showArticle')->withParam('id', 5)->withParam('name', null)
        );
        $this->assertSame(
            '/article-6-item',
            (string)$router->linkTo('showArticle')->withParam('id', 6)->withParam('name', 'item')
        );
    }

    /**
     * @dataProvider providerWrong
     *
     * @param iterable $patternReader
     * @param string   $expected
     */
    public function testWrong($patternReader, string $expected)
    {
        $router = PatternRouter::createDefault();
        
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expected);
        
        foreach ($patternReader as $data) {
            $router->addRoute(...$data);
        }
    }

    public function providerWrong()
    {
        $expected1 = <<<'ERROR'
Each route associated with the same handler must contain the same parameters

1) GET `/article-{{ id :int }}`
   [id]
2) GET `/article-{{ id :int }}-{{ name }}`
   [id, name]

Diff: +name
ERROR;
        $params = [
            ['GET', '/article-{{ id :int }}', 'showArticle'],
            ['GET', '/article-{{ id :int }}-{{ name }}', 'showArticle'],
        ];
        yield [$params, $expected1];

        $expected2 = <<<'ERROR'
Each route associated with the same handler must contain the same parameters

1) GET `/category/{{ rootCategory }}/{{ category }}/item-{{ id }}`
   [rootCategory, category, id]
2) POST `/item-{{ id }}` [withoutPath=1]
   [id, withoutPath]

Diff: -rootCategory, -category, +withoutPath
ERROR;
        $params2 = [
            ['GET', '/category/{{ rootCategory }}/{{ category }}/item-{{ id }}', 'showItem'],
            ['POST', '/item-{{ id }}', 'showItem', ['withoutPath' => true]],
        ];
        yield [$params2, $expected2];
    }
}
