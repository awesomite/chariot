<?php

namespace Awesomite\Chariot\Pattern;

use Awesomite\Chariot\Exceptions\HttpException;
use Awesomite\Chariot\HttpMethods;
use Awesomite\Chariot\LinkInterface;
use Awesomite\Chariot\Pattern\StdPatterns\IntPattern;
use Awesomite\Chariot\Pattern\StdPatterns\PatternDate;
use Awesomite\Chariot\RouterInterface;
use Awesomite\Chariot\TestBase;

/**
 * @internal
 */
class ObjectPatternTest extends TestBase
{
    use ProviderEmptyRouterTrait;

    /**
     * @dataProvider providerEmptyRouters
     *
     * @param PatternRouter $router
     */
    public function testDatePattern(PatternRouter $router)
    {
        $router->getPatterns()->addPattern(':date', new PatternDate());
        $router->get('/day/{{ day :date }}', 'showDay');
        $route = $router->match(HttpMethods::METHOD_GET, '/day/2017-01-01');
        $this->assertSame('showDay', $route->getHandler());
        $this->assertTrue(isset($route->getParams()['day']));
        /** @var \DateTimeInterface $day */
        $day = $route->getParams()['day'];
        $this->assertInstanceOf(\DateTimeInterface::class, $day);
        $this->assertSame('2017-01-01', $day->format('Y-m-d'));

        $thrown = false;
        try {
            $router->match(HttpMethods::METHOD_GET, '/day/2018-02-31');
        } catch (HttpException $exception) {
            $thrown = true;
            $this->assertSame(404, $exception->getCode());
            $this->assertSame([], $router->getAllowedMethods('/day/2018-02-31'));
        }
        $this->assertTrue($thrown);

        $dates = [
            strtotime('2017-01-01'),
            '2017-01-01',
            new \DateTime('2017-01-01'),
            new \DateTimeImmutable('2017-01-01')
        ];
        foreach ($dates as $date) {
            $currentLink = (string) $router->linkTo('showDay')->withParam('day', $date);
            $this->assertSame('/day/2017-01-01', $currentLink);
        }

        $this->assertSame(
            LinkInterface::ERROR_CANNOT_GENERATE_LINK,
            (string) $router->linkTo('showDay')->withParam('day', new \stdClass())
        );
    }

    /**
     * @dataProvider providerEmptyRouters
     *
     * @param PatternRouter $router
     */
    public function testIntPattern(PatternRouter $router)
    {
        $router->getPatterns()->addPattern(':integer', new IntPattern());
        $router->get('/article-{{ id :integer }}', 'showArticle');
        $route = $router->match(HttpMethods::METHOD_GET, '/article-15');
        $this->assertSame('showArticle', $route->getHandler());
        $this->assertSame(['id' => 15], $route->getParams());
        $this->assertSame('/article-150', (string) $router->linkTo('showArticle')->withParam('id', 150));

        $thrown = false;
        try {
            $router->match(HttpMethods::METHOD_GET, '/article-first');
        } catch (HttpException $exception) {
            $thrown = true;
            $this->assertSame(HttpException::HTTP_NOT_FOUND, $exception->getCode());
            $this->assertSame([], $router->getAllowedMethods('/article-first'));
        }
        $this->assertTrue($thrown);

        $this->assertSame(
            LinkInterface::ERROR_CANNOT_GENERATE_LINK,
            (string) $router->linkTo('showArticle')->withParam('id', 'first')
        );
    }

    public function testCache()
    {
        $router = PatternRouter::createDefault();
        $router->getPatterns()->addPattern(':integer', new IntPattern());
        $router->get('/page/{{ number :integer }}', 'showPage');
        $router = eval('return ' . $router->exportToExecutable() . ';');
        /** @var RouterInterface $router */

        $this->assertSame('/page/5', (string) $router->linkTo('showPage')->withParam('number', 5));
        $route = $router->match('GET', '/page/5');
        $this->assertSame(['number' => 5], $route->getParams());
        $this->assertSame('showPage', $route->getHandler());
    }
}
