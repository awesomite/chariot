<?php

use Awesomite\Chariot\Pattern\PatternRouter;
use Symfony\Component\Console\Output\ConsoleOutput;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'init.php';

$output = new ConsoleOutput();

$months = [
    1  => 'jan',
    2  => 'feb',
    3  => 'mar',
    4  => 'apr',
    5  => 'may',
    6  => 'june',
    7  => 'july',
    8  => 'aug',
    9  => 'sept',
    10 => 'oct',
    11 => 'nov',
    12 => 'dec',
];

$router = PatternRouter::createDefault();
foreach ($months as $monthNum => $monthName) {
    $router->get("/calendar/{{ year \d{4} }}-{$monthName}", 'showMonth', [
        'month' => $monthNum,
    ]);
}

$paths = [
    '/calendar/2018-july',
    '/calendar/2017-aug',
    '/calendar/2015-oct',
    '/calendar/2019-dec',
];

$output->writeln('<bg=yellow;fg=black>                </>');
$output->writeln('<bg=yellow;fg=black>  Parsing URLs  </>');
$output->writeln('<bg=yellow;fg=black>                </>');
foreach ($paths as $path) {
    $route = $router->match('GET', $path);
    $year = $route->getParams()['year'];
    $month = $route->getParams()['month'];
    $output->writeln(sprintf(
        '<info>%-20s</info>     => year: <info>%d</info> month: <info>%2d</info>',
        $path,
        $year,
        $month
    ));
}

$paramsData = [
    ['year' => 2014, 'month' => 10],
    ['year' => 2016, 'month' => 12],
    ['year' => 2019, 'month' => 7],
];
$output->writeln('<bg=yellow;fg=black>                 </>');
$output->writeln('<bg=yellow;fg=black>  Building URLs  </>');
$output->writeln('<bg=yellow;fg=black>                 </>');
foreach ($paramsData as $params) {
    $output->writeln(sprintf(
        'year: <info>%d</info>, month: <info>%2d</info>    => <info>%s</info>',
        $params['year'],
        $params['month'],
        (string) $router->linkTo('showMonth')->withParams($params)
    ));
}

/*
 * Output:
 *
 * Parsing URLs
 * /calendar/2018-july      => year: 2018 month:  7
 * /calendar/2017-aug       => year: 2017 month:  8
 * /calendar/2015-oct       => year: 2015 month: 10
 * /calendar/2019-dec       => year: 2019 month: 12
 *
 * Building URLs
 * year: 2014, month: 10    => /calendar/2014-oct
 * year: 2016, month: 12    => /calendar/2016-dec
 * year: 2019, month:  7    => /calendar/2019-july
 */
