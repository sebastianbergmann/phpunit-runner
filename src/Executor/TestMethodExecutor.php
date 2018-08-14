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

final class TestMethodExecutor implements Executor
{
    /**
     * @var TestMethod
     */
    private $method;

    public function __construct(TestMethod $method)
    {
        $this->method = $method;
    }

    public function execute(Result $result): void
    {
        require_once $this->method->sourceFile();

        $className  = $this->method->className();
        $methodName = $this->method->methodName();

        $test = new $className;

        $test->$methodName();
    }
}
