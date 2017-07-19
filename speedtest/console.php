<?php

use Awesomite\Chariot\Speedtest\Application;

require implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'vendor', 'autoload.php']);

$application = new Application();
$application->run();
