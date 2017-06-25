<?php

namespace Kraken\_Unit\Container\Service;

use Kraken\_Unit\Container\_Provider\AProvider;
use Kraken\_Unit\Container\_Provider\BProvider;
use Kraken\_Unit\Container\_Provider\CProvider;
use Kraken\_Unit\Container\_Provider\DProvider;
use Kraken\_Unit\Container\_Provider\EProvider;
use Kraken\_Unit\Container\_Provider\FProvider;
use Kraken\_Unit\Container\_Provider\GProvider;
use Kraken\Container\Service\ServiceSorter;
use Kraken\Test\TUnit;
use Dazzle\Throwable\Exception\Logic\ResourceUndefinedException;
use Dazzle\Throwable\Exception\Runtime\OverflowException;

class ServiceSorterTest extends TUnit
{
    /**
     *
     */
    public function testApiConstructor_CreatesInstance()
    {
        $sorter = $this->createSorter();

        $this->assertInstanceOf(ServiceSorter::class, $sorter);
    }

    /**
     *
     */
    public function testApiDestructor_DoesNotThrowException()
    {
        $sorter = $this->createSorter();
        unset($sorter);
    }

    /**
     *
     */
    public function testApiSortProviders_SortProviders()
    {
        $sorter = $this->createSorter();
        $before = [
            $a = new AProvider,
            $b = new BProvider,
            $c = new CProvider,
            $d = new DProvider,
            $e = new EProvider,
        ];
        $after = [
            $a,
            $d,
            $b,
            $c,
            $e
        ];

        $this->assertSame($after, $sorter->sortProviders($before));
    }

    /**
     *
     */
    public function testApiSortProviders_ThrowsException_WhenRequiredDependenciesCouldNotBeResolved()
    {
        $sorter = $this->createSorter();
        $before = [
            $b = new BProvider,
            $c = new CProvider,
            $d = new DProvider,
            $e = new EProvider,
        ];

        $this->setExpectedException(ResourceUndefinedException::class);
        $sorter->sortProviders($before);
    }

    /**
     *
     */
    public function testApiSortProviders_ThrowsException_WhenCyclicDependencyExist()
    {
        $sorter = $this->createSorter();
        $before = [
            $f = new FProvider(),
            $g = new GProvider()
        ];

        $this->setExpectedException(OverflowException::class);
        $sorter->sortProviders($before);
    }

    /**
     * @param string[]|null $methods
     * @return ServiceSorter|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createSorter($methods = null)
    {
        return $this->getMock(ServiceSorter::class, $methods);
    }
}
