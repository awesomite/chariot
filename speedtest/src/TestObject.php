<?php

/*
 * This file is part of the awesomite/chariot package.
 * (c) Bartłomiej Krukowski <bartlomiej@krukowski.me>
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Awesomite\Chariot\Speedtest;

use Awesomite\Chariot\ExportableTrait;

class TestObject
{
    use ExportableTrait;

    public function __construct(int $numberOfProperties)
    {
        for ($i = 0; $i < $numberOfProperties; $i++) {
            $this->{StringsHelper::getRandomString(10)} = $this->createValue();
        }
    }

    private function createValue()
    {
        switch (\mt_rand(1, 2)) {
            case 1:
                return StringsHelper::getRandomString(10);

            case 2:
                return \range(1, 5);
        }
    }
}
