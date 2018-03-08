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

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers PHPUnit\NewRunner\TestFinder
 *
 * @uses PHPUnit\NewRunner\DataProvider
 * @uses PHPUnit\NewRunner\DataProviderCollection
 * @uses PHPUnit\NewRunner\TestCollection
 * @uses PHPUnit\NewRunner\TestCollectionIterator
 * @uses PHPUnit\NewRunner\TestMethod
 * @uses PHPUnit\NewRunner\TestMethodCollection
 * @uses PHPUnit\NewRunner\TestMethodWithDataProvider
 * @uses PHPUnit\NewRunner\TestMethodWithDependencies
 */
final class TestFinderTest extends TestCase
{
    /**
     * @var TestFinder
     */
    private $finder;

    private $fixtureDirectory;

    protected function setUp(): void
    {
        /** @var Cache|MockObject $cache */
        $cache = $this->createMock(Cache::class);

        $this->finder = new TestFinder($cache);

        $this->fixtureDirectory = \realpath(__DIR__ . '/../../_fixture');
    }

    public function testFindsTestMethods(): void
    {
        /** @var TestMethod[] $tests */
        $tests = \iterator_to_array($this->finder->find([$this->fixtureDirectory]));

        $this->assertInstanceOf(TestMethod::class, $tests[0]);
        $this->assertEquals($this->fixtureDirectory . '/' . 'FooTest.php', $tests[0]->sourceFile());
        $this->assertEquals('PHPUnit\NewRunner\FooTest', $tests[0]->className());
        $this->assertEquals('testOne', $tests[0]->methodName());
        $this->assertEquals([new Annotation('covers', 'Foo')], \iterator_to_array($tests[0]->classLevelAnnotations()));
        $this->assertEmpty($tests[0]->methodLevelAnnotations());

        $this->assertInstanceOf(TestMethod::class, $tests[1]);
        $this->assertEquals($this->fixtureDirectory . '/' . 'FooTest.php', $tests[1]->sourceFile());
        $this->assertEquals('PHPUnit\NewRunner\FooTest', $tests[1]->className());
        $this->assertEquals('testTwo', $tests[1]->methodName());
        $this->assertEquals([new Annotation('covers', 'Foo')], \iterator_to_array($tests[1]->classLevelAnnotations()));
        $this->assertEquals([new Annotation('depends', 'testOne')], \iterator_to_array($tests[1]->methodLevelAnnotations()));

        $this->assertInstanceOf(TestMethod::class, $tests[2]);
        $this->assertEquals($this->fixtureDirectory . '/' . 'FooTest.php', $tests[2]->sourceFile());
        $this->assertEquals('PHPUnit\NewRunner\FooTest', $tests[2]->className());
        $this->assertEquals('testThree', $tests[2]->methodName());
        $this->assertEquals([new Annotation('covers', 'Foo')], \iterator_to_array($tests[2]->classLevelAnnotations()));
        $this->assertEquals([new Annotation('dataProvider', 'dataProvider')], \iterator_to_array($tests[2]->methodLevelAnnotations()));
    }
}
