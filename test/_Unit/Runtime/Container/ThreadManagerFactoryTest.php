<?php

namespace Kraken\_Unit\Runtime\Container;

use Kraken\Runtime\Container\Manager\ThreadManagerBase;
use Kraken\Runtime\Container\Manager\ThreadManagerNull;
use Kraken\Runtime\Container\Manager\ThreadManagerRemote;
use Kraken\Runtime\Container\ThreadManagerFactory;
use Kraken\Runtime\Container\ThreadManagerFactoryInterface;
use Kraken\Test\TUnit;

class ThreadManagerFactoryTest extends TUnit
{
    /**
     *
     */
    public function testApiConstructor_CreatesInstance()
    {
        $factory = $this->createThreadManagerFactory();

        $this->assertInstanceOf(ThreadManagerFactory::class, $factory);
        $this->assertInstanceOf(ThreadManagerFactoryInterface::class, $factory);
    }

    /**
     *
     */
    public function testCaseThreadManagerFactory_HasProperDefinitions()
    {
        $factory = $this->createThreadManagerFactory();
        $expected = $this->getExpectedDefinitions();

        foreach ($expected as $expectation)
        {
            $this->assertTrue($factory->hasDefinition($expectation));
        }
    }

    /**
     * @return string[]
     */
    public function getExpectedDefinitions()
    {
        return [
            ThreadManagerBase::class,
            ThreadManagerRemote::class,
            ThreadManagerNull::class
        ];
    }

    /**
     * @return ThreadManagerFactory
     */
    public function createThreadManagerFactory()
    {
        return new ThreadManagerFactory();
    }
}
