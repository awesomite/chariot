<?php

/*
 * This file is part of the awesomite/chariot package.
 * (c) Bartłomiej Krukowski <bartlomiej@krukowski.me>
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Awesomite\Chariot\Speedtest\Commands;

use Awesomite\Chariot\Speedtest\Timer;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 */
abstract class BaseCompareCommand extends BaseCommand
{
    protected function doExecute(InputInterface $input, OutputInterface $output, bool $fast)
    {
        $this->warmUp();
        $n = $this->getNumber($fast);
        $output->writeln("<fg=black;bg=yellow>  Number of repetitions: {$n}  </>");

        $timer1 = new Timer();
        $this->handleFirst($timer1, $n);

        $timer2 = new Timer();
        $this->handleSecond($timer2, $n);

        $table = new Table($output);
        $table->setHeaders(['', $this->getFirstName(), $this->getSecondsName(), 'Diff [%]']);
        $table->addRow([
            'avg time [ms]',
            $this->formatTime($timer1->getTime() / $n),
            $this->formatTime($timer2->getTime() / $n),
            $this->formatDiff($timer1->getTime(), $timer2->getTime()),
        ]);

        $table->render();
    }

    private function formatTime(float $time): string
    {
        return \sprintf('% 10.4f', $time * 1000);
    }

    private function formatDiff(float $f1, float $f2): string
    {
        return \sprintf('% 8.2f', ($f1 - $f2)/$f2 * 100);
    }

    abstract protected function getFirstName(): string;

    abstract protected function getSecondsName(): string;

    abstract protected function handleFirst(Timer $timer, int $n);

    abstract protected function handleSecond(Timer $timer, int $n);

    abstract protected function getNumber(bool $fast): int;

    abstract protected function warmUp();
}
