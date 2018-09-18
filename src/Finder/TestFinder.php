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

use PHPUnit\Framework\TestCase;
use Roave\BetterReflection\BetterReflection;
use Roave\BetterReflection\Reflection\ReflectionClass;
use Roave\BetterReflection\Reflection\ReflectionMethod;
use Roave\BetterReflection\Reflector\ClassReflector;
use Roave\BetterReflection\SourceLocator\Exception\EmptyPhpSourceCode;
use Roave\BetterReflection\SourceLocator\Type\AggregateSourceLocator;
use Roave\BetterReflection\SourceLocator\Type\AutoloadSourceLocator;
use Roave\BetterReflection\SourceLocator\Type\PhpInternalSourceLocator;
use Roave\BetterReflection\SourceLocator\Type\StringSourceLocator;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

final class TestFinder
{
    /**
     * @var Cache
     */
    private $cache;

    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @throws EmptyPhpSourceCode
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function find(DirectoryCollection $directories): TestCollection
    {
        $tests = new TestCollection;

        foreach ($this->findTestFilesInDirectories($directories) as $file) {
            if ($this->cache->has($file->getRealPath())) {
                $testMethodsInFile = $this->cache->get($file->getRealPath());
            } else {
                $testMethodsInFile = $this->findTestMethodsInFile($file);

                $this->cache->set($file->getRealPath(), $testMethodsInFile);
            }

            $tests->addFrom($testMethodsInFile);
        }

        return $tests;
    }

    /**
     * @throws \InvalidArgumentException
     */
    private function findTestFilesInDirectories(DirectoryCollection $directories): Finder
    {
        $finder = new Finder;

        $finder->files()
               ->in($directories->asArray())
               ->name('*Test.php')
               ->sortByName();

        return $finder;
    }

    /**
     * @throws \RuntimeException
     * @throws EmptyPhpSourceCode
     */
    private function findTestMethodsInFile(SplFileInfo $file): TestCollection
    {
        $tests = new TestCollection;

        foreach ($this->findTestClassesInFile($file) as $class) {
            $className             = $class->getName();
            $sourceFile            = $file->getRealPath();
            $classLevelAnnotations = $this->parseAnnotations($class->getDocComment());

            foreach ($class->getMethods() as $method) {
                if (!$this->isTestMethod($method)) {
                    continue;
                }

                $tests->add(
                    new TestMethod(
                        $sourceFile,
                        $className,
                        $method->getName(),
                        $classLevelAnnotations,
                        $this->parseAnnotations($method->getDocComment())
                    )
                );
            }
        }

        return $tests;
    }

    /**
     * @throws \RuntimeException
     * @throws EmptyPhpSourceCode
     *
     * @return ReflectionClass[]
     */
    private function findTestClassesInFile(SplFileInfo $file): array
    {
        $reflector   = new ClassReflector($this->createSourceLocator($file->getContents()));
        $testClasses = [];

        foreach ($reflector->getAllClasses() as $class) {
            if (!$this->isTestClass($class)) {
                continue;
            }

            $testClasses[] = $class;
        }

        return $testClasses;
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
                new AutoloadSourceLocator($astLocator),
                new PhpInternalSourceLocator($astLocator)
            ]
        );
    }

    private function isTestClass(ReflectionClass $class): bool
    {
        return !$class->isAbstract() && $class->isSubclassOf(TestCase::class);
    }

    private function isTestMethod(ReflectionMethod $method): bool
    {
        if (\strpos($method->getName(), 'test') !== 0) {
            return false;
        }

        if ($method->isAbstract() || !$method->isPublic()) {
            return false;
        }

        return true;
    }

    private function parseAnnotations(string $docBlock): AnnotationCollection
    {
        $annotations = new AnnotationCollection;
        $docBlock    = (string) \substr($docBlock, 3, -2);

        if (\preg_match_all('/@(?P<name>[A-Za-z_-]+)(?:[ \t]+(?P<value>.*?))?[ \t]*\r?$/m', $docBlock, $matches)) {
            $numMatches = \count($matches[0]);

            for ($i = 0; $i < $numMatches; ++$i) {
                $annotations->add(
                    new Annotation(
                        (string) $matches['name'][$i],
                        (string) $matches['value'][$i]
                    )
                );
            }
        }

        return $annotations;
    }
}
