<?php

namespace Kraken\_Unit\Channel;

use Kraken\Channel\Channel;
use Kraken\Channel\ChannelBase;
use Kraken\Channel\ChannelBaseInterface;
use Kraken\Channel\ChannelEncoderInterface;
use Kraken\Channel\ChannelModelInterface;
use Kraken\Channel\ChannelProtocol;
use Kraken\Channel\ChannelProtocolInterface;
use Kraken\Channel\ChannelRouterBase;
use Kraken\Channel\ChannelRouterComposite;
use Kraken\Channel\ChannelRouterCompositeInterface;
use Kraken\Event\EventHandler;
use Kraken\Loop\Loop;
use Kraken\Loop\LoopInterface;
use Kraken\Test\TUnit;

class ChannelBaseText extends TUnit
{
    /**
     * @var ChannelBase|\PHPUnit_Framework_MockObject_MockObject
     */
    private $channel;

    /**
     *
     */
    public function testApiConstructor_CreatesInstance()
    {
        $channel = $this->createChannel();

        $this->assertInstanceOf(ChannelBase::class, $channel);
        $this->assertInstanceOf(ChannelBaseInterface::class, $channel);
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
    public function testApiName_ReturnsName()
    {
        $channel = $this->createChannel();
        $this->assertSame('name', $channel->name());
    }

    /**
     *
     */
    public function testApiModel_ReturnsModel()
    {
        $channel = $this->createChannel();
        $model   = $this->createModel();
        $this->assertSame($model, $channel->model());
    }

    /**
     *
     */
    public function testApiRouter_ReturnsRouter()
    {
        $channel = $this->createChannel();
        $router  = $this->createRouter();
        $this->assertSame($router, $channel->router());
    }

    /**
     *
     */
    public function testApiInput_ReturnsRouterInputBus()
    {
        $channel = $this->createChannel();

        $input  = $this->getMock(ChannelRouterBase::class, [], [], '', false);
        $router = $this->createRouter([ 'bus' ]);
        $router
            ->expects($this->once())
            ->method('bus')
            ->with('input')
            ->will($this->returnValue($input));

        $this->assertSame($input, $channel->input());
    }

    /**
     *
     */
    public function testApiOutput_ReturnsRouterOutputBus()
    {
        $channel = $this->createChannel();

        $output = $this->getMock(ChannelRouterBase::class, [], [], '', false);
        $router = $this->createRouter([ 'bus' ]);
        $router
            ->expects($this->once())
            ->method('bus')
            ->with('output')
            ->will($this->returnValue($output));

        $this->assertSame($output, $channel->output());
    }

    /**
     *
     */
    public function testApiCreateProtocol_CreatesProtocol_WhenNullPassed()
    {
        $channel = $this->createChannel();

        $result = $channel->createProtocol();

        $this->assertInstanceOf(ChannelProtocolInterface::class, $result);
        $this->assertSame('', $result->getMessage());
    }

    /**
     *
     */
    public function testApiCreateProtocol_CreatesProtocol_WhenStringPassed()
    {
        $channel = $this->createChannel();

        $result = $channel->createProtocol('text');

        $this->assertInstanceOf(ChannelProtocolInterface::class, $result);
        $this->assertSame('text', $result->getMessage());
    }

    /**
     * @dataProvider eventsProvider
     */
    public function testCaseAllOnMethods_RegisterHandlers($event)
    {
        $handler  = $this->getMock(EventHandler::class, [], [], '', false);
        $callable = function() {};

        $method = 'on' . ucfirst($event);

        $channel = $this->createChannel([ 'on' ]);
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
    public function testApiStart_CallsStartOnModel()
    {
        $channel = $this->createChannel();
        $model   = $this->createModel();
        $model
            ->expects($this->once())
            ->method('start');

        $channel->start();
    }

    /**
     *
     */
    public function testApiStop_CallsStopOnModel()
    {
        $channel = $this->createChannel();
        $model   = $this->createModel();
        $model
            ->expects($this->once())
            ->method('stop');

        $channel->stop();
    }

    /**
     *
     */
    public function testApiSend_SendsAsync_WhenCallbacksAreNull()
    {
        $bool = true;
        $name = 'name'; $message = 'message'; $flags = 'flags';

        $channel = $this->createChannel([ 'sendAsync' ]);
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
            $channel = $this->createChannel([ 'sendRequest' ]);
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

        $channel = $this->createChannel([ 'pushAsync' ]);
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
            $channel = $this->createChannel([ 'pushRequest' ]);
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

        $channel = $this->createChannel([ 'createMessageProtocol', 'handleSendAsync' ]);
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

        $channel = $this->createChannel([ 'createMessageProtocol', 'handleSendAsync' ]);
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

        $channel = $this->createChannel([ 'createMessageProtocol', 'handleSendAsync' ]);
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

        $channel = $this->createChannel([ 'createMessageProtocol', 'handleSendAsync' ]);
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

        $channel = $this->createChannel([ 'createMessageProtocol', 'handleSendAsync' ]);
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

        $channel = $this->createChannel([ 'createMessageProtocol', 'handlePushAsync' ]);
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

        $channel = $this->createChannel([ 'createMessageProtocol', 'handlePushAsync' ]);
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

        $channel = $this->createChannel([ 'createMessageProtocol', 'handlePushAsync' ]);
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

        $channel = $this->createChannel([ 'createMessageProtocol', 'handlePushAsync' ]);
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

        $channel = $this->createChannel([ 'createMessageProtocol', 'handlePushAsync' ]);
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

        $channel = $this->createChannel([ 'createMessageProtocol', 'handleSendRequest' ]);
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

        $channel = $this->createChannel([ 'createMessageProtocol', 'handleSendRequest' ]);
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

        $channel = $this->createChannel([ 'createMessageProtocol', 'handleSendRequest' ]);
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

        $channel = $this->createChannel([ 'createMessageProtocol', 'handleSendRequest' ]);
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

        $channel = $this->createChannel([ 'createMessageProtocol', 'handleSendRequest' ]);
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

        $channel = $this->createChannel([ 'createMessageProtocol', 'handlePushRequest' ]);
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

        $channel = $this->createChannel([ 'createMessageProtocol', 'handlePushRequest' ]);
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

        $channel = $this->createChannel([ 'createMessageProtocol', 'handlePushRequest' ]);
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

        $channel = $this->createChannel([ 'createMessageProtocol', 'handlePushRequest' ]);
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

        $channel = $this->createChannel([ 'createMessageProtocol', 'handlePushRequest' ]);
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
    public function testApiReceive_ReturnsImmediatelyIfRequestIsReceived()
    {
        $name = 'name';
        $protocol = new ChannelProtocol();

        $channel = $this->createChannel([ 'handleReceiveRequest', 'handleReceiveResponse' ]);
        $channel
            ->expects($this->once())
            ->method('handleReceiveRequest')
            ->with($protocol)
            ->will($this->returnValue(true));
        $channel
            ->expects($this->never())
            ->method('handleReceiveResponse');

        $channel->receive($name, $protocol);
    }

    /**
     *
     */
    public function testApiReceive_ReturnsImmediately_WhenRequestIsReceived()
    {
        $name = 'name';
        $protocol = new ChannelProtocol();

        $channel = $this->createChannel([ 'handleReceiveRequest', 'handleReceiveResponse' ]);
        $channel
            ->expects($this->once())
            ->method('handleReceiveRequest')
            ->with($protocol)
            ->will($this->returnValue(true));
        $channel
            ->expects($this->never())
            ->method('handleReceiveResponse');

        $channel->receive($name, $protocol);
    }

    /**
     *
     */
    public function testApiReceive_EmitsEvent_WhenResponseIsReceived()
    {
        $name = 'name';
        $protocol = new ChannelProtocol();

        $channel = $this->createChannel([ 'emit', 'handleReceiveRequest', 'handleReceiveResponse' ]);
        $channel
            ->expects($this->once())
            ->method('emit')
            ->with('input', [ $name, $protocol ]);
        $channel
            ->expects($this->once())
            ->method('handleReceiveRequest')
            ->with($protocol)
            ->will($this->returnValue(false));
        $channel
            ->expects($this->once())
            ->method('handleReceiveResponse')
            ->with($protocol)
            ->will($this->returnValue(true));

        $channel->receive($name, $protocol);
    }

    /**
     *
     */
    public function testApiReceive_EmitsEvent_WhenResponseIsHandledByRouter()
    {
        $name = 'name';
        $protocol = new ChannelProtocol();

        $mock = $this->getMock(ChannelRouterComposite::class, [], [], '', false);
        $mock
            ->expects($this->once())
            ->method('handle')
            ->with($name, $protocol)
            ->will($this->returnValue(true));

        $channel = $this->createChannel([ 'emit', 'handleReceiveRequest', 'handleReceiveResponse', 'input' ]);
        $channel
            ->expects($this->once())
            ->method('emit')
            ->with('input', [ $name, $protocol ]);
        $channel
            ->expects($this->once())
            ->method('handleReceiveRequest')
            ->with($protocol)
            ->will($this->returnValue(false));
        $channel
            ->expects($this->once())
            ->method('handleReceiveResponse')
            ->with($protocol)
            ->will($this->returnValue(false));
        $channel
            ->expects($this->once())
            ->method('input')
            ->will($this->returnValue($mock));

        $channel->receive($name, $protocol);
    }

    /**
     *
     */
    public function testApiReceive_DoesNothing_WhenResponseIsNotHandledByRouter()
    {
        $name = 'name';
        $protocol = new ChannelProtocol();

        $mock = $this->getMock(ChannelRouterComposite::class, [], [], '', false);
        $mock
            ->expects($this->once())
            ->method('handle')
            ->with($name, $protocol)
            ->will($this->returnValue(false));

        $channel = $this->createChannel([ 'emit', 'handleReceiveRequest', 'handleReceiveResponse', 'input' ]);
        $channel
            ->expects($this->never())
            ->method('emit');
        $channel
            ->expects($this->once())
            ->method('handleReceiveRequest')
            ->with($protocol)
            ->will($this->returnValue(false));
        $channel
            ->expects($this->once())
            ->method('handleReceiveResponse')
            ->with($protocol)
            ->will($this->returnValue(false));
        $channel
            ->expects($this->once())
            ->method('input')
            ->will($this->returnValue($mock));

        $channel->receive($name, $protocol);
    }

    /**
     *
     */
    public function testApiPull_PullsMessage()
    {
        $name = 'name';
        $protocol = new ChannelProtocol();

        $channel = $this->createChannel([ 'emit' ]);
        $channel
            ->expects($this->once())
            ->method('emit')
            ->with('input', [ $name, $protocol ]);

        $channel->pull($name, $protocol);
    }

    /**
     *
     */
    public function testApiIsConnected_CallsModelMethodForEachPassedNameAndReturnsStatusArray()
    {
        $channel = $this->createChannel();
        $model   = $this->createModel();

        $cnt = 0;
        $model
            ->expects($this->twice())
            ->method('isConnected')
            ->with($this->isType('string'))
            ->will($this->returnCallback(function() use(&$cnt) {
                return ++$cnt % 2 === 0 ? true : false;
            }));

        $this->assertSame([ false, true ], $channel->isConnected([ 'A', 'B' ]));
    }

    /**
     *
     */
    public function testApiIsConnected_CallsModelMethod()
    {
        $channel = $this->createChannel();
        $model   = $this->createModel();

        $status = true;
        $model
            ->expects($this->once())
            ->method('isConnected')
            ->with('A')
            ->will($this->returnValue($status));

        $this->assertSame($status, $channel->isConnected('A'));
    }

    /**
     *
     */
    public function testApiGetConnected_CallsModelMethod()
    {
        $channel = $this->createChannel();
        $model   = $this->createModel();

        $conns = [ 'A', 'B' ];
        $model
            ->expects($this->once())
            ->method('getConnected')
            ->will($this->returnValue($conns));

        $this->assertSame($conns, $channel->getConnected());
    }

    /**
     *
     */
    public function testApiFilterConnected_ReturnsMatched()
    {
        $channel = $this->createChannel();
        $model   = $this->createModel();

        $conns = [
            "darkgray",
            "black",
            "grayish",
            "griy",
            "red",
            "some kind of gray"
        ];
        $expected = [
            "darkgray",
            "some kind of gray"
        ];

        $model
            ->expects($this->once())
            ->method('getConnected')
            ->will($this->returnValue($conns));

        $this->assertSame($expected, $channel->filterConnected("*gr[ae]y"));
    }

    /**
     *
     */
    public function testApiHandleSendAsync_HandlesMessageUsingOutput()
    {
        $name = 'name';
        $message = new ChannelProtocol();
        $flags = 'flags';
        $status = true;

        $mock = $this->getMock(ChannelRouterComposite::class, [ 'handle' ], [], '', false);
        $mock
            ->expects($this->once())
            ->method('handle')
            ->with($name, $message, $flags)
            ->will($this->returnValue($status));

        $channel = $this->createChannel([ 'output' ]);
        $channel
            ->expects($this->once())
            ->method('output')
            ->will($this->returnValue($mock));

        $this->assertSame($status, $this->callProtectedMethod($channel, 'handleSendAsync', [ $name, $message, $flags ]));
        $this->assertSame(Channel::TYPE_SND, $message->getType());
        $this->assertSame($name, $message->getDestination());
    }

    /**
     *
     */
    public function testApiHandleSendRequest_HandlesMessagUsingOutput()
    {
        $name = 'name';
        $message = new ChannelProtocol();
        $flags = 'flags';
        $success = function() {};
        $failure = function() {};
        $abort   = function() {};
        $timeout = 2.0;
        $status = true;

        $mock = $this->getMock(ChannelRouterComposite::class, [ 'handle' ], [], '', false);
        $mock
            ->expects($this->once())
            ->method('handle')
            ->with($name, $message, $flags)
            ->will($this->returnValue($status));

        $channel = $this->createChannel([ 'output' ]);
        $channel
            ->expects($this->once())
            ->method('output')
            ->will($this->returnValue($mock));

        $result = $this->callProtectedMethod($channel, 'handleSendRequest', [ $name, $message, $flags, $success, $failure, $abort, $timeout ]);

        $this->assertSame($status, $result);
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
        $message = new ChannelProtocol();
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

        $this->assertInstanceOf(ChannelProtocol::class, $result);
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

        $this->assertInstanceOf(ChannelProtocol::class, $result);
        $this->assertSame('', $result->getMessage());
    }

    /**
     *
     */
    public function testProtectedApiCreateMessageProtocol_DoesNotOverwriteSetFields()
    {
        $channel = $this->createChannel();
        $message = new ChannelProtocol(
            $type = 'type',
            $pid = 'pid',
            $dest = 'destination',
            $origin = 'origin',
            $text = 'text',
            $exception = 'exception',
            $timestamp = 100
        );
        $result  = $this->callProtectedMethod($channel, 'createMessageProtocol', [ $message ]);

        $this->assertInstanceOf(ChannelProtocol::class, $result);
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
        $message = new ChannelProtocol();
        $result  = $this->callProtectedMethod($channel, 'createMessageProtocol', [ $message ]);

        $this->assertInstanceOf(ChannelProtocol::class, $result);
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
     * @param string[]|null $methods
     * @return ChannelBase|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createChannel($methods = null)
    {
        $model   = $this->getMock(ChannelModelInterface::class, [], [], '', false);
        $model
            ->expects($this->any())
            ->method('copyEvents')
            ->will($this->returnCallback(function($obj, $events) {
                $handlers = [];
                foreach ($events as $event)
                {
                    $handlers[] = $this->getMock(EventHandler::class, [], [], '', false);
                }
                return $handlers;
            }));
        $model
            ->expects($this->any())
            ->method('on')
            ->will($this->returnCallback(function($event, $callback) {
                return $this->getMock(EventHandler::class, [], [], '', false);
            }));

        $router  = $this->getMock(ChannelRouterCompositeInterface::class, [], [], '', false);
        $router
            ->expects($this->any())
            ->method('bus')
            ->will($this->returnValue(null));

        $encoder = $this->getMock(ChannelEncoderInterface::class, [], [], '', false);
        $loop    = $this->getMock(LoopInterface::class, [], [], '', false);

        $mock = $this->getMock(ChannelBase::class, $methods, [ 'name', $model, $router, $encoder, $loop ]);

        $this->channel = $mock;

        return $mock;
    }

    /**
     * @return ChannelRouterComposite|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createModel()
    {
        $mock = $this->getMock(ChannelModelInterface::class, [], [], '', false);

        $this->setProtectedProperty($this->channel, 'model', $mock);

        return $mock;
    }

    /**
     * @param string[]|null $methods
     * @return ChannelRouterComposite|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createRouter($methods = null)
    {
        $mock = $this->getMock(ChannelRouterComposite::class, $methods, [], '', false);

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
