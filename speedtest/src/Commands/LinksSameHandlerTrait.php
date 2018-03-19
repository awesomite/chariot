<?php

/*
 * This file is part of the awesomite/chariot package.
 * (c) Bartłomiej Krukowski <bartlomiej@krukowski.me>
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Awesomite\Chariot\Speedtest\Commands;

use Awesomite\Chariot\Pattern\PatternRouter;
use Awesomite\Chariot\Speedtest\StringsHelper;
use Awesomite\Chariot\Speedtest\Timer;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 */
trait LinksSameHandlerTrait
{
    private function displaySameHandlerHeader(OutputInterface $output)
    {
        $description
            = <<<'DESCRIPTION'
<fg=black;bg=yellow>  1. Many links with the same handler  </>
This test generates router with X random generated paths in format <fg=yellow;bg=black>/[random string]</>
which points to <fg=yellow;bg=black>the same handler</>, then generates links to all of them.
DESCRIPTION;
        $output->writeln($description);
    }

    private function executeSameHandlerTests(OutputInterface $output, array $numbers)
    {
        /** @var Timer[] $timers */
        $timers = [];
        $this->testSameHandler(1); // warm up
        foreach ($numbers as $number) {
            $timers[$number] = $this->testSameHandler($number);
        }

        $this->printTableOfTimes($output, $timers);
    }

    private function testSameHandler(int $repetitions): Timer
    {
        $categories = [];
        for ($i = 1; $i <= $repetitions; $i++) {
            $categories[$i] = StringsHelper::getRandomString(\mt_rand(10, 40));
        }

        $router = PatternRouter::createDefault();
        foreach ($categories as $categoryId => $categoryName) {
            $router->get('/' . $categoryName, 'showCategory', ['id' => $categoryId]);
        }

        $timer = new Timer();
        foreach ($categories as $categoryId => $categoryName) {
            $timer->start();
            $router->linkTo('showCategory')->withParam('id', $categoryId)->toString();
            $timer->stop();
        }

        return $timer;
    }
}
