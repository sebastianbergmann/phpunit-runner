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

final class DataProviderCollection implements \Countable, \IteratorAggregate
{
    /**
     * @var DataProvider[]
     */
    private $dataProvider = [];

    public function count(): int
    {
        return \count($this->dataProvider);
    }

    public function add(DataProvider $dataProvider): void
    {
        $this->dataProvider[] = $dataProvider;
    }

    /**
     * @return DataProvider[]
     */
    public function getItems(): array
    {
        return $this->dataProvider;
    }

    public function getIterator(): DataProviderCollectionIterator
    {
        return new DataProviderCollectionIterator($this);
    }
}
