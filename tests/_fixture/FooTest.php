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

final class FooTest extends TestCase
{
    public function testOne(): bool
    {
        $this->assertTrue(true);

        return true;
    }

    /**
     * @depends testOne
     */
    public function testTwo(bool $dependencyInput): void
    {
        $this->assertTrue($dependencyInput);
    }

    /**
     * @dataProvider dataProvider
     */
    public function testThree(bool $dataProviderInput): void
    {
        $this->assertTrue($dataProviderInput);
    }

    public function dataProvider(): array
    {
        return [
            [true]
        ];
    }
}
