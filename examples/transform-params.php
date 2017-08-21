<?php

use Awesomite\Chariot\Exceptions\HttpException;
use Awesomite\Chariot\Pattern\PatternInterface;
use Awesomite\Chariot\Pattern\PatternRouter;
use Awesomite\Chariot\Pattern\Patterns;
use Awesomite\Chariot\Pattern\StdPatterns\DatePattern;

/**
 * @see PatternInterface
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . 'init.php';

$router = new PatternRouter(new Patterns());

/*
 * Add object pattern which will allow transforming params
 */
$router->getPatterns()->addPattern(':date', new DatePattern());

/*
 * Add new route
 */
$router->get('/day/{{ day :date }}', 'showDay');

$route = $router->match('GET', '/day/2017-01-01');
/** @var \DateTimeImmutable $day */
$day = $route->getParams()['day'];
echo 'Day from URL: ', $day->format('Y-m-d'), "\n\n";
/*
 * Output:
 *
 * Day from URL: 2017-01-01
 */

/*
 * Invalid date in URL
 * URL matches to regex, but validator of pattern does not allow for invalid date
 */
$invalidUrl = '/day/2018-01-32';
try {
    $router->match('GET', $invalidUrl);
} catch (HttpException $exception) {
    echo $exception->getMessage(), "\n\n";
}
/*
 * Output:
 *
 * 404 Not Found: GET /day/2018-01-32
 */

echo "Generating URLs:\n";
echo '  using object: ', $router->linkTo('showDay')->withParam('day', new \DateTime('2017-01-01')), "\n";
echo '  using string: ', $router->linkTo('showDay')->withParam('day', '2017-02-01'), "\n";
echo '  using int:    ', $router->linkTo('showDay')->withParam('day', strtotime('2017-03-01')), "\n";
/*
 * Output:
 *
 * Generating URLs:
 *   using object: /day/2017-01-01
 *   using string: /day/2017-02-01
 *   using int:    /day/2017-03-01
 */
