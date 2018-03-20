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
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 */
class LinksCommand extends BaseCommand
{
    const COMMAND_NAME = 'test-links';

    use LinksSameHandlerTrait;
    use LinksDifferentHandlerTrait;

    protected function doExecute(InputInterface $input, OutputInterface $output, bool $fast)
    {
        $numbers = $input->getOption('fast')
            ? [10, 20, 50, 100, 150, 200]
            : [10, 100, 250, 500, 1000, 2000];
        $this->displaySameHandlerHeader($output);
        $this->executeSameHandlerTests($output, $numbers);
        $output->writeln('');
        $this->displayDifferentHandlerHeader($output);
        $this->executeDifferentHandlerTests($output, $numbers);
    }

    /**
     * @param OutputInterface $output
     * @param Timer[]         $timers
     */
    private function printTableOfTimes(OutputInterface $output, array $timers)
    {
        $table = new Table($output);
        $table->setHeaders(\array_merge(['time \ number of paths (X)'], \array_keys($timers)));

        $rowMin = ['min time [ms]'];
        $rowMax = ['max time [ms]'];
        $rowAvg = ['avg time [ms]'];
        $rowTrend = ['avg time (X) / avg time (10)'];

        $format = '% 7.4f';

        $avgTime10 = false;
        foreach ($timers as $number => $timer) {
            if (false === $avgTime10) {
                $avgTime10 = $timer->getTime() / $number;
            }

            $rowMin[] = \sprintf($format, $timer->getMinTime() * 1000);
            $rowMax[] = \sprintf($format, $timer->getMaxTime() * 1000);
            $rowAvg[] = \sprintf($format, $timer->getTime() * 1000 / $number);
            $rowTrend[] = \sprintf('% 7.2f', $timer->getTime() / $number / $avgTime10);
        }

        $table->setRows([
            $rowMin,
            $rowMax,
            $rowAvg,
            new TableSeparator(),
            $rowTrend,
        ]);
        $table->render();
    }
}
