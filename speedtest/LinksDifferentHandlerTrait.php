<?php

namespace Awesomite\Chariot\Speedtest;

use Awesomite\Chariot\Pattern\PatternRouter;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 */
trait LinksDifferentHandlerTrait
{
    private function displayDifferentHandlerHeader(OutputInterface $output)
    {
        $description
            = <<<'DESCRIPTION'
<fg=black;bg=yellow>  2. Many links with different handlers  </>
This command generates router with X random generated paths in format <fg=yellow;bg=black>/[random string]</>
which points to <fg=yellow;bg=black>different handlers</>, then generates links to all of them.
DESCRIPTION;
        $output->writeln($description);
    }

    private function executeDifferentHandlerTests(OutputInterface $output, array $numbers)
    {
        /** @var Timer[] $timers */
        $timers = [];
        $this->testSameHandler(1); // warm up
        foreach ($numbers as $number) {
            $timers[$number] = $this->testDifferentHandlers($number);
        }

        $this->printTableOfTimes($output, $timers);
    }

    private function testDifferentHandlers(int $numberOfCategories): Timer
    {
        $pages = [];
        for ($i = 1; $i <= $numberOfCategories; $i++) {
            $pages[$i] = $this->randomString(mt_rand(10, 20));
        }

        $router = PatternRouter::createDefault();
        foreach ($pages as $pageId => $pageName) {
            $router->get('/' . $pageName, 'handler_' . $pageId);
        }

        $timer = new Timer();
        foreach ($pages as $pageId => $pageName) {
            $timer->start();
            $router->linkTo('handler_' . $pageId)->toString();
            $timer->stop();
        }

        return $timer;
    }
}
