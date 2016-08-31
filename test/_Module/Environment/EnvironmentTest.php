<?php

namespace Kraken\_Module\Environment;

use Kraken\Core\CoreInputContextInterface;
use Kraken\Environment\Environment;
use Kraken\Test\TModule;

class EnvironmentTest extends TModule
{
    /**
     * @var string
     */
    private $path;

    /**
     *
     */
    public function setUp()
    {
        parent::setUp();

        $this->path = __DIR__ . '/_Dir/.env';
    }

    /**
     *
     */
    public function tearDown()
    {
        unset($this->path);

        parent::tearDown();
    }

    /**
     *
     */
    public function testCaseEnvironment_ReadsAndParsesEnvFileCorrectly()
    {
        $context = $this->getMock(CoreInputContextInterface::class);

        $env = new Environment($context, $this->path);

        $this->assertSame('VALUE1', $env->getEnv('KEY1'));
        $this->assertSame('VALUE2', $env->getEnv('KEY2'));
        $this->assertSame('VALUE1/TEST', $env->getEnv('KEY3'));
        $this->assertSame('2048M', $env->getEnv('INI_MEMORY_LIMIT'));

        $this->assertSame('2048M', ini_get('memory_limit'));
    }
}
