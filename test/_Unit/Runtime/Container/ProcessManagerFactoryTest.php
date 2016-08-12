<?php

namespace Kraken\_Unit\Runtime\Container;

use Kraken\Runtime\Container\Manager\ProcessManagerBase;
use Kraken\Runtime\Container\Manager\ProcessManagerNull;
use Kraken\Runtime\Container\Manager\ProcessManagerRemote;
use Kraken\Runtime\Container\ProcessManagerFactory;
use Kraken\Runtime\Container\ProcessManagerFactoryInterface;
use Kraken\Test\TUnit;

class ProcessManagerFactoryTest extends TUnit
{
    /**
     *
     */
    public function testApiConstructor_CreatesInstance()
    {
        $factory = $this->createProcessManagerFactory();

        $this->assertInstanceOf(ProcessManagerFactory::class, $factory);
        $this->assertInstanceOf(ProcessManagerFactoryInterface::class, $factory);
    }

    /**
     *
     */
    public function testCaseProcessManagerFactory_HasProperDefinitions()
    {
        $factory = $this->createProcessManagerFactory();
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
            ProcessManagerBase::class,
            ProcessManagerRemote::class,
            ProcessManagerNull::class
        ];
    }

    /**
     * @return ProcessManagerFactory
     */
    public function createProcessManagerFactory()
    {
        return new ProcessManagerFactory();
    }
}
