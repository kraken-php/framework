<?php

namespace Kraken\_Unit\Channel;

use Kraken\_Unit\Channel\_Mock\ChannelCompositeAwareObject;
use Kraken\Channel\ChannelComposite;
use Kraken\Test\TUnit;

class ChannelCompositeAwareObjectTest extends TUnit
{
    /**
     *
     */
    public function testApiSetChannel_SetsChannel()
    {
        $object  = $this->createChannelCompositeAwareObject();
        $channel = $this->createChannelComposite();

        $this->assertSame(null, $this->getProtectedProperty($object, 'channel'));
        $object->setChannel($channel);
        $this->assertSame($channel, $this->getProtectedProperty($object, 'channel'));
    }

    /**
     *
     */
    public function testApiSetChannel_RemovesChannel_WhenNullPassed()
    {
        $object  = $this->createChannelCompositeAwareObject();
        $channel = $this->createChannelComposite();

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
        $object  = $this->createChannelCompositeAwareObject();
        $this->assertSame(null, $object->getChannel());
    }

    /**
     *
     */
    public function testApiGetChannel_ReturnsChannel_WhenChannelIsSet()
    {
        $object  = $this->createChannelCompositeAwareObject();
        $channel = $this->createChannelComposite();

        $object->setChannel($channel);
        $this->assertSame($channel, $object->getChannel());
    }

    /**
     * @return ChannelComposite
     */
    public function createChannelComposite()
    {
        return $this->getMock(ChannelComposite::class, [], [], '', false);
    }

    /**
     * @return ChannelCompositeAwareObject
     */
    public function createChannelCompositeAwareObject()
    {
        return new ChannelCompositeAwareObject();
    }
}
