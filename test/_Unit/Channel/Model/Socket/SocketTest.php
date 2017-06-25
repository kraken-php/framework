<?php

namespace Kraken\_Unit\Channel\Model\Socket;

use Kraken\Channel\Channel;
use Kraken\Channel\Model\Socket\Socket;
use Kraken\Channel\ChannelModelInterface;
use Dazzle\Socket\SocketListenerInterface;
use Dazzle\Loop\Loop;
use Kraken\Test\TUnit;

class SocketTest extends TUnit
{
    /**
     * @var Socket|\PHPUnit_Framework_MockObject_MockObject
     */
    private $model;

    /**
     *
     */
    public function testApiConstrcutor_CreatesInstance()
    {
        $model = $this->createBinder();

        $this->assertInstanceOf(Socket::class, $model);
        $this->assertInstanceOf(ChannelModelInterface::class, $model);
    }

    /**
     *
     */
    public function testApiDestrcutor_CreatesInstance()
    {
        $model = $this->createBinder();
        unset($model);
    }

    /**
     *
     */
    public function testProtectedApiParseBinderMessage_ParsesBinderMessage()
    {
        $model = $this->createBinder();
        $result = $this->callProtectedMethod($model, 'parseBinderMessage', [ "from|id|type|msg" ]);

        $this->assertSame([ 'id', 'type', 'msg' ], $result);
    }

    /**
     *
     */
    public function testProtectedApiParseConnectorMessage_ParsesConnectorMessage()
    {
        $model  = $this->createConnector();
        $result = $this->callProtectedMethod($model, 'parseConnectorMessage', [ "from|id|type|msg" ]);

        $this->assertSame([ 'id', 'type', 'msg' ], $result);
    }

    /**
     *
     */
    public function testProtectedApiPrepareBinderMessage_PreparesBinderMessage()
    {
        $model = $this->createBinder();
        $this->setProtectedProperty($model, 'id', 'model');
        $result = $this->callProtectedMethod($model, 'prepareBinderMessage', [ 'id', 'type' ]);

        $this->assertSame('id|model|type', $result);
    }

    /**
     *
     */
    public function testProtectedApiPrepareConnectorMessage_PreparesConnectorMessage()
    {
        $model = $this->createConnector();
        $this->setProtectedProperty($model, 'id', 'model');
        $result = $this->callProtectedMethod($model, 'prepareConnectorMessage', [ 'id', 'type' ]);

        $this->assertSame('id|model|type', $result);
    }

    /**
     *
     */
    public function testProtectedApiCreateBinder_CreatesBinder()
    {
        $model = $this->createConnector();
        $result = $this->callProtectedMethod($model, 'createBinder');

        $this->assertInstanceOf(SocketListenerInterface::class, $result);

        $result->close();
    }

    /**
     *
     */
    public function testProtectedApiCreateBinder_CreatesConnector()
    {
        $model = $this->createConnector();
        $result = $this->callProtectedMethod($model, 'createBinder');

        $this->assertInstanceOf(SocketListenerInterface::class, $result);

        $result->close();
    }

    /**
     * @param mixed[] $params
     * @param string[]|null $methods
     * @return Socket|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createConnector($params = [], $methods = [])
    {
        $params['type'] = Channel::CONNECTOR;
        return $this->createModel($params, $methods);
    }

    /**
     * @param mixed[] $params
     * @param string[]|null $methods
     * @return Socket|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createBinder($params = [], $methods = [])
    {
        $params['type'] = Channel::BINDER;
        return $this->createModel($params, $methods);
    }

    /**
     * @param mixed[] $params
     * @param string[]|null $methods
     * @return Socket|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createModel($params = [], $methods = null)
    {
        $params = array_merge(
            [
                'id'        => 'model',
                'endpoint'  => 'tcp://127.0.0.1:2080',
                'type'      => $params['type'],
                'host'      => 'model',

            ],
            $params
        );

        $loop  = $this->getMock(Loop::class, $methods, [], '', false);
        $model = $this->getMock(Socket::class, $methods, [ $loop, $params ]);

        $this->model = $model;

        return $model;
    }

    /**
     * @param string[]|null $methods
     * @return Loop|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createLoop($methods = null)
    {
        $mock = $this->getMock(Loop::class, $methods, [], '', false);

        $this->setProtectedProperty($this->model, 'loop', $mock);

        return $mock;
    }
}
