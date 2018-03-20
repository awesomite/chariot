<?php

/*
 * This file is part of the awesomite/chariot package.
 * (c) Bartłomiej Krukowski <bartlomiej@krukowski.me>
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Awesomite\Chariot\Speedtest;

use Awesomite\Chariot\Speedtest\Commands\CacheCommand;
use Awesomite\Chariot\Speedtest\Commands\LinksCommand;
use Awesomite\Chariot\Speedtest\Commands\ProvidersCommand;
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
        $this->add(new ProvidersCommand());
        $this->add(new CacheCommand());
    }
}
