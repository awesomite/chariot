# Changelog

### [0.2.0] 2017-08-27

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

### [0.1.0] 2017-07-20
    
* Initial public release

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
