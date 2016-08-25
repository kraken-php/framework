<?php

namespace Kraken\_Unit\Network;

use Kraken\Test\TUnit;
use Kraken\Network\NetworkMessage;
use Kraken\Network\NetworkMessageInterface;

class IoMessageTest extends TUnit
{
    /**
     *
     */
    public function testApiConstructor_CreatesInstance()
    {
        $message = $this->createIoMessage('');

        $this->assertInstanceOf(NetworkMessage::class, $message);
        $this->assertInstanceOf(NetworkMessageInterface::class, $message);
    }

    /**
     *
     */
    public function testApiDestructor_DoesNotThrowException()
    {
        $message = $this->createIoMessage('');
        unset($message);
    }

    /**
     *
     */
    public function testApiRead_ReturnsStoredMessage()
    {
        $message = $this->createIoMessage($text = 'text');
        $this->assertSame($text, $message->read());
    }

    /**
     * @return NetworkMessage
     */
    public function createIoMessage($message)
    {
        return new NetworkMessage($message);
    }
}
