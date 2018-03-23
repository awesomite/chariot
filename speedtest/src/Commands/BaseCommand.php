<?php

/*
 * This file is part of the awesomite/chariot package.
 * (c) Bartłomiej Krukowski <bartlomiej@krukowski.me>
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Awesomite\Chariot\Speedtest\Commands;

use Awesomite\Chariot\Speedtest\Timer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 */
abstract class BaseCommand extends Command
{
    const COMMAND_NAME = 'TODO';

    protected function configure()
    {
        parent::configure();
        $this
            ->setName(static::COMMAND_NAME)
            ->addOption('fast', 'f', InputOption::VALUE_NONE);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->handleXdebug();
        $timer = new Timer();
        $timer->start();
        $this->doExecute($input, $output, $input->getOption('fast'));
        $timer->stop();
        $output->writeln(\sprintf('Executed in %.2f s', $timer->getTime()));
        $output->writeln(\sprintf('Memory peak %.2f MB', \memory_get_peak_usage(false)/1024/1024));
        $output->writeln(\sprintf('Real memory peak %.2f MB', \memory_get_peak_usage(true)/1024/1024));
    }

    abstract protected function doExecute(InputInterface $input, OutputInterface $output, bool $fast);

    private function handleXdebug()
    {
        if (\extension_loaded('xdebug')) {
            throw new \RuntimeException('Do not execute performance tests with enabled xdebug (add -n option)');
        }
    }
}
