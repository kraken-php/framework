<?php

namespace Kraken\Channel\Model\Socket;

use Kraken\Channel\Model\Socket\Buffer\Buffer;
use Kraken\Channel\Model\Socket\Connection\Connection;
use Kraken\Channel\Model\Socket\Connection\ConnectionPool;
use Kraken\Channel\Channel;
use Kraken\Channel\ChannelModelInterface;
use Kraken\Event\BaseEventEmitter;
use Kraken\Ipc\Socket\SocketInterface;
use Kraken\Ipc\Socket\SocketListenerInterface;
use Kraken\Loop\Timer\TimerInterface;
use Kraken\Loop\LoopInterface;
use Kraken\Throwable\Exception\Runtime\ExecutionException;
use Error;
use Exception;

class Socket extends BaseEventEmitter implements ChannelModelInterface
{
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
    const SEND_STATUS_DROPPED = 0;

    /**
     * @var int
     */
    const SEND_STATUS_SUCCEEDED = 1;

    /**
     * @var int
     */
    const SEND_STATUS_BUFFERED = 2;

    /**
     * @var LoopInterface
     */
    protected $loop;

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
     * @var SocketInterface|SocketListenerInterface|null
     */
    protected $socket;

    /**
     * @var ConnectionPool
     */
    protected $connectionPool;

    /**
     * @var Buffer
     */
    protected $offlineBuffer;

    /**
     * @var Buffer
     */
    protected $onlineBuffer;

    /**
     * @var string[]
     */
    protected $frameBuffer;

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
            'enableHeartbeat'       => true,
            'enableBuffering'       => true,
            'enableTimeRegister'    => true
        ];

        $options = [
            'bufferSize'            => isset($params['bufferSize']) ? (int)$params['bufferSize'] : 0,
            'bufferTimeout'         => isset($params['bufferTimeout']) ? (int)$params['bufferTimeout'] : 0,
            'heartbeatInterval'     => isset($params['heartbeatInterval']) ? (int)$params['heartbeatInterval'] : 200,
            'heartbeatKeepalive'    => isset($params['heartbeatKeepalive']) ? (int)$params['heartbeatKeepalive'] : 1000,
            'timeRegisterInterval'  => isset($params['timeRegisterInterval']) ? (int)$params['timeRegisterInterval'] : 400
        ];

        $this->loop = $loop;
        $this->id = $id;
        $this->endpoint = $endpoint;
        $this->type = $type;
        $this->hosts = (array) $hosts;
        $this->flags = $flags;
        $this->options = $options;
        $this->isConnected = false;
        $this->hTimer = null;
        $this->rTimer = null;

        $this->socket = null;
        $this->offlineBuffer = $this->getBuffer();
        $this->onlineBuffer = $this->getBuffer();
        $this->frameBuffer = [];
        $this->connectionPool = $this->getConnectionPool();
    }

    /**
     *
     */
    public function __destruct()
    {
        $this->stop();

        unset($this->id);
        unset($this->endpoint);
        unset($this->type);
        unset($this->hosts);
        unset($this->flags);
        unset($this->options);
        unset($this->isConnected);
        unset($this->hTimer);
        unset($this->rTimer);

        unset($this->socket);
        unset($this->onfflinebuffer);
        unset($this->onlinebuffer);
        unset($this->frameBuffer);
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

        if (!$this->startConnection())
        {
            $this->emit('error', [ new ExecutionException('socket not connected.') ]);
            return false;
        }

        $this->stopHeartbeat();
        $this->stopTimeRegister();

        $this->isConnected = true;

        $this->startHeartbeat();
        $this->startTimeRegister();

        foreach ($messages = $this->offlineBuffer->pull() as $message)
        {
            $this->onlineBuffer->push($message[0], $message[1]);
        }

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

        if (!$this->stopConnection())
        {
            $this->emit('error', [ new ExecutionException('socket not disconnected.') ]);
        }

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
    public function unicast($id, $message, $flags = Channel::MODE_STANDARD)
    {
        $status = $this->sendMessage($id, self::COMMAND_MESSAGE, $message, $flags);

        if ($status === static::SEND_STATUS_SUCCEEDED)
        {
            $this->emit('send', [ $id, (array) $message ]);
        }

        return $status > 0;
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
            $statuses[] = $this->sendMessage($conn, self::COMMAND_MESSAGE, $message, Channel::MODE_STANDARD) > 0;
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
     * @param string $message
     * @return string[]
     */
    protected function parseBinderMessage($message)
    {
        $multipart = explode('|', $message, 4);

        $id = $multipart[1];
        $type = $multipart[2];
        $message = $multipart[3];

        return [ $id, $type, $message ];
    }

    /**
     * @param string $message
     * @return string[]
     */
    protected function parseConnectorMessage($message)
    {
        $multipart = explode('|', $message, 4);

        $id = $multipart[1];
        $type = $multipart[2];
        $message = $multipart[3];

        return [ $id, $type, $message ];
    }

    /**
     * @param string $id
     * @param string $type
     * @return string[]
     */
    protected function prepareBinderMessage($id, $type)
    {
        return $id . '|' . $this->id . '|' . $type;
    }

    /**
     * @param string $id
     * @param string $type
     * @return string[]
     */
    protected function prepareConnectorMessage($id, $type)
    {
        return $id . '|' . $this->id . '|' . $type;
    }

    /**
     * @return SocketListenerInterface
     */
    protected function createBinder()
    {
        $binder = $this;
        $socketListener = new \Kraken\Ipc\Socket\SocketListener($this->endpoint, $this->loop);

        $socketListener->on('connect', function(SocketListenerInterface $server, SocketInterface $client) use($binder) {
            $client->on('data', function(SocketInterface $client, $data) use($binder) {
                $binder->onData($client, $data);
            });
        });
        $socketListener->on('close', function() use($binder) {
            $binder->stop();
        });
        $socketListener->start();

        return $socketListener;
    }

    /**
     * @return SocketInterface
     */
    protected function createConnector()
    {
        $connector = $this;
        $loop = $this->loop;
        $socket = new \Kraken\Ipc\Socket\Socket($this->endpoint, $loop);

        $socket->on('data', function(SocketInterface $client, $data) use($connector) {
            $connector->onData($client, $data);
        });
        $socket->on('close', function() use($connector, $loop) {
            $connector->stop(true);
            $loop->addTimer(0.1, function() use($connector) {
                $connector->start(true);
            });
        });

        return $socket;
    }

    /**
     *
     */
    protected function destroyBinder()
    {
        $this->socket->removeListeners('connect');
        $this->socket->removeListeners('close');
    }

    /**
     *
     */
    protected function destroyConnector()
    {
        $this->socket->removeListeners('data');
        $this->socket->removeListeners('close');
    }

    /**
     * @param string $event
     * @param callable $callback
     */
    protected function setEventListener($event, callable $callback)
    {
        if ($this->socket !== null)
        {
            $this->socket->on($event, $callback);
        }
    }

    /**
     * @param string $event
     * @param callable $callback
     */
    protected function removeEventListener($event, callable $callback)
    {
        if ($this->socket !== null)
        {
            $this->socket->removeListener($event, $callback);
        }
    }

    /**
     * @return Buffer
     */
    protected function getBuffer()
    {
        return new Buffer($this->options['bufferSize']);
    }

    /**
     * @return ConnectionPool
     */
    protected function getConnectionPool()
    {
        return new ConnectionPool($this->options['heartbeatKeepalive'], $this->options['heartbeatInterval']);
    }

    /**
     * @param SocketInterface $client
     * @param string $data
     */
    public function onData(SocketInterface $client, $data)
    {
        $messages = [];
        $resID = $client->getResourceId();
        $buffer = '';

        if (isset($this->frameBuffer[$resID]))
        {
            $buffer = $this->frameBuffer[$resID];
            unset($this->frameBuffer[$resID]);
        }

        $buffer = preg_replace_callback(
            "#(.*?)\r\n#si",
            function($matches) use(&$messages) {
                $messages[] = $matches[1];
                return '';
            },
            $buffer . $data
        );

        if ($buffer !== '')
        {
            $this->frameBuffer[$resID] = $buffer;
            unset($buffer);
        }

        foreach ($messages as $message)
        {
            if ($message !== '')
            {
                $this->onMessage($client, $message);
            }
        }
    }

    /**
     * @param SocketInterface $client
     * @param string $message
     */
    private function onMessage(SocketInterface $client, $message)
    {
        if ($this->type === Channel::BINDER)
        {
            list($id, $type, $message) = $this->parseBinderMessage($message);
        }
        else if ($this->type === Channel::CONNECTOR)
        {
            list($id, $type, $message) = $this->parseConnectorMessage($message);
        }
        else
        {
            return;
        }

        $conn = new Connection($id, $client);
        $message = explode("\n", $message);

        switch ($type)
        {
            case self::COMMAND_HEARTBEAT: $this->onRecvHeartbeat($conn);         break;
            case self::COMMAND_MESSAGE:   $this->onRecvMessage($conn, $message); break;
            default: return;
        }
    }

    /**
     * @param Connection $conn
     * @param string[] $message
     */
    private function onRecvMessage(Connection $conn, $message)
    {
        $this->recvHeartbeat($conn);
        $this->recvMessage($conn, $message);

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
     * @param $message[]
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

        if ($this->connectionPool->setConnection($conn))
        {
            $this->emit('connect', [ $conn->id ]);
        }

        if ($this->type === Channel::BINDER)
        {
            $this->heartbeat($conn->id);
        }

        foreach ($messages = $this->onlineBuffer->pull($conn->id) as $message)
        {
            $this->unicast($message[0], $message[1]);
        }
    }

    /**
     * @return bool
     */
    private function startConnection()
    {
        if ($this->socket !== null)
        {
            return false;
        }

        $socket = null;
        $ex = null;

        try
        {
            switch ($this->type)
            {
                case Channel::CONNECTOR:
                    $socket = $this->createConnector();
                    break;

                case Channel::BINDER:
                    $socket = $this->createBinder();
                    break;

                default:
                    return false;
            }
        }
        catch (Error $ex)
        {}
        catch (Exception $ex)
        {}

        if ($ex !== null)
        {
            return false;
        }

        $this->socket = $socket;

        return true;
    }

    /**
     * @return bool
     */
    private function stopConnection()
    {
        if ($this->socket === null)
        {
            return false;
        }

        $socket = null;
        $ex = null;

        try
        {
            switch ($this->type)
            {
                case Channel::CONNECTOR: $this->destroyConnector(); break;
                case Channel::BINDER:    $this->destroyBinder();    break;
                default: return false;
            }
        }
        catch (Error $ex)
        {}
        catch (Exception $ex)
        {}

        if ($ex !== null)
        {
            return false;
        }

        $this->socket->close();
        unset($this->socket);
        $this->socket = null;

        return true;
    }

    /**
     * @param string $id
     * @param string $type
     * @param string $message
     * @return null|string
     */
    private function getFrame($id, $type, $message)
    {
        if ($this->type === Channel::BINDER)
        {
            $frame = $this->prepareBinderMessage($id, $type);
        }
        else if ($this->type === Channel::CONNECTOR)
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
            else if (is_array($message))
            {
                $message = implode("\n", $message);
            }

            $frame .= '|' . $message;
        }
        else
        {
            $frame .= '|';
        }

        return $frame;
    }

    /**
     * @param string $id
     * @param string $type
     * @param string|string[] $message
     * @param int $flags
     * @return bool
     */
    private function sendMessage($id, $type, $message = null, $flags = Channel::MODE_STANDARD)
    {
        if (($frame = $this->getFrame($id, $type, $message)) === null)
        {
            return static::SEND_STATUS_DROPPED;
        }

        $isConnected = $this->isStarted();

        if (!$isConnected && $this->flags['enableBuffering'] === true && ($flags & Channel::MODE_BUFFER_OFFLINE) === Channel::MODE_BUFFER_OFFLINE)
        {
            $frame  = $this->parseConnectorMessage($frame);
            $status = $this->offlineBuffer->push($id, $frame[2]);
            return $status ? static::SEND_STATUS_BUFFERED : static::SEND_STATUS_DROPPED;
        }
        else if ($type === self::COMMAND_HEARTBEAT)
        {
            if ($this->writeData($id, $frame . "\r\n"))
            {
                $this->connectionPool->registerHeartbeat($id);
                return static::SEND_STATUS_SUCCEEDED;
            }
        }
        else if ($this->flags['enableHeartbeat'] === false || $this->connectionPool->validateConnection($id) === true)
        {
            $this->writeData($id, $frame . "\r\n");
            $this->connectionPool->registerHeartbeat($id);
            return static::SEND_STATUS_SUCCEEDED;
        }
        else if ($this->flags['enableBuffering'] === true && ($flags & Channel::MODE_BUFFER_ONLINE) === Channel::MODE_BUFFER_ONLINE)
        {
            $frame  = $this->parseConnectorMessage($frame);
            $status = $this->onlineBuffer->push($id, $frame[2]);
            return $status ? static::SEND_STATUS_BUFFERED : static::SEND_STATUS_DROPPED;
        }

        return static::SEND_STATUS_DROPPED;
    }

    /**
     * @param string $id
     * @param string $data
     * @return bool
     */
    private function writeData($id, $data)
    {
        if ($this->socket === null)
        {
            return false;
        }

        try
        {
            if ($this->type === Channel::CONNECTOR)
            {
                return $this->socket->write($data);
            }

            if ($this->type === Channel::BINDER && $this->connectionPool->existsConnection($id))
            {
                return $this->connectionPool->getConnection($id)->getSocket()->write($data);
            }
        }
        catch (Error $ex)
        {
            return false;
        }
        catch (Exception $ex)
        {
            return false;
        }

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
            return $this->sendMessage($id, self::COMMAND_HEARTBEAT) > 0;
        }

        return false;
    }

    /**
     * Start heartbeat.
     *
     * Heartbeat mechanisms is used to identify online and offline sockets.
     */
    private function startHeartbeat()
    {
        if ($this->hTimer === null && $this->flags['enableHeartbeat'])
        {
            $this->clearConnectionPool();

            $proxy = $this;
            $this->hTimer = $this->loop->addPeriodicTimer(($this->options['heartbeatInterval']/1000), function() use($proxy) {

                if ($proxy->type === Channel::CONNECTOR)
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
     * Stop hearbeat.
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
     * Clear connection pool.
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
     * Start time register.
     *
     * Time register purpose is to cyclically increase timestamp representing last time of tick of event loop. This
     * method allows model to not mark external sockets wrongly as offline because of its own heavy load.
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
