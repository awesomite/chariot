# Chariot

[![Codacy Badge](https://api.codacy.com/project/badge/Grade/ca2c76b33b5042d49658105bc5b63075)](https://www.codacy.com/app/awesomite/chariot?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=awesomite/chariot&amp;utm_campaign=Badge_Grade)
[![Build Status](https://travis-ci.org/awesomite/chariot.svg?branch=master)](https://travis-ci.org/awesomite/chariot)
[![Coverage Status](https://coveralls.io/repos/github/awesomite/chariot/badge.svg?branch=master)](https://coveralls.io/github/awesomite/chariot?branch=master)

[github.com/awesomite/chariot](https://github.com/awesomite/chariot)

Just another routing library. Makes human-friendly URLs and programmer-friendly code.
Uses trees for the best performance.

## Why?

To simplify creating human-friendly URLs.

```php
<?php

/** @var Awesomite\Chariot\RouterInterface $router */
echo $router->linkTo('showArticle')->withParam('id', 5);
```

## Table of contents
* [How does it work?](#how-does-it-work)
    * [Patterns](#patterns)
      * [Parameters](#parameters)
      * [Examples](#examples)
    * [Routing](#routing)
    * [Generating links](#generating-links)
    * [Hidden parameters](#hidden-parameters)
    * [Caching](#caching)
    * [Defining custom patterns](#defining-custom-patterns)
    * [Validation](#validation)
    * [Default parameters](#default-parameters)
    * [Transforming parameters](#transforming-parameters)
    * [Default patterns](#default-patterns)
 * [More examples](#more-examples)
 * [License](#license)
 * [Versioning](#versioning)

## How does it work?

### Patterns

Patterns are designed to maximally simplify creating routing in your application.
Patterns can have parameters packed in `{{` double curly brackets `}}`.

#### Parameters

Parameters contains three values separated by one or more white characters.
Second and third values are optional.
First value is just a name.
Second value is a regular expression or name of registered regular expression,
default value is equal to `[^/]+`.
Third value contains default value of parameter (used for generating links).

#### Examples

I believe that the best documentation are examples from the real world. The following patterns should help you to understand how does it work.

* `/page/{{ page :uint }}`
* `/page/{{page \d+ 1}}`
* `/categories/{{ name [a-zA-Z0-9-]+ }}`
* `/categories/{{ categoryName }}/item-{{ itemId :uint }}`

### Routing

```php
<?php

use Awesomite\Chariot\Pattern\PatternRouter;
use Awesomite\Chariot\HttpMethods;
use Awesomite\Chariot\Exceptions\HttpException;

$router = PatternRouter::createDefault();
$router->addRoute(HttpMethods::METHOD_GET, '/', 'home');

$method = 'GET';
$path = '/';

try {
    $route = $router->match($method, $path);
    $handler = $route->getHandler();
    echo $handler, "\n";
} catch (HttpException $exception) {
    echo $exception->getMessage(), "\n";
    
    // code can be equal to 404 or 405
    if ($exception->getCode() === HttpException::HTTP_METHOD_NOT_ALLOWED) {
        echo 'Allow: ', implode(', ', $router->getAllowedMethods($path)), "\n";   
    }
}
```

### Generating links

```php
<?php

use Awesomite\Chariot\Pattern\PatternRouter;
use Awesomite\Chariot\HttpMethods;

$router = PatternRouter::createDefault();
$router->addRoute(HttpMethods::METHOD_GET, '/category-{{ category :int }}', 'showCategory');

echo $router->linkTo('showCategory')->withParam('category', 5), "\n";
/*
 * Output:
 * /category-5 
 */
```

### Hidden parameters

Hidden parameters are a very useful mechanism.
Using them allows you to completely change routing in your application without changing code in a lot of places.
Let's look at the following scenario:

1. You have a category page in your website.
2. Path pattern to category page is equal to `/category-{{ id :uint }}`.
3. Link to category page is generated in many places in your code. Let's say **100**.
4. You want to change approach. Category's id in a link is not expected anymore. You want to have human-friendly links, e.g. `/books` instead of `/category-15`.
5. Using *old school* way of generating links forced you to rewrite code in **100** places. You have to spend time to rewriting code. The risk of error is high.

Instead of monotonous rewriting code you can change only one thing in routing.
This approuch helps you to save your time and protects your code from bugs.
The following pieces of code should help you understand how to rewrite routing.

**Old code**
```php
$router->get('/category-{{ id :uint }}', 'showCategory');
```

**New code**
```php
$router
    ->get('/fantasy', 'showCategory', ['id' => 1])
    ->get('/comedy', 'showCategory', ['id' => 2]);
```

**Note:**
In this case small number of categories (let's say 100) will not cause performance issue.
But keep in your mind - big number of routes assigned to one handler can slow down generating links.
I encourage you to execute performance tests on your machine.
Exemplary test is attached to this repository, execute the following commands to perform it:

```bash
git clone --depth 1 git@github.com:awesomite/chariot.git
cd chariot
composer update
php speedtest/console.php test-links
```

**Bigger example**
```php
<?php

use Awesomite\Chariot\Pattern\PatternRouter;

$router = PatternRouter::createDefault();
$router->get('/show-first-page', 'showPage', [
    'page' => 1,
]);
$router->get('/page-{{ page :uint }}', 'showPage');

$route = $router->match('GET', '/show-first-page');
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
/*
 * Output:
 * /show-first-page
 * /page-2
 */
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
use Awesomite\Chariot\Pattern\Patterns;

$categories = [
    'action',
    'adventure',
    'comedy',
];

$router = new PatternRouter(new Patterns());
$router->getPatterns()
    ->addPattern(':date', '[0-9]{4}-[0-9]{2}-[0-9]{2}')
    ->addEnumPattern(':category', $categories);

$router->get('/day-{{ date :date }}', 'showDay');
$route = $router->match('GET', '/day-2017-01-01');
echo $route->getParams()['date'], "\n"; // 2017-01-01

$router->get('/category-{{ category :category }}', 'showCategory');
$route = $router->match('GET', '/category-comedy');
echo $route->getParams()['category'], "\n"; // comedy
```

### Validation

**Chariot** checks correctness of values incoming (routing) and outcoming (generating links).

**Note:**
Method `PatternRouter->linkTo()` returns instance of `LinkInterface`.
[Read description](src/LinkInterface.php) to understand difference between `toString()` and `__toString()` methods.

```php
<?php

use Awesomite\Chariot\Pattern\PatternRouter;
use Awesomite\Chariot\Exceptions\HttpException;
use Awesomite\Chariot\Exceptions\CannotGenerateLinkException;

$router = PatternRouter::createDefault();
$router->get('/category-{{ categoryId :int }}', 'showCategory');

/*
 * The following code displays "Error 404"
 */
try {
    $route = $router->match('GET', '/category-books');
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
    echo $router->linkTo('showCategory')->withParam('categoryId', 'books')->toString(), "\n";
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

### Transforming parameters

Router can transform parameter extracted from URL (and parameter passed to URL).
Passed object to method addPattern() must implements interface PatternInterface.
@See [PatternInterface](src/Pattern/PatternInterface.php).

```php
<?php

use Awesomite\Chariot\Pattern\PatternInterface;
use Awesomite\Chariot\Pattern\PatternRouter;
use Awesomite\Chariot\Pattern\Patterns;
use Awesomite\Chariot\Pattern\StdPatterns\DatePattern;

$router = new PatternRouter(new Patterns());
/*
 * Passed object to method addPattern() must implement interface PatternInterface
 */
$router->getPatterns()->addPattern(':date', new DatePattern());
$router->get('/day/{{ day :date }}', 'showDay');
echo $router->linkTo('showDay')->withParam('day', new \DateTime('2017-07-07')), "\n";

/*
 * Output:
 * /day/2017-07-07
 */
```

### Default patterns

Method `Awesomite\Chariot\Pattern\Patterns::createDefault()`
returns instance of `Awesomite\Chariot\Pattern\Patterns`
with set of standard patterns:

| name      | exemplary input  | class/regex          |
|-----------|------------------|----------------------|
| :int      | `-5`             | [IntPattern]         |
| :uint     | `5`              | [UnsignedIntPattern] |
| :date     | `2017-01-01`     | [DatePattern]        |
| :list     | `red,green,blue` | [ListPattern]        |
| :ip4      | `8.8.8.8`        | [Ip4Pattern]         |
| :alphanum | `nickname2000`   | `[a-zA-Z0-9]+`       |

## More examples

* [Own micro framework](examples/micro-framework.php)
* [Months](examples/months.php)
* [Symfony integration](examples/symfony.php)
* [Transforming parameters](examples/transform-params.php)

## License

MIT - [read license](LICENSE)

## Versioning

The version numbers follow the [Semantic Versioning 2.0.0](http://semver.org/) scheme.

[IntPattern](src/Pattern/StdPatterns/IntPattern.php)
[UnsignedIntPattern](src/Pattern/StdPatterns/UnsignedIntPattern.php)
[DatePattern](src/Pattern/StdPatterns/DatePattern.php)
[ListPattern](src/Pattern/StdPatterns/ListPattern.php)
[Ip4Pattern](src/Pattern/StdPatterns/Ip4Pattern.php)
