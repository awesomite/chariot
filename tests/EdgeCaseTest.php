<?php

namespace Awesomite\Chariot;

use Awesomite\Chariot\Exceptions\CannotGenerateLinkException;
use Awesomite\Chariot\Pattern\PatternRouter;

/**
 * @intrnal
 */
class EdgeCaseTest extends TestBase
{
    public function testNonScalar()
    {
        $router = PatternRouter::createDefault();
        $router->get('/article/{{ id :int }}', 'showArticle');
        $this->expectException(CannotGenerateLinkException::class);
        $this->expectExceptionMessage('Cannot generate link for showArticle');
        $router->linkTo('showArticle')->withParam('id', [])->toString();
    }
}
