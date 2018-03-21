<?php

/*
 * This file is part of the awesomite/chariot package.
 * (c) Bartłomiej Krukowski <bartlomiej@krukowski.me>
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Awesomite\Chariot\Speedtest\Commands;

use Awesomite\Chariot\Pattern\PatternRouter;
use Awesomite\Chariot\Speedtest\Timer;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 */
class CacheCommand extends BaseCompareCommand
{
    const COMMAND_NAME = 'test-cache';

    private $fileName;

    protected function getFirstName(): string
    {
        return 'With cache';
    }

    protected function getSecondsName(): string
    {
        return 'Without cache';
    }

    protected function handleFirst(Timer $timer, int $n)
    {
        for ($i = 0; $i < $n; $i++) {
            $timer->start();
            require $this->fileName;
            $timer->stop();
        }
    }

    protected function handleSecond(Timer $timer, int $n)
    {
        for ($i = 0; $i < $n; $i++) {
            $timer->start();
            $this->getExampleRouter();
            $timer->stop();
        }
    }

    protected function getNumber(bool $fast): int
    {
        return $fast ? 1 : 10000;
    }

    protected function doExecute(InputInterface $input, OutputInterface $output, bool $fast)
    {
        parent::doExecute($input, $output, $fast);
        \unlink($this->fileName);
    }

    protected function warmUp()
    {
        foreach (['opcache.enable', 'opcache.enable_cli'] as $conf) {
            if (!\ini_get($conf)) {
                throw new \RuntimeException(\sprintf('%s must be enabled', $conf));
            }
        }

        $this->fileName = __FILE__ . '.cache';
        \file_put_contents($this->fileName, '<?php return ' . $this->getExampleRouter()->exportToExecutable() . ';');
        \touch($this->fileName, \time() - 3600);
        if (!\opcache_compile_file($this->fileName)) {
            throw new \RuntimeException(\sprintf('Cannot compile file `%s`', $this->fileName));
        }
        if (!\opcache_is_script_cached($this->fileName)) {
            throw new \RuntimeException(\sprintf('File `%s` is not compiled', $this->fileName));
        }
        $this->handleFirst(new Timer(), 1);
        $this->handleSecond(new Timer(), 1);
    }

    private function getExampleRouter()
    {
        $router = PatternRouter::createDefault();
        $router->get('/', 'home');
        $router->get('/contact', 'contact');
        $router->post('/contact', 'sendMessage');
        $router->get('/categories', 'categories');
        $router->get('/categories/{{ id :uint }}-{{ name }}', 'category');
        $router->get('/item-{{ id :uint }}-{{ name }}', 'item');
        $router->delete('/item-{{ id :uint }}', 'deleteItem');
        $router->get('/signin', 'signin');
        $router->post('/signin', 'doSignin');

        return $router;
    }
}
