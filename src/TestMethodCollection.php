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

final class TestMethodCollection implements \Countable, \IteratorAggregate
{
    /**
     * @var TestMethod[]
     */
    private $testMethods = [];

    public function count(): int
    {
        return \count($this->testMethods);
    }

    public function add(TestMethod $testMethod): void
    {
        $this->testMethods[] = $testMethod;
    }

    /**
     * @return TestMethod[]
     */
    public function getItems(): array
    {
        return $this->testMethods;
    }

    public function getIterator(): TestMethodCollectionIterator
    {
        return new TestMethodCollectionIterator($this);
    }
}
