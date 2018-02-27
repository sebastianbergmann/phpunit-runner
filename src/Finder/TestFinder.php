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

use PHPUnit\Framework\Assert;
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
     * @throws \PHPUnit\NewRunner\NotYetSupportedException
     * @throws EmptyPhpSourceCode
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function find(array $directories): TestCollection
    {
        $tests = new TestCollection;

        foreach ($this->findTestFilesInDirectories($directories) as $file) {
            if ($this->cache->has($file->getRealPath())) {
                $testsInFile = $this->cache->get($file->getRealPath());
            } else {
                $testsInFile = $this->findTestsInFile($file);

                $this->cache->set($file->getRealPath(), $testsInFile);
            }

            $tests->addFrom($testsInFile);
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
     * @throws NotYetSupportedException
     * @throws \RuntimeException
     * @throws EmptyPhpSourceCode
     */
    private function findTestsInFile(SplFileInfo $file): TestCollection
    {
        $tests = new TestCollection;

        foreach ($this->findClassesInFile($file) as $class) {
            if (!$this->isTestClass($class)) {
                continue;
            }

            $className  = $class->getName();
            $sourceFile = $file->getRealPath();

            foreach ($class->getMethods() as $method) {
                if (!$this->isTestMethod($method)) {
                    continue;
                }

                $annotations  = $this->annotations($method->getDocComment());
                $dataProvider = $this->dataProvider($sourceFile, $className, $annotations);
                $dependencies = $this->dependencies($sourceFile, $className, $annotations);

                if (\count($dataProvider) > 0 && \count($dependencies) > 0) {
                    throw new NotYetSupportedException(
                        'Using @dataProvider and @depends at the same time is not yet supported'
                    );
                }

                if ($dataProvider->count() > 0) {
                    $tests->add(new TestMethodWithDataProvider($sourceFile, $className, $method->getName(), $dataProvider));
                } elseif ($dependencies->count() > 0) {
                    $tests->add(new TestMethodWithDependencies($sourceFile, $className, $method->getName(), $dependencies));
                } else {
                    $tests->add(new TestMethod($sourceFile, $className, $method->getName()));
                }
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

        if ($method->getDeclaringClass()->getName() === Assert::class) {
            return false;
        }

        if ($method->getDeclaringClass()->getName() === TestCase::class) {
            return false;
        }

        return true;
    }

    private function annotations(string $docBlock): AnnotationCollection
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

    /**
     * @throws NotYetSupportedException
     */
    private function dataProvider(string $className, string $sourceFile, AnnotationCollection $annotations): DataProviderCollection
    {
        $dataProvider = new DataProviderCollection;

        foreach ($annotations as $annotation) {
            if ($annotation->name() !== 'dataProvider') {
                continue;
            }

            if (\strpos($annotation->value(), '::') === false) {
                $dataProvider->add(new DataProvider($sourceFile, $className, $annotation->value()));

                continue;
            }

            throw new NotYetSupportedException(
                'Using a data provider from another class is not yet supported'
            );
        }

        return $dataProvider;
    }

    /**
     * @throws NotYetSupportedException
     */
    private function dependencies(string $className, string $sourceFile, AnnotationCollection $annotations): TestMethodCollection
    {
        $dependencies = new TestMethodCollection;

        foreach ($annotations as $annotation) {
            if ($annotation->name() !== 'depends') {
                continue;
            }

            if (\strpos($annotation->value(), '::') === false) {
                $dependencies->add(new TestMethod($sourceFile, $className, $annotation->value()));

                continue;
            }

            throw new NotYetSupportedException(
                'Depending on test methods in another class is not yet supported'
            );
        }

        return $dependencies;
    }
}
