<?php

namespace Kraken\_Unit\Channel;

use Kraken\_Unit\Channel\_Mock\ChannelAwareObject;
use Kraken\Channel\Channel;
use Kraken\Test\TUnit;

class ChannelAwareObjectTest extends TUnit
{
    /**
     *
     */
    public function testApiSetChannel_SetsChannel()
    {
        $object  = $this->createChannelAwareObject();
        $channel = $this->createChannel();

        $this->assertSame(null, $this->getProtectedProperty($object, 'channel'));
        $object->setChannel($channel);
        $this->assertSame($channel, $this->getProtectedProperty($object, 'channel'));
    }

    /**
     *
     */
    public function testApiSetChannel_RemovesChannel_WhenNullPassed()
    {
        $object  = $this->createChannelAwareObject();
        $channel = $this->createChannel();

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
        $object  = $this->createChannelAwareObject();
        $this->assertSame(null, $object->getChannel());
    }

    /**
     *
     */
    public function testApiGetChannel_ReturnsChannel_WhenChannelIsSet()
    {
        $object  = $this->createChannelAwareObject();
        $channel = $this->createChannel();

        $object->setChannel($channel);
        $this->assertSame($channel, $object->getChannel());
    }

    /**
     * @return Channel
     */
    public function createChannel()
    {
        return $this->getMock(Channel::class, [], [], '', false);
    }

    /**
     * @return ChannelAwareObject
     */
    public function createChannelAwareObject()
    {
        return new ChannelAwareObject();
    }
}
