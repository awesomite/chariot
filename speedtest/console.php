<?php

/*
 * This file is part of the awesomite/chariot package.
 * (c) Bartłomiej Krukowski <bartlomiej@krukowski.me>
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

use Awesomite\Chariot\Speedtest\Application;
use Composer\XdebugHandler\XdebugHandler;

require \implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'vendor', 'autoload.php']);

$file = __DIR__ . '/../vendor/composer/xdebug-handler/src/XdebugHandler.php';
$contents = \file_get_contents($file);
$contents = \str_replace(
    "\$content .= 'opcache.enable_cli=0'.PHP_EOL;",
    "\$content .= 'opcache.enable_cli=1'.PHP_EOL; \$content .= 'opcache.enable=1'.PHP_EOL;",
    $contents
);
\file_put_contents($file, $contents);

$xdebugHandler = new XdebugHandler('AWESOMITE_CHARIOT', '--ansi');
$xdebugHandler->check();
unset($xdebugHandler);

$application = new Application();
$application->run();
