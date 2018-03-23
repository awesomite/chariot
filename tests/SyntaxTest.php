<?php

/*
 * This file is part of the awesomite/chariot package.
 * (c) Bartłomiej Krukowski <bartlomiej@krukowski.me>
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Awesomite\Chariot;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * @internal
 */
class SyntaxTest extends TestBase
{
    /**
     * @runInSeparateProcess
     */
    public function testSyntax()
    {
        $counter = 0;
        foreach ($this->findFiles('src', '*.php') as $file) {
            require_once $file->getRealPath();
            $counter++;
        }
        $this->assertGreaterThan(0, $counter);
    }

    /**
     * @param string $path
     * @param string $pattern
     *
     * @return SplFileInfo[]|\Traversable
     */
    private function findFiles(string $path, string $pattern)
    {
        $delimiter = DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR;
        $explodedPath = \explode($delimiter, __FILE__);
        \array_pop($explodedPath);
        $realPath = \implode($delimiter, $explodedPath) . DIRECTORY_SEPARATOR . $path;

        return (new Finder())
            ->in($realPath)
            ->name($pattern);
    }
}
