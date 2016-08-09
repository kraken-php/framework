<?php

namespace Kraken\_Unit\Core\Service;

use Kraken\_Unit\Core\_Provider\AProvider;
use Kraken\_Unit\Core\_Provider\BProvider;
use Kraken\_Unit\Core\_Provider\CProvider;
use Kraken\_Unit\Core\_Provider\DProvider;
use Kraken\_Unit\Core\_Provider\EProvider;
use Kraken\_Unit\Core\_Provider\FProvider;
use Kraken\_Unit\Core\_Provider\GProvider;
use Kraken\Core\Service\ServiceSorter;
use Kraken\Test\TUnit;
use Kraken\Throwable\Exception\Logic\Resource\ResourceUndefinedException;
use Kraken\Throwable\Exception\Runtime\OverflowException;

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
