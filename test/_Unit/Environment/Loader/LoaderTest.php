<?php

namespace Kraken\_Unit\Environment;

use Kraken\Runtime\RuntimeContextInterface;
use Kraken\Environment\Loader\Loader;
use Kraken\Test\TUnit;
use Kraken\Util\Invoker\Invoker;

class LoaderTest extends TUnit
{
    /**
     *
     */
    public function testApiConstructor_CreatesInstance()
    {
        $loader = $this->createLoader();

        $this->assertInstanceOf(Loader::class, $loader);
        $this->assertInstanceOf(\Dotenv\Loader::class, $loader);
    }

    /**
     *
     */
    public function testApiDestructor_DoesNotThrowException()
    {
        $loader = $this->createLoader();
        unset($loader);
    }

    /**
     *
     */
    public function testApiSetEnvironmentVariable_SetsEnvironmentVariableAndIniOption()
    {
        $loader = $this->createLoader();

        $this->assertNotSame('2048M', $loader->getEnvironmentVariable('INI_MEMORY_LIMIT'));
        $this->assertNotSame('2048M', ini_get('memory_limit'));

        $loader->setEnvironmentVariable('INI_MEMORY_LIMIT', '2048M');

        $this->assertSame('2048M', $loader->getEnvironmentVariable('INI_MEMORY_LIMIT'));
        $this->assertSame('2048M', ini_get('memory_limit'));
    }

    /**
     *
     */
    public function testApiClearEnvironmentVariable_ClearsEnvironmentVariableAndRestoresInitOption()
    {
        $loader = $this->createLoader();

        $loader->setEnvironmentVariable('INI_MEMORY_LIMIT', '2048M');

        $this->assertSame('2048M', $loader->getEnvironmentVariable('INI_MEMORY_LIMIT'));
        $this->assertSame('2048M', ini_get('memory_limit'));

        $loader->clearEnvironmentVariable('INI_MEMORY_LIMIT');

        $this->assertNotSame('2048M', $loader->getEnvironmentVariable('INI_MEMORY_LIMIT'));
        $this->assertNotSame('2048M', ini_get('memory_limit'));
    }
    
    /**
     *
     */
    public function testProtectedApiCreateInvoker_CreatesInvoker()
    {
        $loader = $this->createLoader();

        $this->assertInstanceOf(Invoker::class, $this->callProtectedMethod($loader, 'createInvoker'));
    }

    /**
     * @return Loader
     */
    public function createLoader()
    {
        $context = $this->getMock(RuntimeContextInterface::class);

        return new Loader($context, '');
    }
}
