<?php

namespace Kraken\_Unit\Runtime;


use Kraken\_Unit\Runtime\_Aware\RuntimeManagerAwareObject;
use Kraken\Runtime\RuntimeManager;
use Kraken\Runtime\RuntimeManagerAwareInterface;
use Kraken\Test\TUnit;

class RuntimeManagerAwareObjectTest extends TUnit
{
    /**
     *
     */
    public function testApiSetRuntimeManager_SetsRuntimeManager()
    {
        $object = $this->createRuntimeManagerAwareObject();
        $runtimeManager = $this->createRuntimeManager();

        $object->setRuntimeManager($runtimeManager);

        $this->assertSame($runtimeManager, $this->getProtectedProperty($object, 'runtimeManager'));
    }

    /**
     *
     */
    public function testApiGetRuntimeManager_ReturnsRuntimeManager()
    {
        $object = $this->createRuntimeManagerAwareObject();
        $runtimeManager = $this->createRuntimeManager();

        $object->setRuntimeManager($runtimeManager);

        $this->assertSame($runtimeManager, $object->getRuntimeManager());
    }

    /**
     * @return RuntimeManager|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createRuntimeManager()
    {
        return $this->getMock(RuntimeManager::class, [], [], '', false);
    }

    /**
     * @return RuntimeManagerAwareInterface
     */
    public function createRuntimeManagerAwareObject()
    {
        return new RuntimeManagerAwareObject();
    }
}
