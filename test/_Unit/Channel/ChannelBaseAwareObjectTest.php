<?php

namespace Kraken\_Unit\Channel;

use Kraken\_Unit\Channel\_Mock\ChannelBaseAwareObject;
use Kraken\Channel\ChannelBase;
use Kraken\Test\TUnit;

class ChannelBaseAwareObjectTest extends TUnit
{
    /**
     *
     */
    public function testApiSetChannel_SetsChannel()
    {
        $object  = $this->createChannelBaseAwareObject();
        $channel = $this->createChannelBase();

        $this->assertSame(null, $this->getProtectedProperty($object, 'channel'));
        $object->setChannel($channel);
        $this->assertSame($channel, $this->getProtectedProperty($object, 'channel'));
    }

    /**
     *
     */
    public function testApiSetChannel_RemovesChannel_WhenNullPassed()
    {
        $object  = $this->createChannelBaseAwareObject();
        $channel = $this->createChannelBase();

        $object->setChannel($channel);
        $this->assertSame($channel, $this->getProtectedProperty($object, 'channel'));
        $object->setChannel(null);
        $this->assertSame(null, $this->getProtectedProperty($object, 'channel'));
    }

    /**
     *
     */
    public function testApiGetChannel_ReturnsNull_WhenChannelIsNotSet()
    {
        $object  = $this->createChannelBaseAwareObject();
        $this->assertSame(null, $object->getChannel());
    }

    /**
     *
     */
    public function testApiGetChannel_ReturnsChannel_WhenChannelIsSet()
    {
        $object  = $this->createChannelBaseAwareObject();
        $channel = $this->createChannelBase();

        $object->setChannel($channel);
        $this->assertSame($channel, $object->getChannel());
    }

    /**
     * @return ChannelBase
     */
    public function createChannelBase()
    {
        return $this->getMock(ChannelBase::class, [], [], '', false);
    }

    /**
     * @return ChannelBaseAwareObject
     */
    public function createChannelBaseAwareObject()
    {
        return new ChannelBaseAwareObject();
    }
}
