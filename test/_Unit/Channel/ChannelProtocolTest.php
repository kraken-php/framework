<?php

namespace Kraken\_Unit\Channel;

use Kraken\Channel\ChannelProtocol;
use Kraken\Test\TUnit;

class ChannelProtocolTest extends TUnit
{
    /**
     *
     */
    public function testApiConstructor_DoesNotThrowException()
    {
        $this->createChannelProtocol('type', 'pid', 'dest', 'origin', 'message', 'ex', 5);
    }

    /**
     *
     */
    public function testApiDestructor_DoesNotThrowException()
    {
        $protocol = $this->createChannelProtocol('type', 'pid', 'dest', 'origin', 'message', 'ex', 5);
        unset($protocol);
    }

    /**
     *
     */
    public function testApiSetType_SetsType_WhenTypeIsNotSet()
    {
        $protocol = $this->createChannelProtocol();

        $protocol->setType('type');
        $this->assertSame('type', $protocol->getType());
    }

    /**
     *
     */
    public function testApiSetType_SetsType_WhenTypeIsSetAndOverwriteFlagIsSet()
    {
        $protocol = $this->createChannelProtocol('typeOld');

        $this->assertSame('typeOld', $protocol->getType());
        $protocol->setType('type', true);
        $this->assertSame('type', $protocol->getType());
    }

    /**
     *
     */
    public function testApiSetType_DoesNothing_WhenTypeIsSetAndOverwriteFlagIsNotSet()
    {
        $protocol = $this->createChannelProtocol('typeOld');

        $this->assertSame('typeOld', $protocol->getType());
        $protocol->setType('type');
        $this->assertSame('typeOld', $protocol->getType());
    }

    /**
     *
     */
    public function testApiGetType_ReturnsType()
    {
        $protocol = $this->createChannelProtocol('type');

        $this->assertSame('type', $protocol->getType());
    }

    /**
     *
     */
    public function testApiSetPid_SetsPid_WhenPidIsNotSet()
    {
        $protocol = $this->createChannelProtocol();

        $protocol->setPid('pid');
        $this->assertSame('pid', $protocol->getPid());
    }

    /**
     *
     */
    public function testApiSetPid_SetsPid_WhenPidIsSetAndOverwriteFlagIsSet()
    {
        $protocol = $this->createChannelProtocol('', 'pidOld');

        $this->assertSame('pidOld', $protocol->getPid());
        $protocol->setPid('pid', true);
        $this->assertSame('pid', $protocol->getPid());
    }

    /**
     *
     */
    public function testApiSetPid_DoesNothing_WhenPidIsSetAndOverwriteFlagIsNotSet()
    {
        $protocol = $this->createChannelProtocol('', 'pidOld');

        $this->assertSame('pidOld', $protocol->getPid());
        $protocol->setPid('pid');
        $this->assertSame('pidOld', $protocol->getPid());
    }

    /**
     *
     */
    public function testApiGetPid_ReturnsPid()
    {
        $protocol = $this->createChannelProtocol('', 'pid');

        $this->assertSame('pid', $protocol->getPid());
    }

    /**
     *
     */
    public function testApiSetDestination_SetsDestination_WhenDestinationIsNotSet()
    {
        $protocol = $this->createChannelProtocol();

        $protocol->setDestination('destination');
        $this->assertSame('destination', $protocol->getDestination());
    }

    /**
     *
     */
    public function testApiSetDestination_SetsDestination_WhenDestinationIsSetAndOverwriteFlagIsSet()
    {
        $protocol = $this->createChannelProtocol('', '', 'destinationOld');

        $this->assertSame('destinationOld', $protocol->getDestination());
        $protocol->setDestination('destination', true);
        $this->assertSame('destination', $protocol->getDestination());
    }

    /**
     *
     */
    public function testApiSetDestination_DoesNothing_WhenDestinationIsSetAndOverwriteFlagIsNotSet()
    {
        $protocol = $this->createChannelProtocol('', '', 'destinationOld');

        $this->assertSame('destinationOld', $protocol->getDestination());
        $protocol->setDestination('destination');
        $this->assertSame('destinationOld', $protocol->getDestination());
    }

    /**
     *
     */
    public function testApiGetDestination_ReturnsDestination()
    {
        $protocol = $this->createChannelProtocol('', '', 'destination');

        $this->assertSame('destination', $protocol->getDestination());
    }


    /**
     *
     */
    public function testApiSetOrigin_SetsOrigin_WhenOriginIsNotSet()
    {
        $protocol = $this->createChannelProtocol();

        $protocol->setOrigin('origin');
        $this->assertSame('origin', $protocol->getOrigin());
    }

    /**
     *
     */
    public function testApiSetOrigin_SetsOrigin_WhenOriginIsSetAndOverwriteFlagIsSet()
    {
        $protocol = $this->createChannelProtocol('', '', '', 'originOld');

        $this->assertSame('originOld', $protocol->getOrigin());
        $protocol->setOrigin('origin', true);
        $this->assertSame('origin', $protocol->getOrigin());
    }

    /**
     *
     */
    public function testApiSetOrigin_DoesNothing_WhenOriginIsSetAndOverwriteFlagIsNotSet()
    {
        $protocol = $this->createChannelProtocol('', '', '', 'originOld');

        $this->assertSame('originOld', $protocol->getOrigin());
        $protocol->setOrigin('origin');
        $this->assertSame('originOld', $protocol->getOrigin());
    }

    /**
     *
     */
    public function testApiGetOrigin_ReturnsOrigin()
    {
        $protocol = $this->createChannelProtocol('', '', '', 'origin');

        $this->assertSame('origin', $protocol->getOrigin());
    }

    /**
     *
     */
    public function testApiSetMessage_SetsMessage_WhenMessageIsNotSet()
    {
        $protocol = $this->createChannelProtocol();

        $protocol->setMessage('message');
        $this->assertSame('message', $protocol->getMessage());
    }

    /**
     *
     */
    public function testApiSetMessage_SetsMessage_WhenMessageIsSetAndOverwriteFlagIsSet()
    {
        $protocol = $this->createChannelProtocol('', '', '', '', 'messageOld');

        $this->assertSame('messageOld', $protocol->getMessage());
        $protocol->setMessage('message', true);
        $this->assertSame('message', $protocol->getMessage());
    }

    /**
     *
     */
    public function testApiSetMessage_DoesNothing_WhenMessageIsSetAndOverwriteFlagIsNotSet()
    {
        $protocol = $this->createChannelProtocol('', '', '', '', 'messageOld');

        $this->assertSame('messageOld', $protocol->getMessage());
        $protocol->setMessage('message');
        $this->assertSame('messageOld', $protocol->getMessage());
    }

    /**
     *
     */
    public function testApiGetMessage_ReturnsMessage()
    {
        $protocol = $this->createChannelProtocol('', '', '', '', 'message');

        $this->assertSame('message', $protocol->getMessage());
    }

    /**
     *
     */
    public function testApiSetException_SetsException_WhenExceptionIsNotSet()
    {
        $protocol = $this->createChannelProtocol();

        $protocol->setException('exception');
        $this->assertSame('exception', $protocol->getException());
    }

    /**
     *
     */
    public function testApiSetException_SetsException_WhenExceptionIsSetAndOverwriteFlagIsSet()
    {
        $protocol = $this->createChannelProtocol('', '', '', '', '', 'exceptionOld');

        $this->assertSame('exceptionOld', $protocol->getException());
        $protocol->setException('exception', true);
        $this->assertSame('exception', $protocol->getException());
    }

    /**
     *
     */
    public function testApiSetException_DoesNothing_WhenExceptionIsSetAndOverwriteFlagIsNotSet()
    {
        $protocol = $this->createChannelProtocol('', '', '', '', '', 'exceptionOld');

        $this->assertSame('exceptionOld', $protocol->getException());
        $protocol->setException('exception');
        $this->assertSame('exceptionOld', $protocol->getException());
    }

    /**
     *
     */
    public function testApiGetException_ReturnsException()
    {
        $protocol = $this->createChannelProtocol('', '', '', '', '', 'exception');

        $this->assertSame('exception', $protocol->getException());
    }

    /**
     *
     */
    public function testApiSetTimestamp_SetsTimestamp_WhenTimestampIsNotSet()
    {
        $protocol = $this->createChannelProtocol();

        $protocol->setTimestamp(5);
        $this->assertSame(5, $protocol->getTimestamp());
    }

    /**
     *
     */
    public function testApiSetTimestamp_SetsTimestamp_WhenTimestampIsSetAndOverwriteFlagIsSet()
    {
        $protocol = $this->createChannelProtocol('', '', '', '', '', '', 5);

        $this->assertSame(5, $protocol->getTimestamp());
        $protocol->setTimestamp(10, true);
        $this->assertSame(10, $protocol->getTimestamp());
    }

    /**
     *
     */
    public function testApiSetTimestamp_DoesNothing_WhenTimestampIsSetAndOverwriteFlagIsNotSet()
    {
        $protocol = $this->createChannelProtocol('', '', '', '', '', '', 5);

        $this->assertSame(5, $protocol->getTimestamp());
        $protocol->setTimestamp(10);
        $this->assertSame(5, $protocol->getTimestamp());
    }

    /**
     *
     */
    public function testApiGetTimestamp_ReturnsTimestamp()
    {
        $protocol = $this->createChannelProtocol('', '', '', '', '', '', 5);

        $this->assertSame(5, $protocol->getTimestamp());
    }

    /**
     *
     */
    public function testApiSetAll_SetsAll_WhenOverwriteFlagIsSet()
    {
        $protocol = $this->createChannelProtocol();
        $this->assertSame([ '', '', '', '', '', '', 0 ], $protocol->getAll());

        $protocol->setAll([ 'type', 'pid', 'dest', 'origin', 'message', 'ex', 5 ], true);
        $this->assertSame([ 'type', 'pid', 'dest', 'origin', 'message', 'ex', 5 ], $protocol->getAll());
    }

    /**
     *
     */
    public function testApiGetAll_ReturnsAll()
    {
        $protocol = $this->createChannelProtocol('type', 'pid', 'dest', 'origin', 'message', 'ex', 5);

        $this->assertSame([ 'type', 'pid', 'dest', 'origin', 'message', 'ex', 5 ], $protocol->getAll());
    }

    /**
     * @param string $type
     * @param string $pid
     * @param string $destination
     * @param string $origin
     * @param string $message
     * @param string $exception
     * @param int $timestamp
     * @return ChannelProtocol
     */
    public function createChannelProtocol($type = '', $pid = '', $destination = '', $origin = '', $message = '', $exception = '', $timestamp = 0)
    {
        return new ChannelProtocol($type, $pid, $destination, $origin, $message, $exception, $timestamp);
    }
}
