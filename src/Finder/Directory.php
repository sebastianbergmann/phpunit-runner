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

final class Directory
{
    /**
     * @var string
     */
    private $directory;

    public static function fromString(string $directory): self
    {
        return new self($directory);
    }

    private function __construct(string $directory)
    {
        $this->ensureDirectoryExists($directory);

        $this->directory = \realpath($directory);
    }

    public function __toString(): string
    {
        return $this->directory;
    }

    private function ensureDirectoryExists(string $directory): void
    {
        if (!\is_dir($directory)) {
            throw new InvalidDirectoryException(
                \sprintf(
                    'Directory "%s" does not exist',
                    $directory
                )
            );
        }
    }
}
