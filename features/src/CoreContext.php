<?php

/*
 * This file is part of the awesomite/chariot package.
 * (c) Bartłomiej Krukowski <bartlomiej@krukowski.me>
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

use Awesomite\Chariot\Exceptions\CannotGenerateLinkException;
use Awesomite\Chariot\Exceptions\HttpException;
use Awesomite\Chariot\Pattern\PatternRouter;
use Awesomite\Chariot\RouterInterface;
use Behat\Behat\Context\Context;
use PHPUnit\Framework\Assert;

/**
 * @internal
 */
class CoreContext implements Context
{
    /**
     * @var PatternRouter
     */
    private $patternRouter;
    
    /**
     * @var RouterInterface
     */
    private $testedRouter;

    /**
     * @var string|null
     */
    private $cache;

    /**
     * @Given there is an empty router
     */
    public function thereIsAnEmptyRouter()
    {
        $this->testedRouter = $this->patternRouter = PatternRouter::createDefault();
    }

    /**
     * @When I add URL :url with method :method for route :handler :jsonParams
     */
    public function iRegisterUrlWithMethodForRoute($url, $method, $handler, $jsonParams)
    {
        $this->patternRouter->addRoute($method, $url, $handler, \json_decode($jsonParams, true));
    }

    /**
     * @When I add URL :url with method :method for route :handler
     */
    public function iRegisterUrlWithMethodForRouteWithoutParams($url, $method, $handler)
    {
        $this->iRegisterUrlWithMethodForRoute($url, $method, $handler, '{}');
    }

    /**
     * @Then router should return :handler :jsonParams for :method :url
     */
    public function routerShouldReturnFor($handler, $jsonParams, $method, $url)
    {
        $route = $this->testedRouter->match($method, $url);
        Assert::assertSame($handler, $route->getHandler());
        Assert::assertSame(\json_decode($jsonParams, true), $route->getParams());
    }

    /**
     * @Then router should throw :code for :method :url
     */
    public function routerShouldThrowFor($code, $method, $url)
    {
        try {
            $this->patternRouter->match($method, $url);
        } catch (HttpException $exception) {
            Assert::assertSame((int) $code, $exception->getCode());
            return;
        }
        
        Assert::fail('Router should throw an exception');
    }

    /**
     * @Then router should allow for methods :methods for URL :url
     */
    public function routerShouldAllowForMethodsForUrl($methods, $url)
    {
        $explodedMethods = '' === $methods ? [] : \preg_split('#,\\s*#', $methods);
        $this->assertArraysWithSameElements($explodedMethods, $this->patternRouter->getAllowedMethods($url));
    }

    /**
     * @Then router should not generate URL for method :method with handler :handler
     */
    public function routerShouldNotGenerateUrlForMethodWithHandler($method, $handler)
    {
        $this->routerShouldNotGenerateUrlForMethodWithHandlerAndParams($method, $handler, '{}');
    }

    /**
     * @Then router should not generate URL for method :method with handler :handler and params :params
     */
    public function routerShouldNotGenerateUrlForMethodWithHandlerAndParams($method, $handler, $params)
    {
        $arrayParams = \json_decode($params, true);
        try {
            $this->testedRouter->linkTo($handler, $method)->withParams($arrayParams)->toString();
        } catch (CannotGenerateLinkException $exception) {
            return;
        }
        
        Assert::fail('Router should not generate URL for given data');
    }

    /**
     * @Then router should generate URL :url for method :method with handler :handler and params :params
     */
    public function routerShouldGenerateUrlForMethodForHandlerWithParams($url, $method, $handler, $params)
    {
        $arrayParams = \json_decode($params, true);
        $generatedUrl = (string) $this->testedRouter->linkTo($handler, $method)->withParams($arrayParams);
        Assert::assertSame($url, $generatedUrl, $generatedUrl);
    }

    /**
     * @Then router should generate URL :url for method :method with handler :handler
     */
    public function routerShouldGenerateUrlForMethodForHandlerWithoutParams($url, $method, $handler)
    {
        $this->routerShouldGenerateUrlForMethodForHandlerWithParams($url, $method, $handler, '{}');
    }

    /**
     * @When I add pattern :pattern with name :name
     */
    public function iAddPatternWithName($pattern, $name)
    {
        $this->patternRouter->getPatterns()->addPattern($name, $pattern);
    }

    private function assertArraysWithSameElements(array $expected, array $actual, string $message = '')
    {
        \sort($expected);
        \sort($actual);
        Assert::assertSame($expected, $actual, $message);
    }

    /**
     * @When I save router to cache
     */
    public function iSaveRouterToCache()
    {
        $this->cache = $this->patternRouter->exportToExecutable();
    }

    /**
     * @When I restore router from cache
     */
    public function iRestoreRouterFromCache()
    {
        $this->testedRouter = eval('return ' . $this->cache . ';');
    }
}
