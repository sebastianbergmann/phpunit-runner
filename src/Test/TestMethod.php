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

class TestMethod implements Test
{
    /**
     * @var string
     */
    private $sourceFile;

    /**
     * @var string
     */
    private $className;

    /**
     * @var string
     */
    private $methodName;

    /**
     * @var AnnotationCollection
     */
    private $classLevelAnnotations;

    /**
     * @var AnnotationCollection
     */
    private $methodLevelAnnotations;

    public function __construct(string $sourceFile, string $className, string $methodName, AnnotationCollection $classLevelAnnotations, AnnotationCollection $methodLevelAnnotations)
    {
        $this->sourceFile             = $sourceFile;
        $this->className              = $className;
        $this->methodName             = $methodName;
        $this->classLevelAnnotations  = $classLevelAnnotations;
        $this->methodLevelAnnotations = $methodLevelAnnotations;
    }

    public function sourceFile(): string
    {
        return $this->sourceFile;
    }

    public function className(): string
    {
        return $this->className;
    }

    public function methodName(): string
    {
        return $this->methodName;
    }

    public function classLevelAnnotations(): AnnotationCollection
    {
        return $this->classLevelAnnotations;
    }

    public function methodLevelAnnotations(): AnnotationCollection
    {
        return $this->methodLevelAnnotations;
    }
}
