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

final class DirectoryCollection implements \Countable, \IteratorAggregate
{
    /**
     * @var Directory[]
     */
    private $directories = [];

    public static function fromArray(Directory ...$directories): self
    {
        $collection = new self;

        foreach ($directories as $directory) {
            $collection->add($directory);
        }

        return $collection;
    }

    public function count(): int
    {
        return \count($this->directories);
    }

    public function add(Directory $directory): void
    {
        $this->directories[] = $directory;
    }

    /**
     * @return Directory[]
     */
    public function directories(): array
    {
        return $this->directories;
    }

    public function getIterator(): DirectoryCollectionIterator
    {
        return new DirectoryCollectionIterator($this);
    }
}
