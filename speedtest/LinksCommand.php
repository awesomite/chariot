<?php

namespace Awesomite\Chariot\Speedtest;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 */
class LinksCommand extends Command
{
    use LinksSameHandlerTrait;
    use LinksDifferentHandlerTrait;

    protected function configure()
    {
        parent::configure();
        $this->setName('test-links');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->checkXdebug();

        $globalTimer = new Timer();
        $globalTimer->start();

        $this->displaySameHandlerHeader($output);
        $this->executeSameHandlerTests($output, [10, 100, 250, 500, 1000]);
        $output->writeln('');
        $this->displayDifferentHandlerHeader($output);
        $this->executeDifferentHandlerTests($output, [10, 100, 250, 500, 1000, 5000]);

        $output->writeln('');
        $globalTimer->stop();
        $output->writeln(sprintf('Executed in %.2fs', $globalTimer->getTime()));
    }

    private function checkXdebug()
    {
        if (extension_loaded('xdebug')) {
            throw new \RuntimeException('Do not execute performance tests with enabled xdebug');
        }
    }

    /**
     * @param OutputInterface $output
     * @param Timer[]         $timers
     */
    private function printTableOfTimes(OutputInterface $output, array $timers)
    {
        $table = new Table($output);
        $table->setHeaders(array_merge(['time \ number of paths (X)'], array_keys($timers)));

        $rowMin = ['min time [ms]'];
        $rowMax = ['max time [ms]'];
        $rowAvg = ['avg time [ms]'];

        $format = '% 7.4f';

        foreach ($timers as $number => $timer) {
            $rowMin[] = sprintf($format, $timer->getMinTime() * 1000);
            $rowMax[] = sprintf($format, $timer->getMaxTime() * 1000);
            $rowAvg[] = sprintf($format, $timer->getTime() * 1000 / $number);
        }

        $table->setRows([
            $rowMin,
            $rowMax,
            $rowAvg,
        ]);
        $table->render();
    }

    private function randomString(int $length)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        return $randomString;
    }
}
