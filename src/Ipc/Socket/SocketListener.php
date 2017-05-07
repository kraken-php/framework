<?php

namespace Kraken\Ipc\Socket;

use Kraken\Event\BaseEventEmitter;
use Kraken\Throwable\Exception\Runtime\ExecutionException;
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
     * @var mixed
     */
    const CONFIG_DEFAULT_SSL = false;

    /**
     * @var mixed
     */
    const CONFIG_DEFAULT_SSL_METHOD = STREAM_CRYPTO_METHOD_TLSv1_2_SERVER;

    /**
     * @var mixed
     */
    const CONFIG_DEFAULT_SSL_NAME = '';

    /**
     * @var mixed
     */
    const CONFIG_DEFAULT_SSL_VERIFY_SIGN = false;

    /**
     * @var mixed
     */
    const CONFIG_DEFAULT_SSL_VERIFY_PEER = false;

    /**
     * @var mixed
     */
    const CONFIG_DEFAULT_SSL_VERIFY_DEPTH = 10;

    /**
     * @var resource
     */
    protected $socket;

    /**
     * @var bool
     */
    protected $started;

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
    public function __construct($endpointOrResource, LoopInterface $loop, $config = [])
    {
        $this->configure($config);
        $this->endpoint = $endpointOrResource;
        $this->socket = null;
        $this->loop = $loop;
        $this->started = false;
        $this->paused = true;
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
        unset($this->started);
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
            return;
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
            }

            $this->resume();
            $this->started = true;
        }
        catch (Error $ex)
        {
            throw new InstantiationException('SocketListener could not be created.', $ex);
        }
        catch (Exception $ex)
        {
            throw new InstantiationException('SocketListener could not be created.', $ex);
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
    public function getLocalProtocol()
    {
        $endpoint = explode('://', $this->getLocalEndpoint(), 2);

        return isset($endpoint[0]) ? $endpoint[0]:'';
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
        return $this->started;
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
     * @override
     * @inheritDoc
     */
    public function isEncrypted()
    {
        return isset($this->config['ssl']) && $this->config['ssl'] === true;
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

        $this->started = false;

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

        $ssl = $this->config['ssl'];
        $name = $this->config['ssl_name'];
        $verifySign = $this->config['ssl_verify_sign'];
        $verifyPeer = $this->config['ssl_verify_peer'];
        $verifyDepth = $this->config['ssl_verify_depth'];

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

        $context['ssl'] = [
            'allow_self_signed' => !$verifySign,
            'verify_peer' => $verifyPeer,
            'verify_peer_name' => $verifyPeer,
            'verify_depth' => $verifyDepth,
            'disable_compression' => true,
            'SNI_enabled' => $name !== '',
            'SNI_server_name' => $name,
            'peer_name' => $name,
        ];

        if ($ssl && isset($config['ssl_cert']))
        {
            $context['ssl']['local_cert'] = $config['ssl_cert'];
        }

        if ($ssl && isset($config['ssl_key']))
        {
            $context['ssl']['local_pk'] = $config['ssl_key'];
        }

        if ($ssl && isset($config['ssl_secret']))
        {
            $context['ssl']['passphrase'] = $config['ssl_secret'];
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
     * @param string[] $config
     * @return SocketInterface
     */
    protected function createClient($resource, $config = [])
    {
        return new Socket($resource, $this->loop, $config);
    }

    /**
     * Handle the new connection.
     *
     * @internal
     */
    public function handleConnect()
    {
        $socket = @stream_socket_accept($this->socket);

        if ($socket === false)
        {
            $this->emit('error', [ $this, new ReadException('Socket could not accept new connection.') ]);
            return;
        }

        $client = null;
        $ex = null;

        try
        {
            $client = $this->createClient($socket, [
                'ssl' => $this->config['ssl'],
                'ssl_method' => $this->config['ssl_method'],
            ]);

            $this->emit('connect', [ $this, $client ]);
        }
        catch (Error $ex)
        {}
        catch (Exception $ex)
        {}

        if ($ex !== null)
        {
            $this->handleDisconnect($socket);
            $this->emit('error', [ $this, new ReadException('Socket could not wrap new connection!') ]);
        }
    }

    private function handleDisconnect($resource)
    {
        if (is_resource($resource))
        {
            // http://chat.stackoverflow.com/transcript/message/7727858#7727858
            stream_socket_shutdown($resource, STREAM_SHUT_RDWR);
            stream_set_blocking($resource, 0);
            fclose($resource);
        }
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
     * Configure socket.
     *
     * @param string[] $config
     */
    private function configure($config = [])
    {
        $this->config = $config;

        $this->configureVariable('ssl');
        $this->configureVariable('ssl_method');
        $this->configureVariable('ssl_name');
        $this->configureVariable('ssl_verify_sign');
        $this->configureVariable('ssl_verify_peer');
        $this->configureVariable('ssl_verify_depth');
    }

    /**
     * Configure static key
     *
     * @param $configKey
     */
    private function configureVariable($configKey)
    {
        $configStaticKey = 'CONFIG_DEFAULT_' . strtoupper($configKey);
        $this->config[$configKey] = isset($this->config[$configKey]) ? $this->config[$configKey] : constant("static::$configStaticKey");
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
            return is_string($this->endpoint) ? $this->endpoint : '';
        }
    }
}
