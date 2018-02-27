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

class TestMethodWithDataProvider extends TestMethod
{
    /**
     * @var DataProviderCollection
     */
    private $dataProvider;

    public function __construct(string $sourceFile, string $className, string $methodName, DataProviderCollection $dataProvider)
    {
        parent::__construct($sourceFile, $className, $methodName);

        $this->dataProvider = $dataProvider;
    }

    public function dataProvider(): DataProviderCollection
    {
        return $this->dataProvider;
    }
}
