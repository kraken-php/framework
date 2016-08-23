<?php

namespace Kraken\_Unit\Transfer;

use Kraken\Test\TUnit;
use Kraken\Transfer\TransferMessage;
use Kraken\Transfer\TransferMessageInterface;

class IoMessageTest extends TUnit
{
    /**
     *
     */
    public function testApiConstructor_CreatesInstance()
    {
        $message = $this->createIoMessage('');

        $this->assertInstanceOf(TransferMessage::class, $message);
        $this->assertInstanceOf(TransferMessageInterface::class, $message);
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
     * @return TransferMessage
     */
    public function createIoMessage($message)
    {
        return new TransferMessage($message);
    }
}
