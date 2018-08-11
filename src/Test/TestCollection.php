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

final class TestCollection implements \IteratorAggregate
{
    /**
     * @var Test[]
     */
    private $tests = [];

    public function add(Test $test): void
    {
        $this->tests[] = $test;
    }

    public function addFrom(self $tests): void
    {
        foreach ($tests as $test) {
            $this->tests[] = $test;
        }
    }

    /**
     * @return Test[]
     */
    public function asArray(): array
    {
        return $this->tests;
    }

    public function getIterator(): TestCollectionIterator
    {
        return new TestCollectionIterator($this);
    }
}
