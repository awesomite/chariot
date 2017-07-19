<?php

namespace Awesomite\Chariot\Speedtest;

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
