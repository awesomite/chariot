<?php

/*
 * This file is part of the awesomite/chariot package.
 * (c) Bartłomiej Krukowski <bartlomiej@krukowski.me>
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Awesomite\Chariot\Speedtest\Commands;

use Awesomite\Chariot\ParamDecorators\ContextInterface;
use Awesomite\Chariot\ParamDecorators\ParamDecoratorInterface;
use Awesomite\Chariot\Pattern\PatternRouter;
use Awesomite\Chariot\Speedtest\Timer;

/**
 * @internal
 */
class ProvidersCommand extends BaseCompareCommand
{
    const COMMAND_NAME = 'test-providers';

    protected function getNumber(bool $fast): int
    {
        return $fast ? 100000 : 1000;
    }

    protected function warmUp()
    {
        $this->handleFirst(new Timer(), 1);
        $this->handleSecond(new Timer(), 1);
    }

    protected function getFirstName(): string
    {
        return 'With provider';
    }

    protected function getSecondsName(): string
    {
        return 'Without provider';
    }

    protected function handleFirst(Timer $timer, int $n)
    {
        $router = PatternRouter::createDefault();
        $router->addParamDecorator(new class() implements ParamDecoratorInterface {
            private $mapping = [
                1 => 'first',
                2 => 'second',
                3 => 'third',
            ];

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
        });
        $router->get('/items/{{ id :int }}-{{ title }}', 'showItem');

        for ($i = 0; $i < $n; $i++) {
            $timer->start();
            $router->linkTo('showItem')->withParam('id', 3)->toString();
            $timer->stop();
        }
    }

    protected function handleSecond(Timer $timer, int $n)
    {
        $router = PatternRouter::createDefault();
        $router->get('/items/{{ id :int }}-{{ title }}', 'showItem');

        for ($i = 0; $i < $n; $i++) {
            $timer->start();
            $router->linkTo('showItem')->withParam('id', 3)->withParam('title', 'third')->toString();
            $timer->stop();
        }
    }
}
