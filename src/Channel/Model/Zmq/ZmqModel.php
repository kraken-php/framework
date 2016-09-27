<?php

namespace Kraken\Channel\Model\Zmq;

use Kraken\Channel\Channel;
use Kraken\Channel\ChannelModelInterface;
use Kraken\Channel\Model\Zmq\Connection\Connection;
use Kraken\Channel\Model\Zmq\Connection\ConnectionPool;
use Kraken\Channel\Model\Zmq\Buffer\Buffer;
use Kraken\Event\BaseEventEmitter;
use Kraken\Ipc\Zmq\ZmqContext;
use Kraken\Ipc\Zmq\ZmqSocket;
use Kraken\Loop\LoopInterface;
use Kraken\Loop\Timer\TimerInterface;
use Kraken\Throwable\Exception\Runtime\ExecutionException;

abstract class ZmqModel extends BaseEventEmitter implements ChannelModelInterface
{
    /**
     * @var int
     */
    const CONNECTOR = 2;

    /**
     * @var int
     */
    const BINDER = 1;

    /**
     * @var int
     */
    const SOCKET_UNDEFINED = 1;

    /**
     * @var int
     */
    const COMMAND_HEARTBEAT = 1;

    /**
     * @var int
     */
    const COMMAND_MESSAGE = 2;

    /**
     * @var int
     */
    const MODE_STANDARD = Channel::MODE_STANDARD;

    /**
     * @var int
     */
    const MODE_BUFFER_ONLINE = Channel::MODE_BUFFER_ONLINE;

    /**
     * @var int
     */
    const MODE_BUFFER_OFFLINE = Channel::MODE_BUFFER_OFFLINE;

    /**
     * @var int
     */
    const MODE_BUFFER = Channel::MODE_BUFFER;

    /**
     * @var LoopInterface
     */
    protected $loop;

    /**
     * @var ZmqContext
     */
    protected $context;

    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $endpoint;

    /**
     * @var int
     */
    protected $type;

    /**
     * @var string[]
     */
    protected $hosts;

    /**
     * @var string[]
     */
    protected $flags;

    /**
     * @var mixed[]
     */
    protected $options;

    /**
     * @var bool
     */
    protected $isConnected;

    /**
     * @var string
     */
    protected $pendingOperation;

    /**
     * @var callable
     */
    protected $connectCallback;

    /**
     * @var callable
     */
    protected $disconnectCallback;

    /**
     * @var ZmqSocket
     */
    public $socket;

    /**
     * @var Buffer
     */
    protected $buffer;

    /**
     * @var ConnectionPool
     */
    protected $connectionPool;

    /**
     * @var TimerInterface
     */
    private $hTimer;

    /**
     * @var TimerInterface
     */
    private $rTimer;

    /**
     * @param LoopInterface $loop
     * @param string[] $params
     */
    public function __construct(LoopInterface $loop, $params)
    {
        $id         = $params['id'];
        $endpoint   = $params['endpoint'];
        $type       = $params['type'];
        $hosts      = $params['host'];

        $flags = [
            'enableHeartbeat'       => isset($params['enableHeartbeat']) ? $params['enableHeartbeat'] : true,
            'enableBuffering'       => isset($params['enableBuffering']) ? $params['enableBuffering'] : true,
            'enableTimeRegister'    => isset($params['enableTimeRegister']) ? $params['enableTimeRegister'] : true
        ];

        $options = [
            'bufferSize'            => isset($params['bufferSize']) ? (int)$params['bufferSize'] : 0,
            'bufferTimeout'         => isset($params['bufferTimeout']) ? (int)$params['bufferTimeout'] : 0,
            'heartbeatInterval'     => isset($params['heartbeatInterval']) ? (int)$params['heartbeatInterval'] : 200,
            'heartbeatKeepalive'    => isset($params['heartbeatKeepalive']) ? (int)$params['heartbeatKeepalive'] : 1000,
            'timeRegisterInterval'  => isset($params['timeRegisterInterval']) ? (int)$params['timeRegisterInterval'] : 400
        ];

        $this->loop = $loop;
        $this->context = new ZmqContext($this->loop);
        $this->id = $id;
        $this->endpoint = $endpoint;
        $this->type = $type;
        $this->hosts = is_array($hosts) ? $hosts : [ $hosts ];
        $this->flags = $flags;
        $this->options = $options;
        $this->isConnected = false;
        $this->hTimer = null;
        $this->rTimer = null;

        $this->connectCallback = $this->getSocketConnectorType($this->type);
        $this->disconnectCallback = $this->getSocketDisconnectorType($this->type);
        $this->socket = $this->getSocket();
        $this->buffer = $this->getBuffer();
        $this->connectionPool = $this->getConnectionPool();

        $this->setEventListener('messages', [ $this, 'onMessages' ]);
    }

    /**
     *
     */
    public function __destruct()
    {
        $this->stop();

        $this->removeEventListener('messages', [ $this, 'onMessages' ]);

        unset($this->context);
        unset($this->id);
        unset($this->endpoint);
        unset($this->type);
        unset($this->hosts);
        unset($this->flags);
        unset($this->options);
        unset($this->isConnected);
        unset($this->hTimer);
        unset($this->rTimer);

        unset($this->connectCallback);
        unset($this->disconnectCallback);
        unset($this->socket);
        unset($this->buffer);
        unset($this->connectionPool);
        unset($this->loop);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function start($blockEvent = false)
    {
        if ($this->isStarted())
        {
            return false;
        }

        $connect = $this->connectCallback;
        if (!$this->socket->$connect($this->endpoint))
        {
            $this->emit('error', [ new ExecutionException('socket not connected.') ]);
            return false;
        }

        $this->stopHeartbeat();
        $this->stopTimeRegister();

        $this->isConnected = true;

        $this->startHeartbeat();
        $this->startTimeRegister();

        $this->connectionPool->erase();
        $this->buffer->send();

        if (!$blockEvent)
        {
            $this->emit('start');
        }

        return true;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function stop($blockEvent = false)
    {
        if (!$this->isStarted())
        {
            return false;
        }

        $this->stopHeartbeat();
        $this->stopTimeRegister();

        $disconnect = $this->disconnectCallback;
        $this->socket->$disconnect($this->endpoint);

        $this->isConnected = false;

        if (!$blockEvent)
        {
            $this->emit('stop');
        }

        return true;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function unicast($id, $message, $flags = self::MODE_STANDARD)
    {
        $status = $this->sendMessage($id, self::COMMAND_MESSAGE, $message, $flags);

        $this->emit('send', [ $id, (array) $message ]);

        return $status;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function broadcast($message)
    {
        $conns = $this->getConnected();
        $statuses = [];

        foreach ($conns as $conn)
        {
            $statuses[] = $this->sendMessage($conn, self::COMMAND_MESSAGE, $message, self::MODE_STANDARD);
        }

        foreach ($conns as $conn)
        {
            $this->emit('send', [ $conn, (array) $message ]);
        }

        return $statuses;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function isStarted()
    {
        return $this->isConnected;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function isStopped()
    {
        return !$this->isConnected;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function isConnected($id)
    {
        return $this->connectionPool->validateConnection($id);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getConnected()
    {
        return $this->connectionPool->getConnected();
    }

    /**
     * Set connection statically to be marked as online until specific timestamp.
     *
     * @param string $id
     * @param float $until
     */
    public function markConnectionOnline($id, $until)
    {
        $this->connectionPool->setConnectionProperty($id, 'timestampIn', $until);
    }

    /**
     * Set connection statically to be marked always as online.
     *
     * @param string $id
     */
    public function markConnectionPersistent($id)
    {
        $this->connectionPool->setConnectionProperty($id, 'timestampIn', 0);
    }

    /**
     * @return int
     */
    abstract protected function getSocketType();

    /**
     * @param string[] $multipartMessage
     * @return string[]
     */
    abstract protected function parseBinderMessage($multipartMessage);

    /**
     * @param string[] $multipartMessage
     * @return string[]
     */
    abstract protected function parseConnectorMessage($multipartMessage);

    /**
     * @param string $id
     * @param string $type
     * @return string[]
     */
    abstract protected function prepareBinderMessage($id, $type);

    /**
     * @param string $id
     * @param string $type
     * @return string[]
     */
    abstract protected function prepareConnectorMessage($id, $type);

    /**
     * @return ZmqSocket
     */
    protected function getSocket()
    {
        $socket = $this->context->getSocket($this->getSocketType());

        $socket->setSockOpt(\ZMQ::SOCKOPT_IDENTITY, $this->id);
//        $socket->setSockOpt(\ZMQ::SOCKOPT_SNDHWM, $this->options['bufferSize']);
//        $socket->setSockOpt(\ZMQ::SOCKOPT_RCVHWM, $this->options['bufferSize']);
        $socket->setSockOpt(\ZMQ::SOCKOPT_LINGER, $this->options['bufferTimeout']);

        return $socket;
    }

    /**
     * @return Buffer
     */
    protected function getBuffer()
    {
        return new Buffer($this->socket, $this->options['bufferSize']);
    }

    /**
     * @return ConnectionPool
     */
    protected function getConnectionPool()
    {
        return new ConnectionPool($this->options['heartbeatKeepalive'], $this->options['heartbeatInterval']);
    }

    /**
     * @param string $event
     * @param callable $callback
     */
    protected function setEventListener($event, callable $callback)
    {
        $this->socket->on($event, $callback);
    }

    /**
     * @param string $event
     * @param callable $callback
     */
    protected function removeEventListener($event, callable $callback)
    {
        $this->socket->removeListener($event, $callback);
    }

    /**
     * @param string[] $argv
     */
    public function onMessages($argv)
    {
        if ($this->type === self::BINDER)
        {
            list($id, $type, $message) = $this->parseBinderMessage($argv);
        }
        else if ($this->type === self::CONNECTOR)
        {
            list($id, $type, $message) = $this->parseConnectorMessage($argv);
        }
        else
        {
            return;
        }

        $conn = new Connection($id);

        switch ($type)
        {
            case self::COMMAND_HEARTBEAT:
                $this->onRecvHeartbeat($conn);
                break;

            case self::COMMAND_MESSAGE:
                $this->onRecvMessage($conn, $message);
                break;

            default:
                return;
        }
    }

    /**
     * @param int $type
     * @return int string
     */
    private function getSocketConnectorType($type)
    {
        switch ($type)
        {
            case self::CONNECTOR:
                return 'connect';
            case self::BINDER:
                return 'bind';
            default:
                return 'fail';
        }
    }

    /**
     * @param int $type
     * @return int string
     */
    private function getSocketDisconnectorType($type)
    {
        switch ($type)
        {
            case self::CONNECTOR:
                return 'disconnect';
            case self::BINDER:
                return 'unbind';
            default:
                return 'fail';
        }
    }

    /**
     * @param Connection $conn
     * @param string[] $message
     */
    private function onRecvMessage(Connection $conn, $message)
    {
        $this->recvMessage($conn, $message);
        $this->recvHeartbeat($conn);
    }

    /**
     * @param Connection $conn
     */
    private function onRecvHeartbeat(Connection $conn)
    {
        $this->recvHeartbeat($conn);
    }

    /**
     * @param Connection $conn
     * @param $message
     * @return mixed
     */
    private function recvMessage(Connection $conn, $message)
    {
        $this->emit('recv', [ $conn->id, $message ]);
    }

    /**
     * @param Connection $conn
     */
    private function recvHeartbeat(Connection $conn)
    {
        if ($this->flags['enableHeartbeat'] !== true)
        {
            return;
        }

        if ($this->connectionPool->setConnection($conn->id))
        {
            $this->emit('connect', [ $conn->getId() ]);
        }

        if ($this->type === self::BINDER)
        {
            $this->heartbeat($conn->id);
        }
    }

    /**
     *
     */
    private function fail()
    {
        return false;
    }

    /**
     * @param string $id
     * @return bool
     */
    private function heartbeat($id)
    {
        if ($this->connectionPool->isHeartbeatNeeded($id) === true)
        {
            return $this->sendMessage($id, self::COMMAND_HEARTBEAT);
        }

        return false;
    }

    /**
     *
     */
    private function startHeartbeat()
    {
        if ($this->hTimer === null && $this->flags['enableHeartbeat'])
        {
            $proxy = $this;
            $this->hTimer = $this->loop->addPeriodicTimer(($this->options['heartbeatInterval']/1000), function() use($proxy) {

                if ($proxy->type === self::CONNECTOR)
                {
                    foreach ($proxy->hosts as $hostid)
                    {
                        $proxy->heartbeat($hostid);
                    }
                }

                $this->clearConnectionPool();
            });
        }
    }

    /**
     *
     */
    private function clearConnectionPool()
    {
        $deleted = $this->connectionPool->removeInvalid();

        foreach ($deleted as $deletedid)
        {
            $this->emit('disconnect', [ $deletedid ]);
        }
    }

    /**
     *
     */
    private function stopHeartbeat()
    {
        if ($this->hTimer !== null)
        {
            $this->hTimer->cancel();
            $this->hTimer = null;
        }
    }

    /**
     * @param string $id
     * @param string $type
     * @param mixed $message
     * @return null|string[]
     */
    private function getFrame($id, $type, $message)
    {
        if ($this->type === self::BINDER)
        {
            $frame = $this->prepareBinderMessage($id, $type);
        }
        else if ($this->type === self::CONNECTOR)
        {
            $frame = $this->prepareConnectorMessage($id, $type);
        }
        else
        {
            return null;
        }

        if ($message !== null)
        {
            if (is_object($message))
            {
                return null;
            }
            else if (!is_array($message))
            {
                $message = [ $message ];
            }

            $frame = array_merge($frame, $message);
        }

        return $frame;
    }

    /**
     * @param string $id
     * @param string $type
     * @param mixed $message
     * @param int $flags
     * @return bool
     */
    private function sendMessage($id, $type, $message = null, $flags = self::MODE_STANDARD)
    {
        if (($frame = $this->getFrame($id, $type, $message)) === null)
        {
            return false;
        }

        $isConnected = $this->isStarted();

        if (!$isConnected)
        {
            if ($this->flags['enableBuffering'] === true && ($flags & self::MODE_BUFFER_OFFLINE) === self::MODE_BUFFER_OFFLINE)
            {
                return $this->buffer->add($frame);
            }
        }
        else if ($type === self::COMMAND_HEARTBEAT)
        {
            if ($this->socket->closed === false && $this->socket->send($frame))
            {
                $this->connectionPool->registerHeartbeat($id);
                return true;
            }
        }
        else if (($this->flags['enableHeartbeat'] === false) || ($this->flags['enableBuffering'] === true && ($flags & self::MODE_BUFFER_ONLINE) === self::MODE_BUFFER_ONLINE) || ($this->connectionPool->validateConnection($id) === true))
        {
            $this->socket->send($frame);
            $this->connectionPool->registerHeartbeat($id);
            return true;
        }

        return false;
    }

    /**
     * Start time register.
     *
     * Time register purpose is to cyclically increase timestamp representing last active tick of event loop. This method
     * allows model to not mark external sockets wrongly as offline because of its own heavy load.
     */
    private function startTimeRegister()
    {
        if ($this->rTimer === null && $this->flags['enableHeartbeat'] === true && $this->flags['enableTimeRegister'] === true)
        {
            $proxy = $this;
            $this->rTimer = $this->loop->addPeriodicTimer(($this->options['timeRegisterInterval']/1000), function() use($proxy) {
                $now = round(microtime(true)*1000);
                $proxy->connectionPool->setNow(function() use($now) {
                    return $now;
                });
            });
        }
    }

    /**
     * Stop time register.
     *
     * @see ZmqModel::startTimeRegister
     */
    private function stopTimeRegister()
    {
        if ($this->rTimer !== null)
        {
            $this->rTimer->cancel();
            $this->rTimer = null;
            $this->connectionPool->resetNow();
        }
    }
}
