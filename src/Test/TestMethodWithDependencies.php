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

class TestMethodWithDependencies extends TestMethod
{
    /**
     * @var TestMethodCollection
     */
    private $dependencies;

    public function __construct(string $testSourceFile, string $testClassName, string $testMethodName, TestMethodCollection $dependencies)
    {
        parent::__construct($testSourceFile, $testClassName, $testMethodName);

        $this->dependencies = $dependencies;
    }

    public function dependencies(): TestMethodCollection
    {
        return $this->dependencies;
    }
}
