<?php

/*
 * This file is part of the awesomite/chariot package.
 * (c) Bartłomiej Krukowski <bartlomiej@krukowski.me>
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

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
