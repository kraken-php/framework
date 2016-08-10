<?php

namespace Kraken\_Unit\Supervisor;

use Kraken\_Unit\Supervisor\_Aware\SupervisorAwareObject;
use Kraken\Supervisor\Supervisor;
use Kraken\Supervisor\SupervisorAwareInterface;
use Kraken\Test\TUnit;

class SupervisorAwareObjectTest extends TUnit
{
    /**
     *
     */
    public function testApiSetSupervisor_SetsSupervisor()
    {
        $object = $this->createSupervisorAwareObject();
        $supervisor = $this->createSupervisor();

        $object->setSupervisor($supervisor);

        $this->assertSame($supervisor, $this->getProtectedProperty($object, 'supervisor'));
    }

    /**
     *
     */
    public function testApiGetSupervisor_ReturnsSupervisor()
    {
        $object = $this->createSupervisorAwareObject();
        $supervisor = $this->createSupervisor();

        $object->setSupervisor($supervisor);

        $this->assertSame($supervisor, $object->getSupervisor());
    }

    /**
     * @return Supervisor|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createSupervisor()
    {
        return $this->getMock(Supervisor::class, [], [], '', false);
    }

    /**
     * @return SupervisorAwareInterface
     */
    public function createSupervisorAwareObject()
    {
        return new SupervisorAwareObject();
    }
}
