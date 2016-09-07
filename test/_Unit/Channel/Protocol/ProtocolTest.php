<?php

namespace Kraken\_Unit\Channel\Protocol;

use Kraken\Channel\Protocol\Protocol;
use Kraken\Test\TUnit;

class ProtocolTest extends TUnit
{
    /**
     *
     */
    public function testApiConstructor_DoesNotThrowException()
    {
        $this->createProtocol('type', 'pid', 'dest', 'origin', 'message', 'ex', 5);
    }

    /**
     *
     */
    public function testApiDestructor_DoesNotThrowException()
    {
        $protocol = $this->createProtocol('type', 'pid', 'dest', 'origin', 'message', 'ex', 5);
        unset($protocol);
    }

    /**
     *
     */
    public function testApiSetType_SetsType_WhenTypeIsNotSet()
    {
        $protocol = $this->createProtocol();

        $protocol->setType('type');
        $this->assertSame('type', $protocol->getType());
    }

    /**
     *
     */
    public function testApiSetType_SetsType_WhenTypeIsSetAndOverwriteFlagIsSet()
    {
        $protocol = $this->createProtocol('typeOld');

        $this->assertSame('typeOld', $protocol->getType());
        $protocol->setType('type', true);
        $this->assertSame('type', $protocol->getType());
    }

    /**
     *
     */
    public function testApiSetType_DoesNothing_WhenTypeIsSetAndOverwriteFlagIsNotSet()
    {
        $protocol = $this->createProtocol('typeOld');

        $this->assertSame('typeOld', $protocol->getType());
        $protocol->setType('type');
        $this->assertSame('typeOld', $protocol->getType());
    }

    /**
     *
     */
    public function testApiGetType_ReturnsType()
    {
        $protocol = $this->createProtocol('type');

        $this->assertSame('type', $protocol->getType());
    }

    /**
     *
     */
    public function testApiSetPid_SetsPid_WhenPidIsNotSet()
    {
        $protocol = $this->createProtocol();

        $protocol->setPid('pid');
        $this->assertSame('pid', $protocol->getPid());
    }

    /**
     *
     */
    public function testApiSetPid_SetsPid_WhenPidIsSetAndOverwriteFlagIsSet()
    {
        $protocol = $this->createProtocol('', 'pidOld');

        $this->assertSame('pidOld', $protocol->getPid());
        $protocol->setPid('pid', true);
        $this->assertSame('pid', $protocol->getPid());
    }

    /**
     *
     */
    public function testApiSetPid_DoesNothing_WhenPidIsSetAndOverwriteFlagIsNotSet()
    {
        $protocol = $this->createProtocol('', 'pidOld');

        $this->assertSame('pidOld', $protocol->getPid());
        $protocol->setPid('pid');
        $this->assertSame('pidOld', $protocol->getPid());
    }

    /**
     *
     */
    public function testApiGetPid_ReturnsPid()
    {
        $protocol = $this->createProtocol('', 'pid');

        $this->assertSame('pid', $protocol->getPid());
    }

    /**
     *
     */
    public function testApiSetDestination_SetsDestination_WhenDestinationIsNotSet()
    {
        $protocol = $this->createProtocol();

        $protocol->setDestination('destination');
        $this->assertSame('destination', $protocol->getDestination());
    }

    /**
     *
     */
    public function testApiSetDestination_SetsDestination_WhenDestinationIsSetAndOverwriteFlagIsSet()
    {
        $protocol = $this->createProtocol('', '', 'destinationOld');

        $this->assertSame('destinationOld', $protocol->getDestination());
        $protocol->setDestination('destination', true);
        $this->assertSame('destination', $protocol->getDestination());
    }

    /**
     *
     */
    public function testApiSetDestination_DoesNothing_WhenDestinationIsSetAndOverwriteFlagIsNotSet()
    {
        $protocol = $this->createProtocol('', '', 'destinationOld');

        $this->assertSame('destinationOld', $protocol->getDestination());
        $protocol->setDestination('destination');
        $this->assertSame('destinationOld', $protocol->getDestination());
    }

    /**
     *
     */
    public function testApiGetDestination_ReturnsDestination()
    {
        $protocol = $this->createProtocol('', '', 'destination');

        $this->assertSame('destination', $protocol->getDestination());
    }


    /**
     *
     */
    public function testApiSetOrigin_SetsOrigin_WhenOriginIsNotSet()
    {
        $protocol = $this->createProtocol();

        $protocol->setOrigin('origin');
        $this->assertSame('origin', $protocol->getOrigin());
    }

    /**
     *
     */
    public function testApiSetOrigin_SetsOrigin_WhenOriginIsSetAndOverwriteFlagIsSet()
    {
        $protocol = $this->createProtocol('', '', '', 'originOld');

        $this->assertSame('originOld', $protocol->getOrigin());
        $protocol->setOrigin('origin', true);
        $this->assertSame('origin', $protocol->getOrigin());
    }

    /**
     *
     */
    public function testApiSetOrigin_DoesNothing_WhenOriginIsSetAndOverwriteFlagIsNotSet()
    {
        $protocol = $this->createProtocol('', '', '', 'originOld');

        $this->assertSame('originOld', $protocol->getOrigin());
        $protocol->setOrigin('origin');
        $this->assertSame('originOld', $protocol->getOrigin());
    }

    /**
     *
     */
    public function testApiGetOrigin_ReturnsOrigin()
    {
        $protocol = $this->createProtocol('', '', '', 'origin');

        $this->assertSame('origin', $protocol->getOrigin());
    }

    /**
     *
     */
    public function testApiSetMessage_SetsMessage_WhenMessageIsNotSet()
    {
        $protocol = $this->createProtocol();

        $protocol->setMessage('message');
        $this->assertSame('message', $protocol->getMessage());
    }

    /**
     *
     */
    public function testApiSetMessage_SetsMessage_WhenMessageIsSetAndOverwriteFlagIsSet()
    {
        $protocol = $this->createProtocol('', '', '', '', 'messageOld');

        $this->assertSame('messageOld', $protocol->getMessage());
        $protocol->setMessage('message', true);
        $this->assertSame('message', $protocol->getMessage());
    }

    /**
     *
     */
    public function testApiSetMessage_DoesNothing_WhenMessageIsSetAndOverwriteFlagIsNotSet()
    {
        $protocol = $this->createProtocol('', '', '', '', 'messageOld');

        $this->assertSame('messageOld', $protocol->getMessage());
        $protocol->setMessage('message');
        $this->assertSame('messageOld', $protocol->getMessage());
    }

    /**
     *
     */
    public function testApiGetMessage_ReturnsMessage()
    {
        $protocol = $this->createProtocol('', '', '', '', 'message');

        $this->assertSame('message', $protocol->getMessage());
    }

    /**
     *
     */
    public function testApiSetException_SetsException_WhenExceptionIsNotSet()
    {
        $protocol = $this->createProtocol();

        $protocol->setException('exception');
        $this->assertSame('exception', $protocol->getException());
    }

    /**
     *
     */
    public function testApiSetException_SetsException_WhenExceptionIsSetAndOverwriteFlagIsSet()
    {
        $protocol = $this->createProtocol('', '', '', '', '', 'exceptionOld');

        $this->assertSame('exceptionOld', $protocol->getException());
        $protocol->setException('exception', true);
        $this->assertSame('exception', $protocol->getException());
    }

    /**
     *
     */
    public function testApiSetException_DoesNothing_WhenExceptionIsSetAndOverwriteFlagIsNotSet()
    {
        $protocol = $this->createProtocol('', '', '', '', '', 'exceptionOld');

        $this->assertSame('exceptionOld', $protocol->getException());
        $protocol->setException('exception');
        $this->assertSame('exceptionOld', $protocol->getException());
    }

    /**
     *
     */
    public function testApiGetException_ReturnsException()
    {
        $protocol = $this->createProtocol('', '', '', '', '', 'exception');

        $this->assertSame('exception', $protocol->getException());
    }

    /**
     *
     */
    public function testApiSetTimestamp_SetsTimestamp_WhenTimestampIsNotSet()
    {
        $protocol = $this->createProtocol();

        $protocol->setTimestamp(5);
        $this->assertSame(5, $protocol->getTimestamp());
    }

    /**
     *
     */
    public function testApiSetTimestamp_SetsTimestamp_WhenTimestampIsSetAndOverwriteFlagIsSet()
    {
        $protocol = $this->createProtocol('', '', '', '', '', '', 5);

        $this->assertSame(5, $protocol->getTimestamp());
        $protocol->setTimestamp(10, true);
        $this->assertSame(10, $protocol->getTimestamp());
    }

    /**
     *
     */
    public function testApiSetTimestamp_DoesNothing_WhenTimestampIsSetAndOverwriteFlagIsNotSet()
    {
        $protocol = $this->createProtocol('', '', '', '', '', '', 5);

        $this->assertSame(5, $protocol->getTimestamp());
        $protocol->setTimestamp(10);
        $this->assertSame(5, $protocol->getTimestamp());
    }

    /**
     *
     */
    public function testApiGetTimestamp_ReturnsTimestamp()
    {
        $protocol = $this->createProtocol('', '', '', '', '', '', 5);

        $this->assertSame(5, $protocol->getTimestamp());
    }

    /**
     *
     */
    public function testApiSetAll_SetsAll_WhenOverwriteFlagIsSet()
    {
        $protocol = $this->createProtocol();
        $this->assertSame([ '', '', '', '', '', '', 0 ], $protocol->getAll());

        $protocol->setAll([ 'type', 'pid', 'dest', 'origin', 'message', 'ex', 5 ], true);
        $this->assertSame([ 'type', 'pid', 'dest', 'origin', 'message', 'ex', 5 ], $protocol->getAll());
    }

    /**
     *
     */
    public function testApiGetAll_ReturnsAll()
    {
        $protocol = $this->createProtocol('type', 'pid', 'dest', 'origin', 'message', 'ex', 5);

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
     * @return Protocol
     */
    public function createProtocol($type = '', $pid = '', $destination = '', $origin = '', $message = '', $exception = '', $timestamp = 0)
    {
        return new Protocol($type, $pid, $destination, $origin, $message, $exception, $timestamp);
    }
}
