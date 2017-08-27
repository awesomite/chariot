<?php

namespace Awesomite\Chariot\Pattern;

/**
 * @internal
 */
trait ProviderEmptyRouterTrait
{
    public function providerEmptyRouters()
    {
        foreach ($this->getEmptyRouters() as $router) {
            yield [$router];
        }
    }

    /**
     * @return PatternRouter[]
     */
    private function getEmptyRouters()
    {
        return [
            new PatternRouter(Patterns::createDefault(), PatternRouter::STRATEGY_SEQUENTIALLY),
            new PatternRouter(Patterns::createDefault(), PatternRouter::STRATEGY_TREE),
        ];
    }
}
