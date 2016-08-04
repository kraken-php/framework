<?php

namespace Kraken\_Unit\Container\Object;

use Kraken\_Unit\Container\_Asset\Invokable;
use Kraken\Container\Object\InvokableObject;
use Kraken\Test\TUnit;

class InvokableObjectTest extends TUnit
{
    /**
     *
     */
    public function testApiConstructor_DoesNotThrowException()
    {
        $this->createInvokableObject(new Invokable);
    }

    /**
     *
     */
    public function testApiDestructor_DoesNotThrowException()
    {
        $object = $this->createInvokableObject(new Invokable);
        unset($object);
    }

    /**
     *
     */
    public function testApiGetObject_ReturnsObject()
    {
        $object = $this->createInvokableObject($invokable = new Invokable);

        $this->assertSame($invokable, $object->getObject());
    }

    /**
     * @param object $object
     * @return InvokableObject
     */
    public function createInvokableObject($object)
    {
        return new InvokableObject($object);
    }
}
