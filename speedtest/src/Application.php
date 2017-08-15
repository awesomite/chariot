<?php

namespace Awesomite\Chariot\Speedtest;

use Awesomite\Chariot\Speedtest\Commands\LinksCommand;
use Symfony\Component\Console\Application as SymfonyApplication;

/**
 * @internal
 */
class Application extends SymfonyApplication
{
    public function __construct()
    {
        parent::__construct('Chariot Speed Test', 'dev');
        $this->add(new LinksCommand());
    }
}
