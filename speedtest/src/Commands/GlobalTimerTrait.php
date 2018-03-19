<?php

/*
 * This file is part of the awesomite/chariot package.
 * (c) Bartłomiej Krukowski <bartlomiej@krukowski.me>
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Awesomite\Chariot\Speedtest\Commands;

use Awesomite\Chariot\Speedtest\Timer;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 */
trait GlobalTimerTrait
{
    private $globalTimer;
    
    private function startGlobalTimer()
    {
        $this->getGlobalTimer()->start();
    }
    
    private function printGlobalTimerFooter(OutputInterface $output)
    {
        $globalTimer = $this->getGlobalTimer();
        $globalTimer->stop();
        $output->writeln(\sprintf('Executed in %.2fs', $globalTimer->getTime()));
    }
    
    private function getGlobalTimer(): Timer
    {
        return $this->globalTimer ?? $this->globalTimer = new Timer();
    }
}
