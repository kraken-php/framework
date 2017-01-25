<?php

namespace Kraken\Ipc\Socket;

use Kraken\Event\BaseEventEmitter;
use Kraken\Throwable\Exception\Runtime\ReadException;
use Kraken\Throwable\Exception\Logic\InstantiationException;
use Kraken\Throwable\Exception\LogicException;
use Kraken\Loop\LoopAwareTrait;
use Kraken\Loop\LoopInterface;
use Error;
use Exception;

class SocketListener extends BaseEventEmitter implements SocketListenerInterface
{
    use LoopAwareTrait;

    /**
     * @var int
     */
    const DEFAULT_BACKLOG = 1024;

    /**
     * @var resource
     */
    protected $socket;

    /**
     * @var bool
     */
    protected $open;

    /**
     * @var bool
     */
    protected $paused;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var string|resource
     */
    protected $endpoint;

    /**
     * @param string|resource $endpointOrResource
     * @param LoopInterface $loop
     * @param mixed[] $config
     * @throws InstantiationException
     */
    public function __construct($endpointOrResource, LoopInterface $loop, $config = ['ssl'=>false])
    {
        $this->endpoint = $endpointOrResource;
        $this->socket = null;
        $this->loop = $loop;
        $this->open = false;
        $this->paused = true;
        $this->config = $config;
    }

    /**
     *
     */
    public function __destruct()
    {
        $this->handleClose();

        parent::__destruct();

        unset($this->socket);
        unset($this->loop);
        unset($this->open);
        unset($this->paused);
        unset($this->config);
        unset($this->endpoint);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function start()
    {
        if ($this->isOpen())
        {
            return ;
        }

        try
        {
            if (!is_resource($this->endpoint))
            {
                $this->socket = $this->createServer($this->endpoint, $this->config);
            }
            else
            {
                $this->socket = &$this->endpoint;

                if ($this->config['ssl'] === true)
                {
                    stream_context_set_option($this->socket, 'ssl', 'verify_peer', false);
                    stream_context_set_option($this->socket, 'ssl', 'allow_self_signed', true);
                    stream_context_set_option($this->socket,'ssl','verify_peer_name',false);
                    stream_context_set_option($this->socket, 'ssl', 'local_cert', $this->config['local_cert']);
                    stream_context_set_option($this->socket, 'ssl', 'local_pk', $this->config['local_pk']);
                    stream_context_set_option($this->socket, 'ssl', 'passphrase', $this->config['passphrase']);
                }
            }

            $this->resume();
            $this->open = true;
        }
        catch (Error $ex)
        {
            throw new InstantiationException('SocketServer could not be created.', $ex);
        }
        catch (Exception $ex)
        {
            throw new InstantiationException('SocketServer could not be created.', $ex);
        }
    }

    /**
     * @override
     * @inheritDoc
     */
    public function stop()
    {
        $this->close();
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getLocalEndpoint()
    {
        return $this->parseEndpoint();
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getLocalAddress()
    {
        $endpoint = explode('://', $this->getLocalEndpoint(), 2);

        return isset($endpoint[1]) ? $endpoint[1] : $endpoint[0];
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getLocalHost()
    {
        $address = explode(':', $this->getLocalAddress(), 2);

        return $address[0];
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getLocalPort()
    {
        $address = explode(':', $this->getLocalAddress(), 2);

        return isset($address[1]) ? $address[1] : '';
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getLocalTransport()
    {
        $endpoint = explode('://', $this->getLocalEndpoint(), 2);

        return isset($endpoint[0])?$endpoint[0]:'';
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getResource()
    {
        return $this->socket;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getResourceId()
    {
        return (int) $this->socket;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getMetadata()
    {
        if ($this->isOpen())
        {
            return stream_get_meta_data($this->socket);
        }
        return [];
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getStreamType()
    {
        $data = $this->getMetadata();

        return isset($data['stream_type']) ? $data['stream_type'] : 'undefined';
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getWrapperType()
    {
        $data = $this->getMetadata();

        return isset($data['wrapper_type']) ? $data['wrapper_type'] : 'undefined';
    }

    /**
     * @override
     * @inheritDoc
     */
    public function isOpen()
    {
        return $this->open;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function isPaused()
    {
        return $this->paused;
    }

    /**
     * @internal
     * @return bool
     */
    public function isEncrypt()
    {
        return $this->encrypt && true;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function close()
    {
        if (!$this->isOpen())
        {
            return;
        }

        $this->open = false;

        $this->emit('close', [ $this ]);
        $this->handleClose();
        $this->emit('done', [ $this ]);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function pause()
    {
        if (!$this->paused)
        {
            $this->paused = true;

            if (isset($this->loop))
            {
                $this->loop->removeReadStream($this->socket);
            }
        }
    }

    /**
     * @override
     * @inheritDoc
     */
    public function resume()
    {
        if ($this->paused)
        {
            $this->paused = false;

            if (isset($this->loop))
            {
                $this->loop->addReadStream($this->socket, [ $this, 'handleConnect' ]);
            }
        }
    }

    /**
     * Create the server resource.
     *
     * This method creates server resource for socket connections.
     *
     * @param string $endpoint
     * @param mixed[] $config
     * @return resource
     * @throws LogicException
     */
    protected function createServer($endpoint, $config = [])
    {
        if (stripos($endpoint, 'unix://') !== false)
        {
            if ($endpoint[7] === DIRECTORY_SEPARATOR)
            {
                $path = substr($endpoint, 7);
            }
            else
            {
                $path = getcwd() . DIRECTORY_SEPARATOR . substr($endpoint, 7);
                $endpoint = 'unix://' . $path;
            }

            if (file_exists($path))
            {
                unlink($path);
            }
        }

        $ssl = (bool) ((isset($config['ssl'])) ? $config['ssl'] : false);
        $backlog = (int) (isset($config['backlog']) ? $config['backlog'] : self::DEFAULT_BACKLOG);
        $reuseaddr = (bool) (isset($config['reuseaddr']) ? $config['reuseaddr'] : false);
        $reuseport = (bool) (isset($config['reuseport']) ? $config['reuseport'] : false);

        $context['socket'] = [
            'bindto' => $endpoint,
            'backlog' => $backlog,
            'ipv6_v6only' => true,
            'so_reuseaddr' => $reuseaddr,
            'so_reuseport' => $reuseport,
        ];

        if($ssl) {
            $context['ssl'] = [
                'verify_peer'=>false,
                'verify_peer_name' => false,
                'allow_self_signed'=> true,
                'local_cert' => $config['local_cert'],
                'local_pk' => $config['local_pk'],
                'passphrase' => $config['passphrase'],
            ];

        }

        $bitmask = STREAM_SERVER_BIND | STREAM_SERVER_LISTEN;

        $context = stream_context_create($context);

        // Error reporting suppressed since stream_socket_server() emits an E_WARNING on failure.
        $socket = stream_socket_server(
            $endpoint,
            $errno,
            $errstr,
            $bitmask,
            $context
        );

        if (!$socket || $errno)
        {
            throw new LogicException(
                sprintf('Could not bind socket [%s] because of error [%d; %s]', $endpoint, $errno, $errstr)
            );
        }

        return $socket;
    }

    /**
     * Create the client resource.
     *
     * This method creates client resource for socket connections.
     *
     * @param resource $resource
     * @return SocketInterface
     */
    protected function createClient($resource)
    {
        return new Socket($resource, $this->loop);
    }

    /**
     * Handle the new connection.
     *
     * @internal
     */
    public function handleConnect()
    {
        $newSocket = @stream_socket_accept($this->socket);

        if ($newSocket === false)
        {
            $this->emit('error', [ $this, new ReadException('Socket could not accept new connection.') ]);
            return;
        }

        stream_set_blocking($newSocket, 0);

        $client = $this->createClient($newSocket);

        $this->emit('connect', [ $this, $client ]);
    }

    /**
     * Handle closing event.
     *
     * @internal
     */
    public function handleClose()
    {
        $this->pause();

        if (is_resource($this->socket))
        {
            if ($this->getStreamType() === Socket::TYPE_UNIX)
            {
                $path = substr($this->parseEndpoint(), 7);
                unlink($path);
            }
            fclose($this->socket);
        }
    }

    /**
     * @return string
     */
    private function parseEndpoint()
    {
        if ($this->isOpen())
        {
            $name = stream_socket_get_name($this->socket, false);
            $type = $this->getStreamType();

            switch ($type)
            {
                case Socket::TYPE_UNIX:
                    $transport = 'unix://';
                    $endpoint = $transport . $name;
                    break;

                case Socket::TYPE_TCP:
                    $transport = 'tcp://';
                    if (substr_count($name, ':') > 1)
                    {
                        $parts = explode(':', $name);
                        $count = count($parts);
                        $port = $parts[$count - 1];
                        unset($parts[$count - 1]);
                        $endpoint = $transport.'[' . implode(':', $parts) . ']:' . $port;
                    }
                    else
                    {
                        $endpoint = $transport . $name;
                    }
                    break;

                case Socket::TYPE_UDP:
                    $transport = 'udp://';
                    $endpoint = $transport . $name;
                    break;

                default:
                    $endpoint = '';
            }

            return $endpoint;
        }
        else
        {
            return is_string($this->endpoint)?$this->endpoint:'';
        }

    }
}
