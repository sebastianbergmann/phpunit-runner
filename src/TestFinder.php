<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPUnit\Runner;

use PHPUnit\Framework\TestCase;
use Roave\BetterReflection\BetterReflection;
use Roave\BetterReflection\Reflector\ClassReflector;
use Roave\BetterReflection\SourceLocator\Exception\EmptyPhpSourceCode;
use Roave\BetterReflection\SourceLocator\Type\AggregateSourceLocator;
use Roave\BetterReflection\SourceLocator\Type\AutoloadSourceLocator;
use Roave\BetterReflection\SourceLocator\Type\StringSourceLocator;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

final class TestFinder
{
    /**
     * @throws EmptyPhpSourceCode
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function find(array $directories): TestCollection
    {
        $tests = new TestCollection;

        foreach ($this->findTestFilesInDirectories($directories) as $file) {
            foreach ($this->findClassesInFile($file) as $class) {
                if ($class->isSubclassOf(TestCase::class)) {
                    print '*';
                }
            }
        }

        return $tests;
    }

    /**
     * @throws \InvalidArgumentException
     */
    private function findTestFilesInDirectories(array $directories): Finder
    {
        $finder = new Finder;

        $finder->files()
               ->in($directories)
               ->name('*Test.php')
               ->sortByName();

        return $finder;
    }

    /**
     * @throws \RuntimeException
     * @throws EmptyPhpSourceCode
     */
    private function findClassesInFile(SplFileInfo $file): array
    {
        $reflector = new ClassReflector($this->createSourceLocator($file->getContents()));

        return $reflector->getAllClasses();
    }

    /**
     * @throws EmptyPhpSourceCode
     */
    private function createSourceLocator(string $source): AggregateSourceLocator
    {
        $astLocator = (new BetterReflection())->astLocator();

        return new AggregateSourceLocator(
            [
                new StringSourceLocator($source, $astLocator),
                new AutoloadSourceLocator($astLocator)
            ]
        );
    }
}
