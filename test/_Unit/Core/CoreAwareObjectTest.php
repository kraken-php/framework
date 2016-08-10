<?php

namespace Kraken\_Unit\Core;

use Kraken\_Unit\Core\_Mock\CoreAwareObjectMock;
use Kraken\Core\Core;
use Kraken\Core\CoreAwareInterface;
use Kraken\Test\TUnit;

class CoreAwareObjectTest extends TUnit
{
    /**
     *
     */
    public function testApiSetCore_SetsCore()
    {
        $object = $this->createCoreAwareObject();
        $core = $this->createCore();

        $object->setCore($core);

        $this->assertSame($core, $this->getProtectedProperty($object, 'core'));
    }

    /**
     *
     */
    public function testApiGetCore_ReturnsCore()
    {
        $object = $this->createCoreAwareObject();
        $core = $this->createCore();

        $object->setCore($core);

        $this->assertSame($core, $object->getCore());
    }

    /**
     * @return Core|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createCore()
    {
        return $this->getMock(Core::class, [], [], '', false);
    }

    /**
     * @return CoreAwareInterface
     */
    public function createCoreAwareObject()
    {
        return new CoreAwareObjectMock();
    }
}
