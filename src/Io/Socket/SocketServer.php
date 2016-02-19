<?php
namespace Kraken\Io\Socket;

//use Ratchet\MessageComponentInterface;
//use React\EventLoop\LoopInterface;
//use React\Socket\ServerInterface;
//use React\EventLoop\Factory as LoopFactory;
//use React\Socket\Server as Reactor;
use Kraken\Io\ServerComponentInterface;
use Kraken\Ipc\Socket\SocketInterface;
use Kraken\Ipc\Socket\SocketServerInterface;
use Kraken\Loop\LoopInterface;

/**
 * Creates an open-ended socket to listen on a port for incoming connections.
 * Events are delegated through this to attached applications
 */
class SocketServer
{
    /**
     * @var ServerComponentInterface
     */
    protected $component;

    /**
     * @var SocketServerInterface
     */
    protected $socket;

    /**
     * @var LoopInterface
     */
    protected $loop;

    /**
     * Array of React event handlers
     * @var \SplFixedArray
     */
    protected $handlers;

    /**
     * @param ServerComponentInterface $component
     * @param SocketServerInterface $socket
     * @param LoopInterface $loop
     */
    public function __construct(ServerComponentInterface $component, SocketServerInterface $socket, LoopInterface $loop)
    {
        $this->component = $component;
        $this->socket = $socket;
        $this->loop = $loop;

        $socket->on('connect', [ $this, 'handleConnect' ]);

//        $this->handlers = new \SplFixedArray(3);
//        $this->handlers[0] = array($this, 'handleData');
//        $this->handlers[1] = array($this, 'handleEnd');
//        $this->handlers[2] = array($this, 'handleError');
    }

//    /**
//     * @param  \Ratchet\MessageComponentInterface $component The application that I/O will call when events are received
//     * @param  int                                $port      The port to server sockets on
//     * @param  string                             $address   The address to receive sockets on (0.0.0.0 means receive connections from any)
//     * @return IoServer
//     */
//    public static function factory(MessageComponentInterface $component, $port = 80, $address = '0.0.0.0') {
//        $loop   = LoopFactory::create();
//        $socket = new Reactor($loop);
//        $socket->listen($port, $address);
//
//        return new static($component, $socket, $loop);
//    }

    /**
     * Triggered when a new connection is received from SocketServer.
     *
     * @param SocketInterface $conn
     */
    public function handleConnect($conn)
    {
        $conn->decor = new IoConnection($conn);

        $conn->decor->resourceId    = (int)$conn->stream;
        $conn->decor->remoteAddress = $conn->getRemoteAddress();

        $this->app->onOpen($conn->decor);

        $conn->on('data', $this->handlers[0]);
        $conn->on('end', $this->handlers[1]);
        $conn->on('error', $this->handlers[2]);
    }

    /**
     * Data has been received from React
     * @param string                            $data
     * @param \React\Socket\ConnectionInterface $conn
     */
    public function handleData($data, $conn) {
        try {
            $this->app->onMessage($conn->decor, $data);
        } catch (\Exception $e) {
            $this->handleError($e, $conn);
        }
    }

    /**
     * A connection has been closed by React
     * @param \React\Socket\ConnectionInterface $conn
     */
    public function handleEnd($conn) {
        try {
            $this->app->onClose($conn->decor);
        } catch (\Exception $e) {
            $this->handleError($e, $conn);
        }

        unset($conn->decor);
    }

    /**
     * An error has occurred, let the listening application know
     * @param \Exception                        $e
     * @param \React\Socket\ConnectionInterface $conn
     */
    public function handleError(\Exception $e, $conn) {
        $this->app->onError($conn->decor, $e);
    }
}
