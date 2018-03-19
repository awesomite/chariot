# Changelog

### [?.?.?] - ????-??-??

* Added `Awesomite\Chariot\ParamDecorators\ParamDecoratorInterface`,
see examples:
  * `examples/param-decorator.php`
  * `examples/param-decorator2.php`
* Changed - cannot add route after restoring router from cache
```php
<?php

use Awesomite\Chariot\Pattern\PatternRouter;

// the following code will not work

$router = PatternRouter::createDefault();

$router = eval('return ' . $router->exportToExecutable() . ';');
$router->get('/', 'home');
```
* Added restriction - each route for the same `handler` must contain the same list of parameters, e.g.:
```php
<?php

use Awesomite\Chariot\Pattern\PatternRouter;

$router = PatternRouter::createDefault();

$router->get('/article-{{ id :int }}-{{ name }}', 'showArticle');
// hidden parameter 'name' = null is required
$router->get('/article-{{ id :int }}', 'showArticle', ['name' => null]);
```

### [0.3.1] - 2017-09-17

* Fixed - `Awesomite\Chariot\Pattern\PatternRouter` did not work properly when pattern was prefixed by regular expression,
e.g. `{{ subdomain }}.local/`

### [0.3.0] - 2017-09-13

* Changed - method `Awesomite\Chariot\Pattern\Patterns::addPattern()`
  throws `Awesomite\Chariot\Exceptions\InvalidArgumentException`
  instead of `Awesomite\Chariot\Exceptions\LogicException` when argument `$name` is not prefixed by `:`
* Changed - method `Awesomite\Chariot\Pattern\Patterns::addPattern()`
  accepts stringable object (with method `__toString`) as argument `$pattern`
* Changed - everything outside `{{` double brackets `}}` is transformed by `preg_quote()` function.
* Constant `Awesomite\Chariot\Pattern\Patterns::DELIMITER` instead of hardcoded value `#`

### [0.2.1] - 2017-08-27

* Fixed regex `Awesomite\Chariot\Pattern\Patterns::REGEX_FLOAT`
* Fixed regex `Awesomite\Chariot\Pattern\Patterns::REGEX_UFLOAT`
* Fixed constant `Awesomite\Chariot\Pattern\Patterns::STANDARD_PATTERNS` (invalid value for `:ufloat`)

### [0.2.0] - 2017-08-27

* Added `Awesomite\Chariot\Pattern\PatternInterface` - possibility to conversions url params, e.g. date in format `YYYY-mm-dd` to `DateTime` object
* Added [behat] tests
* Force pattern names prefixed by ":"
* Changed `Awesomite\Chariot\Pattern\Patterns::createDefault()`, result is set of patterns:
  
  | name      | action          | class/regex            |
  |-----------|-----------------|------------------------|
  | :int      | changed         | [IntPattern]           |
  | :uint     | changed         | [UnsignedIntPattern]   |
  | :float    | added           | [FloatPattern]         |
  | :ufloat   | added           | [UnsignedFloatPattern] |
  | :date     | added           | [DatePattern]          |
  | :list     | added           | [ListPattern]          |
  | :ip4      | added           | [Ip4Pattern]           |
  | :alphanum | same as earlier | `[a-zA-Z0-9]+`         |

### [0.1.0] - 2017-07-20
    
* Initial public release

[0.3.1]: https://github.com/awesomite/chariot/compare/v0.3.0...v0.3.1
[0.3.0]: https://github.com/awesomite/chariot/compare/v0.2.1...v0.3.0
[0.2.1]: https://github.com/awesomite/chariot/compare/v0.2.0...v0.2.1
[0.2.0]: https://github.com/awesomite/chariot/compare/v0.1.0...v0.2.0
[0.1.0]: https://github.com/awesomite/chariot/tree/v0.1.0
[behat]: http://behat.org

[IntPattern]:           src/Pattern/StdPatterns/IntPattern.php
[UnsignedIntPattern]:   src/Pattern/StdPatterns/UnsignedIntPattern.php
[FloatPattern]:         src/Pattern/StdPatterns/FloatPattern.php
[UnsignedFloatPattern]: src/Pattern/StdPatterns/UnsignedFloatPattern.php
[DatePattern]:          src/Pattern/StdPatterns/DatePattern.php
[ListPattern]:          src/Pattern/StdPatterns/ListPattern.php
[Ip4Pattern]:           src/Pattern/StdPatterns/Ip4Pattern.php
