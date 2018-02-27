<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPUnit\NewRunner;

final class TestMethodExecutor
{
    public function execute(TestMethod $testMethod): void
    {
        require_once $testMethod->sourceFile();

        $className  = $testMethod->className();
        $methodName = $testMethod->methodName();

        $test = new $className;

        $test->$methodName();
    }
}
