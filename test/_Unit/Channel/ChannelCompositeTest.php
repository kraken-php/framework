<?php

namespace Kraken\_Unit\Channel;

use Kraken\Channel\Encoder\EncoderInterface;
use Kraken\Channel\Protocol\Protocol;
use Kraken\Channel\Protocol\ProtocolInterface;
use Kraken\Channel\Record\RequestRecord;
use Kraken\Channel\Router\RouterComposite;
use Kraken\Channel\Router\RouterCompositeInterface;
use Kraken\Channel\Channel;
use Kraken\Channel\ChannelInterface;
use Kraken\Channel\ChannelComposite;
use Kraken\Channel\ChannelCompositeInterface;
use Kraken\Channel\ChannelModelInterface;
use Kraken\Event\EventListener;
use Kraken\Loop\Loop;
use Kraken\Loop\LoopInterface;
use Dazzle\Throwable\Exception\Logic\ResourceOccupiedException;
use Dazzle\Throwable\Exception\Logic\ResourceUndefinedException;
use Kraken\Test\TUnit;

class ChannelCompositeTest extends TUnit
{
    /**
     * @var ChannelComposite|\PHPUnit_Framework_MockObject_MockObject
     */
    private $channel;

    /**
     *
     */
    public function testApiConstructor_CreatesInstance()
    {
        $channel = $this->createChannel();

        $this->assertInstanceOf(ChannelComposite::class, $channel);
        $this->assertInstanceOf(ChannelCompositeInterface::class, $channel);
        $this->assertInstanceOf(ChannelInterface::class, $channel);
    }

    /**
     *
     */
    public function testApiDestructor_DoesNotThrowException()
    {
        $channel = $this->createChannel();
        unset($channel);
    }

    /**
     *
     */
    public function testApiExistsBus_ReturnsFalse_WhenBusDoesNotExist()
    {
        $channel = $this->createChannel();
        $this->assertFalse($channel->existsBus('bus'));
    }

    /**
     *
     */
    public function testApiExistsBus_ReturnsTrue_WhenBusDoesExist()
    {
        $bus = $this->createBus();

        $channel = $this->createChannel([ 'bus' => $bus ]);
        $this->assertTrue($channel->existsBus('bus'));
    }

    /**
     *
     */
    public function testApiGetBus_ThrowsException_WhenBusDoesNotExist()
    {
        $channel = $this->createChannel();

        $this->setExpectedException(ResourceUndefinedException::class);
        $channel->getBus('bus');
    }

    /**
     *
     */
    public function testApiGetBus_ReturnsBus_WhenBusDoesExist()
    {
        $bus = $this->createBus();
        $channel = $this->createChannel([ 'bus' => $bus ]);

        $this->assertSame($bus, $channel->getBus('bus'));
    }

    /**
     *
     */
    public function testApiSetBus_ThrowsException_WhenBusDoesExist()
    {
        $bus = $this->createBus();
        $channel = $this->createChannel([ 'bus' => $bus ]);

        $this->setExpectedException(ResourceOccupiedException::class);
        $channel->setBus('bus', $this->createBus());
    }

    /**
     *
     */
    public function testApiSetBus_SetsBus_WhenBusDoesNotExist()
    {
        $bus = $this->createBus();

        $channel = $this->createChannel();
        $channel->setBus('bus', $bus);

        $buses  = $this->getProtectedProperty($channel, 'buses');
        $events = $this->getProtectedProperty($channel, 'events');

        $this->assertSame($bus, $buses['bus']);
        $this->assertCount(3, $events['bus']);
    }

    /**
     *
     */
    public function testApiRemoveBus_DoesNothing_WhenBusDoesNotExist()
    {
        $channel = $this->createChannel();
        $channel->removeBus('bus');
    }

    /**
     *
     */
    public function testApiRemoveBus_RemovesBus_WhenBusDoesExist()
    {
        $bus = $this->createBus();
        $channel = $this->createChannel([ 'bus' => $bus ]);

        $this->assertTrue($channel->existsBus('bus'));
        $channel->removeBus('bus');
        $this->assertFalse($channel->existsBus('bus'));
    }

    /**
     *
     */
    public function testApiGetBuses_ReturnsEmptyArray_WhenNoBusIsSet()
    {
        $channel = $this->createChannel();
        $this->assertSame([], $channel->getBuses());
    }

    /**
     *
     */
    public function testApiGetBuses_ReturnsArrayOfBuses_WhenAtLeastOneBusIsSet()
    {
        $bus1 = $this->createBus();
        $bus2 = $this->createBus();

        $buses = [ 'bus1' => $bus1, 'bus2' => $bus2 ];
        $channel = $this->createChannel($buses);

        $this->assertSame($buses, $channel->getBuses());
    }

    /**
     *
     */
    public function testApiName_ReturnsName()
    {
        $channel = $this->createChannel();
        $this->assertSame('name', $channel->getName());
    }

    /**
     *
     */
    public function testApiModel_ReturnsNull()
    {
        $channel = $this->createChannel();
        $this->assertSame(null, $channel->getModel());
    }

    /**
     *
     */
    public function testApiRouter_ReturnsRouter()
    {
        $channel = $this->createChannel();
        $router  = $this->createRouter();
        $this->assertSame($router, $channel->getRouter());
    }

    /**
     *
     */
    public function testApiInput_ReturnsRouterInputBus()
    {
        $channel = $this->createChannel();

        $input  = $this->createBus();
        $router = $this->createRouter([ 'getBus' ]);
        $router
            ->expects($this->once())
            ->method('getBus')
            ->with('input')
            ->will($this->returnValue($input));

        $this->assertSame($input, $channel->getInput());
    }

    /**
     *
     */
    public function testApiOutput_ReturnsRouterOutputBus()
    {
        $channel = $this->createChannel();

        $output = $this->createBus();
        $router = $this->createRouter([ 'getBus' ]);
        $router
            ->expects($this->once())
            ->method('getBus')
            ->with('output')
            ->will($this->returnValue($output));

        $this->assertSame($output, $channel->getOutput());
    }

    /**
     *
     */
    public function testApiCreateProtocol_CreatesProtocol_WhenNullPassed()
    {
        $channel = $this->createChannel();

        $result = $channel->createProtocol();

        $this->assertInstanceOf(ProtocolInterface::class, $result);
        $this->assertSame('', $result->getMessage());
    }

    /**
     *
     */
    public function testApiCreateProtocol_CreatesProtocol_WhenStringPassed()
    {
        $channel = $this->createChannel();

        $result = $channel->createProtocol('text');

        $this->assertInstanceOf(ProtocolInterface::class, $result);
        $this->assertSame('text', $result->getMessage());
    }

    /**
     * @dataProvider eventsProvider
     */
    public function testCaseAllOnMethods_RegisterHandlers($event)
    {
        $handler  = $this->getMock(EventListener::class, [], [], '', false);
        $callable = function() {};

        $method = 'on' . ucfirst($event);

        $channel = $this->createChannel([], [ 'on' ]);
        $channel
            ->expects($this->once())
            ->method('on')
            ->with($event, $this->isType('callable'))
            ->will($this->returnValue($handler));

        $this->assertSame($handler, call_user_func_array([ $channel, $method ], [ $callable ]));
    }

    /**
     *
     */
    public function testApiStart_CallsStartOnAllBuses()
    {
        $bus1 = $this->createBus('bus1', [ 'start' ]);
        $bus1
            ->expects($this->once())
            ->method('start');

        $bus2 = $this->createBus('bus2', [ 'start' ]);
        $bus2
            ->expects($this->once())
            ->method('start');

        $channel = $this->createChannel([ 'bus1' => $bus1, 'bus2' => $bus2 ]);
        $channel->start();
    }

    /**
     *
     */
    public function testApiStop_CallsStopOnAllBuses()
    {
        $bus1 = $this->createBus('bus1', [ 'stop' ]);
        $bus1
            ->expects($this->once())
            ->method('stop');

        $bus2 = $this->createBus('bus2', [ 'stop' ]);
        $bus2
            ->expects($this->once())
            ->method('stop');

        $channel = $this->createChannel([ 'bus1' => $bus1, 'bus2' => $bus2 ]);
        $channel->stop();
    }

    /**
     *
     */
    public function testApiSend_SendsAsync_WhenCallbacksAreNull()
    {
        $bool = true;
        $name = 'name'; $message = 'message'; $flags = 'flags';

        $channel = $this->createChannel([], [ 'sendAsync' ]);
        $channel
            ->expects($this->once())
            ->method('sendAsync')
            ->with($name, $message, $flags)
            ->will($this->returnValue($bool));

        $this->assertSame($bool, $channel->send($name, $message, $flags));
    }

    /**
     *
     */
    public function testApiSend_SendsRequest_WhenAtLeastOneOfCallbacksIsNotNull()
    {
        $callbacks = [
            [ function() {}, null, null ],
            [ null, function() {}, null ],
            [ null, null, function() {} ]
        ];
        $bool = true;
        $name = 'name'; $message = 'message'; $flags = 'flags';
        $timeout = 1.0;

        foreach ($callbacks as $callback)
        {
            $channel = $this->createChannel([], [ 'sendRequest' ]);
            $channel
                ->expects($this->once())
                ->method('sendRequest')
                ->with($name, $message, $flags, $callback[0], $callback[1], $callback[2], $timeout)
                ->will($this->returnValue($bool));

            $this->assertSame(
                $bool,
                $channel->send($name, $message, $flags, $callback[0], $callback[1], $callback[2], $timeout)
            );
        }
    }

    /**
     *
     */
    public function testApiPush_PushesAsync_WhenCallbacksAreNull()
    {
        $bool = true;
        $name = 'name'; $message = 'message'; $flags = 'flags';

        $channel = $this->createChannel([], [ 'pushAsync' ]);
        $channel
            ->expects($this->once())
            ->method('pushAsync')
            ->with($name, $message, $flags)
            ->will($this->returnValue($bool));

        $this->assertSame($bool, $channel->push($name, $message, $flags));
    }

    /**
     *
     */
    public function testApiPush_PushesRequest_WhenAtLeastOneOfCallbacksIsNotNull()
    {
        $callbacks = [
            [ function() {}, null, null ],
            [ null, function() {}, null ],
            [ null, null, function() {} ]
        ];
        $bool = true;
        $name = 'name'; $message = 'message'; $flags = 'flags';
        $timeout = 1.0;

        foreach ($callbacks as $callback)
        {
            $channel = $this->createChannel([], [ 'pushRequest' ]);
            $channel
                ->expects($this->once())
                ->method('pushRequest')
                ->with($name, $message, $flags, $callback[0], $callback[1], $callback[2], $timeout)
                ->will($this->returnValue($bool));

            $this->assertSame(
                $bool,
                $channel->push($name, $message, $flags, $callback[0], $callback[1], $callback[2], $timeout)
            );
        }
    }

    /**
     *
     */
    public function testApiSendAsync_HandlesSendAsync()
    {
        $name = 'name'; $message = 'message'; $flags = 'flags';

        $channel = $this->createChannel([], [ 'createMessageProtocol', 'handleSendAsync' ]);
        $channel
            ->expects($this->once())
            ->method('createMessageProtocol')
            ->will($this->returnArgument(0));
        $channel
            ->expects($this->once())
            ->method('handleSendAsync')
            ->with($name, $message, $flags)
            ->will($this->returnValue(true));

        $channel->sendAsync($name, $message, $flags);
    }

    /**
     *
     */
    public function testApiSendAsync_HandlesSendAsyncOnEachName()
    {
        $names = [ 'name1', 'name2' ]; $message = 'message'; $flags = 'flags';

        $channel = $this->createChannel([], [ 'createMessageProtocol', 'handleSendAsync' ]);
        $channel
            ->expects($this->once())
            ->method('createMessageProtocol')
            ->will($this->returnArgument(0));
        $channel
            ->expects($this->twice())
            ->method('handleSendAsync')
            ->will($this->returnValue(true));

        $channel->sendAsync($names, $message, $flags);
    }

    /**
     *
     */
    public function testApiSendAsync_ReturnsArrayOfStatuses_WhenMultipleNamesSet()
    {
        $names = [ 'name1', 'name2' ]; $message = 'message';

        $channel = $this->createChannel([], [ 'createMessageProtocol', 'handleSendAsync' ]);
        $channel
            ->expects($this->once())
            ->method('createMessageProtocol')
            ->will($this->returnArgument(0));
        $channel
            ->expects($this->twice())
            ->method('handleSendAsync')
            ->will($this->returnValue(true));

        $result = $channel->sendAsync($names, $message);

        $this->assertSame([ true, true ], $result);
    }

    /**
     *
     */
    public function testApiSendAsync_ReturnsStatus_WhenSingleNameSet()
    {
        $name = 'name'; $message = 'message';

        $channel = $this->createChannel([], [ 'createMessageProtocol', 'handleSendAsync' ]);
        $channel
            ->expects($this->once())
            ->method('createMessageProtocol')
            ->will($this->returnArgument(0));
        $channel
            ->expects($this->once())
            ->method('handleSendAsync')
            ->will($this->returnValue(true));

        $result = $channel->sendAsync($name, $message);

        $this->assertSame(true, $result);
    }

    /**
     *
     */
    public function testApiSendAsync_ReturnsEmptyArray_WhenNoneNameSet()
    {
        $names = []; $message = 'message';

        $channel = $this->createChannel([], [ 'createMessageProtocol', 'handleSendAsync' ]);
        $channel
            ->expects($this->once())
            ->method('createMessageProtocol')
            ->will($this->returnArgument(0));
        $channel
            ->expects($this->never())
            ->method('handleSendAsync');

        $result = $channel->sendAsync($names, $message);

        $this->assertSame([], $result);
    }

    /**
     *
     */
    public function testApiPushAsync_HandlesSendAsync()
    {
        $name = 'name'; $message = 'message'; $flags = 'flags';

        $channel = $this->createChannel([], [ 'createMessageProtocol', 'handlePushAsync' ]);
        $channel
            ->expects($this->once())
            ->method('createMessageProtocol')
            ->will($this->returnArgument(0));
        $channel
            ->expects($this->once())
            ->method('handlePushAsync')
            ->with($name, $message, $flags)
            ->will($this->returnValue(true));

        $channel->pushAsync($name, $message, $flags);
    }

    /**
     *
     */
    public function testApiPushAsync_HandlesSendAsyncOnEachName()
    {
        $names = [ 'name1', 'name2' ]; $message = 'message'; $flags = 'flags';

        $channel = $this->createChannel([], [ 'createMessageProtocol', 'handlePushAsync' ]);
        $channel
            ->expects($this->once())
            ->method('createMessageProtocol')
            ->will($this->returnArgument(0));
        $channel
            ->expects($this->twice())
            ->method('handlePushAsync')
            ->will($this->returnValue(true));

        $channel->pushAsync($names, $message, $flags);
    }

    /**
     *
     */
    public function testApiPushAsync_ReturnsArrayOfStatuses_WhenMultipleNamesSet()
    {
        $names = [ 'name1', 'name2' ]; $message = 'message';

        $channel = $this->createChannel([], [ 'createMessageProtocol', 'handlePushAsync' ]);
        $channel
            ->expects($this->once())
            ->method('createMessageProtocol')
            ->will($this->returnArgument(0));
        $channel
            ->expects($this->twice())
            ->method('handlePushAsync')
            ->will($this->returnValue(true));

        $result = $channel->pushAsync($names, $message);

        $this->assertSame([ true, true ], $result);
    }

    /**
     *
     */
    public function testApiPushAsync_ReturnsStatus_WhenSingleNameSet()
    {
        $name = 'name'; $message = 'message';

        $channel = $this->createChannel([], [ 'createMessageProtocol', 'handlePushAsync' ]);
        $channel
            ->expects($this->once())
            ->method('createMessageProtocol')
            ->will($this->returnArgument(0));
        $channel
            ->expects($this->once())
            ->method('handlePushAsync')
            ->will($this->returnValue(true));

        $result = $channel->pushAsync($name, $message);

        $this->assertSame(true, $result);
    }

    /**
     *
     */
    public function testApiPushAsync_ReturnsEmptyArray_WhenNoneNameSet()
    {
        $names = []; $message = 'message';

        $channel = $this->createChannel([], [ 'createMessageProtocol', 'handlePushAsync' ]);
        $channel
            ->expects($this->once())
            ->method('createMessageProtocol')
            ->will($this->returnArgument(0));
        $channel
            ->expects($this->never())
            ->method('handlePushAsync');

        $result = $channel->pushAsync($names, $message);

        $this->assertSame([], $result);
    }

    /**
     *
     */
    public function testApiSendRequest_HandlesSendAsync()
    {
        $name = 'name'; $message = 'message'; $flags = 'flags';

        $channel = $this->createChannel([], [ 'createMessageProtocol', 'handleSendRequest' ]);
        $channel
            ->expects($this->once())
            ->method('createMessageProtocol')
            ->will($this->returnArgument(0));
        $channel
            ->expects($this->once())
            ->method('handleSendRequest')
            ->with($name, $message, $flags)
            ->will($this->returnValue(true));

        $channel->sendRequest($name, $message, $flags);
    }

    /**
     *
     */
    public function testApiSendRequest_HandlesSendAsyncOnEachName()
    {
        $names = [ 'name1', 'name2' ]; $message = 'message'; $flags = 'flags';

        $channel = $this->createChannel([], [ 'createMessageProtocol', 'handleSendRequest' ]);
        $channel
            ->expects($this->once())
            ->method('createMessageProtocol')
            ->will($this->returnArgument(0));
        $channel
            ->expects($this->twice())
            ->method('handleSendRequest')
            ->will($this->returnValue(true));

        $channel->sendRequest($names, $message, $flags);
    }

    /**
     *
     */
    public function testApiSendRequest_ReturnsArrayOfStatuses_WhenMultipleNamesSet()
    {
        $names = [ 'name1', 'name2' ]; $message = 'message';

        $channel = $this->createChannel([], [ 'createMessageProtocol', 'handleSendRequest' ]);
        $channel
            ->expects($this->once())
            ->method('createMessageProtocol')
            ->will($this->returnArgument(0));
        $channel
            ->expects($this->twice())
            ->method('handleSendRequest')
            ->will($this->returnValue(true));

        $result = $channel->sendRequest($names, $message);

        $this->assertSame([ true, true ], $result);
    }

    /**
     *
     */
    public function testApiSendRequest_ReturnsStatus_WhenSingleNameSet()
    {
        $name = 'name'; $message = 'message';

        $channel = $this->createChannel([], [ 'createMessageProtocol', 'handleSendRequest' ]);
        $channel
            ->expects($this->once())
            ->method('createMessageProtocol')
            ->will($this->returnArgument(0));
        $channel
            ->expects($this->once())
            ->method('handleSendRequest')
            ->will($this->returnValue(true));

        $result = $channel->sendRequest($name, $message);

        $this->assertSame(true, $result);
    }

    /**
     *
     */
    public function testApiSendRequest_ReturnsEmptyArray_WhenNoneNameSet()
    {
        $names = []; $message = 'message';

        $channel = $this->createChannel([], [ 'createMessageProtocol', 'handleSendRequest' ]);
        $channel
            ->expects($this->once())
            ->method('createMessageProtocol')
            ->will($this->returnArgument(0));
        $channel
            ->expects($this->never())
            ->method('handleSendRequest');

        $result = $channel->sendRequest($names, $message);

        $this->assertSame([], $result);
    }

    /**
     *
     */
    public function testApiPushRequest_HandlesSendAsync()
    {
        $name = 'name'; $message = 'message'; $flags = 'flags';

        $channel = $this->createChannel([], [ 'createMessageProtocol', 'handlePushRequest' ]);
        $channel
            ->expects($this->once())
            ->method('createMessageProtocol')
            ->will($this->returnArgument(0));
        $channel
            ->expects($this->once())
            ->method('handlePushRequest')
            ->with($name, $message, $flags)
            ->will($this->returnValue(true));

        $channel->pushRequest($name, $message, $flags);
    }

    /**
     *
     */
    public function testApiPushRequest_HandlesSendAsyncOnEachName()
    {
        $names = [ 'name1', 'name2' ]; $message = 'message'; $flags = 'flags';

        $channel = $this->createChannel([], [ 'createMessageProtocol', 'handlePushRequest' ]);
        $channel
            ->expects($this->once())
            ->method('createMessageProtocol')
            ->will($this->returnArgument(0));
        $channel
            ->expects($this->twice())
            ->method('handlePushRequest')
            ->will($this->returnValue(true));

        $channel->pushRequest($names, $message, $flags);
    }

    /**
     *
     */
    public function testApiPushRequest_ReturnsArrayOfStatuses_WhenMultipleNamesSet()
    {
        $names = [ 'name1', 'name2' ]; $message = 'message';

        $channel = $this->createChannel([], [ 'createMessageProtocol', 'handlePushRequest' ]);
        $channel
            ->expects($this->once())
            ->method('createMessageProtocol')
            ->will($this->returnArgument(0));
        $channel
            ->expects($this->twice())
            ->method('handlePushRequest')
            ->will($this->returnValue(true));

        $result = $channel->pushRequest($names, $message);

        $this->assertSame([ true, true ], $result);
    }

    /**
     *
     */
    public function testApiPushRequest_ReturnsStatus_WhenSingleNameSet()
    {
        $name = 'name'; $message = 'message';

        $channel = $this->createChannel([], [ 'createMessageProtocol', 'handlePushRequest' ]);
        $channel
            ->expects($this->once())
            ->method('createMessageProtocol')
            ->will($this->returnArgument(0));
        $channel
            ->expects($this->once())
            ->method('handlePushRequest')
            ->will($this->returnValue(true));

        $result = $channel->pushRequest($name, $message);

        $this->assertSame(true, $result);
    }

    /**
     *
     */
    public function testApiPushRequest_ReturnsEmptyArray_WhenNoneNameSet()
    {
        $names = []; $message = 'message';

        $channel = $this->createChannel([], [ 'createMessageProtocol', 'handlePushRequest' ]);
        $channel
            ->expects($this->once())
            ->method('createMessageProtocol')
            ->will($this->returnArgument(0));
        $channel
            ->expects($this->never())
            ->method('handlePushRequest');

        $result = $channel->pushRequest($names, $message);

        $this->assertSame([], $result);
    }

    /**
     *
     */
    public function testApiReceive_ReceivesMessage()
    {
        $name = 'name';
        $protocol = new Protocol();

        $mock = $this->getMock(RouterComposite::class, [], [], '', false);
        $mock
            ->expects($this->once())
            ->method('handle')
            ->with($name, $protocol)
            ->will($this->returnValue(true));

        $channel = $this->createChannel([], [ 'getInput', 'emit' ]);
        $channel
            ->expects($this->once())
            ->method('getInput')
            ->will($this->returnValue($mock));
        $channel
            ->expects($this->once())
            ->method('emit')
            ->with('input', [ $name, $protocol ]);

        $channel->receive($name, $protocol);
    }

    /**
     *
     */
    public function testApiPull_PullsMessage()
    {
        $name = 'name';
        $protocol = new Protocol();

        $channel = $this->createChannel([], [ 'emit' ]);
        $channel
            ->expects($this->once())
            ->method('emit')
            ->with('input', [ $name, $protocol ]);

        $channel->pull($name, $protocol);
    }

    /**
     *
     */
    public function testApiIsStarted_ReturnsEmptyArray_WhenNoneBusIsSet()
    {
        $channel = $this->createChannel();
        $this->assertSame([], $channel->isStarted());
    }

    /**
     *
     */
    public function testApiIsStarted_ReturnsStatusesArray()
    {
        $bus1 = $this->createBus('name1', [ 'isStarted' ]);
        $bus1
            ->expects($this->once())
            ->method('isStarted')
            ->will($this->returnValue(false));

        $bus2 = $this->createBus('name2', [ 'isStarted' ]);
        $bus2
            ->expects($this->once())
            ->method('isStarted')
            ->will($this->returnValue(true));

        $channel = $this->createChannel([ 'bus1' => $bus1, 'bus2' => $bus2 ]);

        $this->assertSame([ 'bus1' => false, 'bus2' => true ], $channel->isStarted());
    }

    /**
     *
     */
    public function testApiIsStopped_ReturnsEmptyArray_WhenNoneBusIsSet()
    {
        $channel = $this->createChannel();
        $this->assertSame([], $channel->isStopped());
    }

    /**
     *
     */
    public function testApiIsStopped_ReturnsStatusesArray()
    {
        $bus1 = $this->createBus('name1', [ 'isStopped' ]);
        $bus1
            ->expects($this->once())
            ->method('isStopped')
            ->will($this->returnValue(false));

        $bus2 = $this->createBus('name2', [ 'isStopped' ]);
        $bus2
            ->expects($this->once())
            ->method('isStopped')
            ->will($this->returnValue(true));

        $channel = $this->createChannel([ 'bus1' => $bus1, 'bus2' => $bus2 ]);

        $this->assertSame([ 'bus1' => false, 'bus2' => true ], $channel->isStopped());
    }

    /**
     *
     */
    public function testApiIsConnected_ReturnsFalse_WhenNoneBusIsSet()
    {
        $name = 'name';
        $channel = $this->createChannel();

        $this->assertFalse($channel->isConnected($name));
    }

    /**
     *
     */
    public function testApiIsConnected_ReturnsFalse_WhenNoneBusReturnsTrue()
    {
        $name = 'name';
        $bus1 = $this->createBus('name1', [ 'isConnected' ]);
        $bus1
            ->expects($this->once())
            ->method('isConnected')
            ->with($name)
            ->will($this->returnValue(false));
        $bus2 = $this->createBus('name2', [ 'isConnected' ]);
        $bus2
            ->expects($this->once())
            ->method('isConnected')
            ->with($name)
            ->will($this->returnValue(false));

        $channel = $this->createChannel([ 'bus1' => $bus1, 'bus2' => $bus2 ]);

        $this->assertFalse($channel->isConnected($name));
    }

    /**
     *
     */
    public function testApiIsConnected_ReturnsTrue_WhenAtLeastOneBusReturnsTrue()
    {
        $name = 'name';
        $bus1 = $this->createBus('name1', [ 'isConnected' ]);
        $bus1
            ->expects($this->once())
            ->method('isConnected')
            ->with($name)
            ->will($this->returnValue(false));
        $bus2 = $this->createBus('name2', [ 'isConnected' ]);
        $bus2
            ->expects($this->once())
            ->method('isConnected')
            ->with($name)
            ->will($this->returnValue(true));

        $channel = $this->createChannel([ 'bus1' => $bus1, 'bus2' => $bus2 ]);

        $this->assertTrue($channel->isConnected($name));
    }

    /**
     *
     */
    public function testApiGetConnected_ReturnsEmptyArray_WhenNoBusSet()
    {
        $channel = $this->createChannel();
        $this->assertSame([], $channel->getConnected());
    }

    /**
     *
     */
    public function testApiGetConnected_ReturnsUniqueArrayOfConnectedNames()
    {
        $bus1 = $this->createBus('name1', [ 'getConnected' ]);
        $bus1
            ->expects($this->once())
            ->method('getConnected')
            ->will($this->returnValue([ 'A', 'B' ]));

        $bus2 = $this->createBus('name2', [ 'getConnected' ]);
        $bus2
            ->expects($this->once())
            ->method('getConnected')
            ->will($this->returnValue([ 'A', 'C' ]));

        $channel = $this->createChannel([ 'bus1' => $bus1, 'bus2' => $bus2 ]);

        $this->assertSame([ 'A', 'B', 'C' ], $channel->getConnected());
    }

    /**
     *
     */
    public function testApiFilterConnected_ReturnsEmptyArray_WhenNoBusSet()
    {
        $name = 'name';
        $channel = $this->createChannel();
        $this->assertSame([], $channel->filterConnected($name));
    }

    /**
     *
     */
    public function testApiMatchConnected_ReturnsUniqueArrayOfConnectedNames()
    {
        $name = 'name';
        $bus1 = $this->createBus('name1', [ 'filterConnected' ]);
        $bus1
            ->expects($this->once())
            ->method('filterConnected')
            ->with($name)
            ->will($this->returnValue([ 'A', 'B' ]));

        $bus2 = $this->createBus('name2', [ 'filterConnected' ]);
        $bus2
            ->expects($this->once())
            ->method('filterConnected')
            ->with($name)
            ->will($this->returnValue([ 'A', 'C' ]));

        $channel = $this->createChannel([ 'bus1' => $bus1, 'bus2' => $bus2 ]);

        $this->assertSame([ 'A', 'B', 'C' ], $channel->filterConnected($name));
    }

    /**
     *
     */
    public function testApiHandleSendAsync_HandlesMessageUsingOutput()
    {
        $name = 'name';
        $message = new Protocol();
        $flags = 'flags';
        $status = true;

        $mock = $this->getMock(RouterComposite::class, [ 'handle' ], [], '', false);
        $mock
            ->expects($this->once())
            ->method('handle')
            ->with($name, $message, $flags)
            ->will($this->returnValue($status));

        $channel = $this->createChannel([], [ 'getOutput' ]);
        $channel
            ->expects($this->once())
            ->method('getOutput')
            ->will($this->returnValue($mock));

        $this->assertSame($status, $this->callProtectedMethod($channel, 'handleSendAsync', [ $name, $message, $flags ]));
        $this->assertSame(Channel::TYPE_SND, $message->getType());
        $this->assertSame($name, $message->getDestination());
    }

    /**
     *
     */
    public function testApiHandlePushAsync_ReturnsFalseAndDoesNotEmitEvents_WhenAllBusesReturnFalse()
    {
        $name = 'name';
        $message = 'message';
        $flags = 'flags';

        $bus1 = $this->createBus('name1', [ 'push' ]);
        $bus1
            ->expects($this->once())
            ->method('push')
            ->with($name, $message, $flags)
            ->will($this->returnValue(false));

        $bus2 = $this->createBus('name2', [ 'push' ]);
        $bus2
            ->expects($this->once())
            ->method('push')
            ->with($name, $message, $flags)
            ->will($this->returnValue(false));

        $channel = $this->createChannel([ 'bus1' => $bus1, 'bus2' => $bus2 ], [ 'emit' ]);
        $channel
            ->expects($this->never())
            ->method('emit');

        $result = $this->callProtectedMethod($channel, 'handlePushAsync', [ $name, $message, $flags ]);

        $this->assertFalse($result);
    }

    /**
     *
     */
    public function testApiHandlePushAsync_ReturnsTrueAndDoesEmitsEvents_WhenAtLeastOneBusReturnTrue()
    {
        $name = 'name';
        $message = 'message';
        $flags = 'flags';

        $bus1 = $this->createBus('name1', [ 'push' ]);
        $bus1
            ->expects($this->once())
            ->method('push')
            ->with($name, $message, $flags)
            ->will($this->returnValue(true));

        $bus2 = $this->createBus('name2', [ 'push' ]);
        $bus2
            ->expects($this->once())
            ->method('push')
            ->with($name, $message, $flags)
            ->will($this->returnValue(false));

        $channel = $this->createChannel([ 'bus1' => $bus1, 'bus2' => $bus2 ], [ 'emit' ]);
        $channel
            ->expects($this->once())
            ->method('emit')
            ->with('output', [ $name, $message ]);

        $result = $this->callProtectedMethod($channel, 'handlePushAsync', [ $name, $message, $flags ]);

        $this->assertTrue($result);
    }

    /**
     *
     */
    public function testApiHandleSendRequest_HandlesMessagUsingOutput()
    {
        $name = 'name';
        $message = new Protocol();
        $flags = 'flags';
        $success = function() {};
        $failure = function() {};
        $abort   = function() {};
        $timeout = 2.0;
        $status = true;

        $mock = $this->getMock(RouterComposite::class, [ 'handle' ], [], '', false);
        $mock
            ->expects($this->once())
            ->method('handle')
            ->with($name, $message, $flags)
            ->will($this->returnValue($status));

        $channel = $this->createChannel([], [ 'getOutput' ]);
        $channel
            ->expects($this->once())
            ->method('getOutput')
            ->will($this->returnValue($mock));

        $result = $this->callProtectedMethod($channel, 'handleSendRequest', [ $name, $message, $flags, $success, $failure, $abort, $timeout ]);

        $this->assertSame($status, $result);
    }

    /**
     *
     */
    public function testApiHandlePushRequest_ReturnsFalseAndDoesNotEmitEvents_WhenAllBusesReturnFalse()
    {
        $name = 'name';
        $message = 'message';
        $flags = 'flags';
        $success = function() {};
        $failure = function() {};
        $abort   = function() {};
        $timeout = 2.0;

        $bus1 = $this->createBus('name1', [ 'push' ]);
        $bus1
            ->expects($this->once())
            ->method('push')
            ->with($name, $message, $flags, $success, $failure, $abort, $timeout)
            ->will($this->returnValue(false));

        $bus2 = $this->createBus('name2', [ 'push' ]);
        $bus2
            ->expects($this->once())
            ->method('push')
            ->with($name, $message, $flags, $success, $failure, $abort, $timeout)
            ->will($this->returnValue(false));

        $channel = $this->createChannel([ 'bus1' => $bus1, 'bus2' => $bus2 ], [ 'emit' ]);
        $channel
            ->expects($this->never())
            ->method('emit');

        $result = $this->callProtectedMethod($channel, 'handlePushRequest', [ $name, $message, $flags, $success, $failure, $abort, $timeout ]);

        $this->assertFalse($result);
    }

    /**
     *
     */
    public function testApiHandlePushRequest_ReturnsTrueAndDoesEmitsEvents_WhenAtLeastOneBusReturnTrue()
    {
        $name = 'name';
        $message = 'message';
        $flags = 'flags';
        $success = function() {};
        $failure = function() {};
        $abort   = function() {};
        $timeout = 2.0;

        $bus1 = $this->createBus('name1', [ 'push' ]);
        $bus1
            ->expects($this->once())
            ->method('push')
            ->with($name, $message, $flags, $success, $failure, $abort, $timeout)
            ->will($this->returnValue($this->getMock(RequestRecord::class, [], [], '', false)));

        $bus2 = $this->createBus('name2', [ 'push' ]);
        $bus2
            ->expects($this->once())
            ->method('push')
            ->with($name, $message, $flags, $success, $failure, $abort, $timeout)
            ->will($this->returnValue(false));

        $channel = $this->createChannel([ 'bus1' => $bus1, 'bus2' => $bus2 ], [ 'emit' ]);
        $channel
            ->expects($this->once())
            ->method('emit')
            ->with('output', [ $name, $message ]);

        $result = $this->callProtectedMethod($channel, 'handlePushRequest', [ $name, $message, $flags, $success, $failure, $abort, $timeout ]);

        $this->assertTrue($result);
    }

    /**
     *
     */
    public function testProtectedApiGenID_GeneratesUniqueID_WithPrefixEqualToSeed()
    {
        $channel = $this->createChannel();

        $this->setProtectedProperty($channel, 'seed', $seed = 'seed');
        $id1 = $this->callProtectedMethod($channel, 'genID');
        $id2 = $this->callProtectedMethod($channel, 'genID');
        $id3 = $this->callProtectedMethod($channel, 'genID');

        $this->assertSame($seed . '1000000000', $id1);
        $this->assertSame($seed . '1000000001', $id2);
        $this->assertSame($seed . '1000000002', $id3);
    }

    /**
     *
     */
    public function testProtectedApiGetTime_ReturnsTime()
    {
        $channel = $this->createChannel();
        $time = $this->callProtectedMethod($channel, 'getTime');

        $this->assertSame(time(), (int)($time/1e3));
    }

    /**
     *
     */
    public function testProtectedApiGetNextSuffix_ReturnsNextSuffix()
    {
        $channel = $this->createChannel();

        $cnt1 = $this->callProtectedMethod($channel, 'getNextSuffix');
        $cnt2 = $this->callProtectedMethod($channel, 'getNextSuffix');
        $cnt3 = $this->callProtectedMethod($channel, 'getNextSuffix');

        $this->assertSame('1000000000', $cnt1);
        $this->assertSame('1000000001', $cnt2);
        $this->assertSame('1000000002', $cnt3);
    }

    /**
     *
     */
    public function testProtectedApiGetNextSuffix_ResetsOnAfter2Mld()
    {
        $channel = $this->createChannel();

        $this->setProtectedProperty($channel, 'counter', 2e9);
        $cnt1 = $this->callProtectedMethod($channel, 'getNextSuffix');
        $cnt2 = $this->callProtectedMethod($channel, 'getNextSuffix');
        $cnt3 = $this->callProtectedMethod($channel, 'getNextSuffix');

        $this->assertSame('2000000000', $cnt1);
        $this->assertSame('1000000000', $cnt2);
        $this->assertSame('1000000001', $cnt3);
    }

    /**
     *
     */
    public function testProtectedApiCreateMessageProtocol_AcceptsMessageProtocol()
    {
        $channel = $this->createChannel();
        $message = new Protocol();
        $result  = $this->callProtectedMethod($channel, 'createMessageProtocol', [ $message ]);

        $this->assertSame($message, $result);
    }

    /**
     *
     */
    public function testProtectedApiCreateMessageProtocol_AcceptsStringProtocol()
    {
        $channel = $this->createChannel();
        $message = 'message';
        $result  = $this->callProtectedMethod($channel, 'createMessageProtocol', [ $message ]);

        $this->assertInstanceOf(Protocol::class, $result);
        $this->assertSame($message, $result->getMessage());
    }

    /**
     *
     */
    public function testProtectedApiCreateMessageProtocol_AcceptsNull()
    {
        $channel = $this->createChannel();
        $message = null;
        $result  = $this->callProtectedMethod($channel, 'createMessageProtocol', [ $message ]);

        $this->assertInstanceOf(Protocol::class, $result);
        $this->assertSame('', $result->getMessage());
    }

    /**
     *
     */
    public function testProtectedApiCreateMessageProtocol_DoesNotOverwriteSetFields()
    {
        $channel = $this->createChannel();
        $message = new Protocol(
            $type = 'type',
            $pid = 'pid',
            $dest = 'destination',
            $origin = 'origin',
            $text = 'text',
            $exception = 'exception',
            $timestamp = 100
        );
        $result  = $this->callProtectedMethod($channel, 'createMessageProtocol', [ $message ]);

        $this->assertInstanceOf(Protocol::class, $result);
        $this->assertSame($type, $result->getType());
        $this->assertSame($pid, $result->getPid());
        $this->assertSame($dest, $result->getDestination());
        $this->assertSame($origin, $result->getOrigin());
        $this->assertSame($text, $result->getMessage());
        $this->assertSame($exception, $result->getException());
        $this->assertSame($timestamp, $result->getTimestamp());
    }

    /**
     *
     */
    public function testProtectedApiCreateMessageProtocol_OverwritesNotSetFields()
    {
        $channel = $this->createChannel();
        $message = new Protocol();
        $result  = $this->callProtectedMethod($channel, 'createMessageProtocol', [ $message ]);

        $this->assertInstanceOf(Protocol::class, $result);
        $this->assertNotSame('', $result->getPid());
        $this->assertGreaterThan(0, $result->getTimestamp());
    }

    /**
     * @return string[][]
     */
    public function eventsProvider()
    {
        return [
            [ 'start' ],
            [ 'stop' ],
            [ 'connect' ],
            [ 'disconnect' ],
            [ 'input' ],
            [ 'output' ]
        ];
    }

    /**
     * @param string $name
     * @param string[]|null $methods
     * @return Channel|\PHPUnit_Framework_MockObject_MockObject
     */
    public function prepareBus($name = 'name', $methods = [])
    {
        $model   = $this->getMock(ChannelModelInterface::class, [], [], '', false);
        $model
            ->expects($this->any())
            ->method('copyEvents')
            ->will($this->returnCallback(function($obj, $events) {
                $handlers = [];
                foreach ($events as $event)
                {
                    $handlers[] = $this->getMock(EventListener::class, [], [], '', false);
                }
                return $handlers;
            }));
        $model
            ->expects($this->any())
            ->method('on')
            ->will($this->returnCallback(function($event, $callback) {
                return $this->getMock(EventListener::class, [], [], '', false);
            }));

        $router  = $this->getMock(RouterCompositeInterface::class, [], [], '', false);
        $encoder = $this->getMock(EncoderInterface::class, [], [], '', false);
        $loop    = $this->getMock(LoopInterface::class, [], [], '', false);

        $bus = $this->getMock(Channel::class, $methods, [ $name, $model, $router, $encoder, $loop ]);

        return $bus;
    }

    /**
     * @param string $name
     * @param string[] $methods
     * @return Channel|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createBus($name = 'name', $methods = [])
    {
        $methods = array_merge([ 'copyEvents', 'on' ], $methods);
        $bus = $this->prepareBus($name, $methods);
        $bus
            ->expects($this->any())
            ->method('copyEvents')
            ->will($this->returnCallback(function($obj, $events) {
                $handlers = [];
                foreach ($events as $event)
                {
                    $handlers[] = $this->getMock(EventListener::class, [], [], '', false);
                }
                return $handlers;
            }));
        $bus
            ->expects($this->any())
            ->method('on')
            ->will($this->returnCallback(function($event, $callback) {
                return $this->getMock(EventListener::class, [], [], '', false);
            }));

        return $bus;
    }

    /**
     * @param ChannelInterface[]|ChannelCompositeInterface[] $buses
     * @param string[]|null $methods
     * @return ChannelComposite|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createChannel($buses = [], $methods = null)
    {
        $name = 'name';
        $router = $this->getMock(RouterComposite::class, [], [], '', false);
        $loop   = $this->getMock(Loop::class, [], [], '', false);

        $this->channel = $this->getMock(ChannelComposite::class, $methods, [ $name, $buses, $router, $loop ]);

        return $this->channel;
    }

    /**
     * @param string[]|null $methods
     * @return RouterComposite|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createRouter($methods = null)
    {
        $mock = $this->getMock(RouterComposite::class, $methods, [], '', false);

        $this->setProtectedProperty($this->channel, 'router', $mock);

        return $mock;
    }

    /**
     * @param string[]|null $methods
     * @return Loop|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createLoop($methods = null)
    {
        $mock = $this->getMock(Loop::class, $methods, [], '', false);

        $this->setProtectedProperty($this->channel, 'loop', $mock);

        return $mock;
    }
}
