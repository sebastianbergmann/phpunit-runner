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

final class FilesystemCache implements Cache
{
    /**
     * @var string
     */
    private $directory;

    /**
     * @throws InvalidCacheDirectoryException
     */
    public function __construct(string $directory)
    {
        $this->ensureDirectoryExists($directory);

        $this->directory = $directory;
    }

    public function get(string $sourceFile): TestCollection
    {
        return \unserialize(
            \file_get_contents($this->cacheFile($sourceFile)),
            [
                TestCollection::class,
                TestMethod::class
            ]
        );
    }

    public function has(string $sourceFile): bool
    {
        if (!\file_exists($this->cacheFile($sourceFile))) {
            return false;
        }

        if (\filemtime($sourceFile) > \filemtime($this->cacheFile($sourceFile))) {
            return false;
        }

        return true;
    }

    public function set(string $sourceFile, TestCollection $tests): void
    {
        \file_put_contents($this->cacheFile($sourceFile), \serialize($tests));
    }

    /**
     * @throws InvalidCacheDirectoryException
     */
    private function ensureDirectoryExists(string $directory): void
    {
        if (!@\mkdir($directory, 0777, true) && !\is_dir($directory)) {
            throw new InvalidCacheDirectoryException(
                \sprintf(
                    'Cannot use "%s" as cache directory',
                    $directory
                )
            );
        }
    }

    private function cacheFile(string $sourceFile): string
    {
        return $this->directory . '/' . \hash('sha256', $sourceFile);
    }
}
