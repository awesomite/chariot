# Chariot

[![Build Status](https://travis-ci.org/awesomite/chariot.svg?branch=master)](https://travis-ci.org/awesomite/chariot)
[![Coverage Status](https://coveralls.io/repos/github/awesomite/chariot/badge.svg?branch=master)](https://coveralls.io/github/awesomite/chariot?branch=master)

Just another routing library.

## Why?

To simplify creating human-friendly URLs.

## How does it work?

### Routing

```php
<?php

use Awesomite\Chariot\Pattern\PatternRouter;
use Awesomite\Chariot\HttpMethods;
use Awesomite\Chariot\Exceptions\HttpException;

$router = PatternRouter::createDefault();
$router->addRoute(HttpMethods::METHOD_GET, '/', 'home');

try {
    $route = $router->match(HttpMethods::METHOD_GET, '/');
    $handler = $route->getHandler();
    echo $handler;
} catch (HttpException $exception) {
    // code can be equal to 404 or 405
    echo 'Error ' . $exception->getCode();
}
```

### Generating links

```php
<?php

use Awesomite\Chariot\Pattern\PatternRouter;
use Awesomite\Chariot\HttpMethods;

$router = PatternRouter::createDefault();
$router->addRoute(HttpMethods::METHOD_GET, '/category-{{ category :int }}', 'showCategory');

echo $router->linkTo('showCategory')->withParam('category', 5);
/**
 * Output:
 * /category-5 
 */
```

### Hidden parameters

```php
<?php

use Awesomite\Chariot\Pattern\PatternRouter;
use Awesomite\Chariot\HttpMethods;

$router = PatternRouter::createDefault();
$router->get('/show-first-page', 'showPage', [
    'page' => 1,
]);
$router->get('/page-{{ page :uint }}', 'showPage');

$route = $router->match(HttpMethods::METHOD_GET, '/show-first-page');
echo $route->getHandler(), "\n";
var_dump($route->getParams());
/*
 * Output:
 * showPage
 * array(1) {
 *   'page' =>
 *   int(1)
 * }
 */

echo $router->linkTo('showPage')->withParam('page', 1), "\n"; // /show-first-page
echo $router->linkTo('showPage')->withParam('page', 2), "\n"; // /page-2

```

### Caching

```php
<?php

use Awesomite\Chariot\Pattern\PatternRouter;

class RouterFactory
{
    private $cacheFile;

    public function __construct(string $cacheFile)
    {
        $this->cacheFile = $cacheFile;
    }

    public function rebuildRouter()
    {
        $router = $this->createRouter();
        file_put_contents($this->cacheFile, '<?php return ' . $router->exportToExecutable() . ';');
    }

    public function getRouter(): PatternRouter
    {
        return require $this->cacheFile;
    }

    private function createRouter(): PatternRouter
    {
        return PatternRouter::createDefault()
            ->get('/', 'showHome')
            ->get('/news', 'showNews', ['page' => 1])
            ->get('/news/{{ page :int }}', 'showNews');
    }
}

$factory = new RouterFactory(__DIR__ . DIRECTORY_SEPARATOR . 'router.cache');
// Executing this function once is enough, e.g. during warmup
$factory->rebuildRouter();
$router = $factory->getRouter();
```

### Defining custom patterns

```php
<?php

use Awesomite\Chariot\Pattern\PatternRouter;
use Awesomite\Chariot\HttpMethods;

$months = [
    'jan',
    'feb',
    'mar',
    'apr',
    'may',
    'june',
    'july',
    'aug',
    'sept',
    'oct',
    'nov',
    'dec',
];

$router = PatternRouter::createDefault();
$router->getPatterns()
    ->addPattern(':date', '[0-9]{4}-[0-9]{2}-[0-9]{2}')
    ->addEnumPattern(':month', $months);

$router->get('/day-{{ date :date }}', 'showDay');
$route = $router->match(HttpMethods::METHOD_GET, '/day-2017-01-01');
echo $route->getParams()['date'], "\n"; // 2017-01-01

$router->get('/month-{{ month :month }}', 'showMonth');
$route = $router->match(HttpMethods::METHOD_GET, '/month-sept');
echo $route->getParams()['month'], "\n"; // sept
```

### Validation

Method `PatternRouter->linkTo()` returns instance of `LinkInterface`.
[Read description](src/LinkInterface.php) to understand difference between `toString()` and `__toString()` methods.

```php
<?php

use Awesomite\Chariot\Pattern\PatternRouter;
use Awesomite\Chariot\HttpMethods;
use Awesomite\Chariot\Exceptions\HttpException;
use Awesomite\Chariot\Exceptions\CannotGenerateLinkException;

$router = PatternRouter::createDefault();
$router->get('/category-{{ categoryId :int }}', 'showCategory');

/*
 * The following code displays "Error 404"
 */
try {
    $route = $router->match(HttpMethods::METHOD_GET, '/category-books');
    echo "Handler:\n", $route->getHandler(), "\n";
    echo "Params:\n";
    var_dump($route->getParams());
} catch (HttpException $exception) {
    echo 'Error ', $exception->getCode(), "\n";
}

/*
 * The following code displays "Cannot generate link"
 */
try {
    echo $router->linkTo('showCategory')->withParam('categoryId', 'books')->toString();
} catch (CannotGenerateLinkException $exception) {
    echo "Cannot generate link\n";
}
```

### Default parameters

```php
<?php

use Awesomite\Chariot\Pattern\PatternRouter;

$router = PatternRouter::createDefault();
$router->get('/articles/{{ page :uint 1 }}', 'articles');
echo $router->linkTo('articles'), "\n";
echo $router->linkTo('articles')->withParam('page', 2), "\n";

/*
 * Output:
 * /articles/1
 * /articles/2
 */
```

## License

MIT - [read license](LICENSE).
