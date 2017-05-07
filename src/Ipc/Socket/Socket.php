<?php

namespace Kraken\Ipc\Socket;

use Kraken\Loop\LoopInterface;
use Kraken\Stream\AsyncStream;
use Kraken\Throwable\Exception\Logic\InstantiationException;
use Kraken\Throwable\Exception\Runtime\ExecutionException;
use Kraken\Throwable\Exception\LogicException;
use Error;
use Exception;

class Socket extends AsyncStream implements SocketInterface
{
    /**
     * @var string
     */
    const TYPE_UNIX = 'unix_socket';

    /**
     * @var string
     */
    const TYPE_TCP = 'tcp_socket/ssl';

    /**
     * @var string
     */
    const TYPE_UDP = 'udp_socket';

    /**
     * @var string
     */
    const TYPE_UNKNOWN = 'Unknown';

    /**
     * @var int
     */
    const CRYPTO_TYPE_UNKNOWN = 0;

    /**
     * @var int
     */
    const CRYPTO_TYPE_SERVER = 1;

    /**
     * @var int
     */
    const CRYPTO_TYPE_CLIENT = 2;

    /**
     * @var mixed
     */
    const CONFIG_DEFAULT_SSL = false;

    /**
     * @var mixed
     */
    const CONFIG_DEFAULT_SSL_METHOD = STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT;

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
     * @var int
     */
    protected $crypto = 0;

    /**
     * @var int
     */
    protected $cryptoMethod = 0;

    /**
     * @var array
     */
    protected $config = [];

    /**
     * @var bool[]
     */
    private $cachedEndpoint = [];

    /**
     * @param string|resource $endpointOrResource
     * @param LoopInterface $loop
     * @param mixed[] $config
     * @throws InstantiationException
     */
    public function __construct($endpointOrResource, LoopInterface $loop, $config = [])
    {
        try
        {
            $this->configure($config);

            if (!is_resource($endpointOrResource))
            {
                $endpointOrResource = $this->createClient($endpointOrResource, $config);
            }

            parent::__construct($endpointOrResource, $loop);

            $this->read();
        }
        catch (Error $ex)
        {
            throw new InstantiationException('SocketClient could not be created.', $ex);
        }
        catch (Exception $ex)
        {
            throw new InstantiationException('SocketClient could not be created.', $ex);
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
        return $this->parseEndpoint(false);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getRemoteEndpoint()
    {
        return $this->parseEndpoint(true);
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

        return isset($endpoint[0]) ? $endpoint[0] : '';
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getRemoteAddress()
    {
        $endpoint = explode('://', $this->getRemoteEndpoint(), 2);

        return isset($endpoint[1]) ? $endpoint[1] : $endpoint[0];
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getRemoteHost()
    {
        $address = explode(':', $this->getRemoteAddress(), 2);

        return $address[0];
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getRemotePort()
    {
        $address = explode(':', $this->getRemoteAddress(), 2);

        return isset($address[1]) ? $address[1] : '';
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getRemoteProtocol()
    {
        $endpoint = explode('://', $this->getRemoteEndpoint(), 2);

        return isset($endpoint[0]) ? $endpoint[0] : '';
    }

    /**
     * @override
     * @inheritDoc
     */
    public function isEncrypted()
    {
        return $this->crypto !== 0;
    }

    /**
     * Create the client resource.
     *
     * This method creates client resource for socket connections.
     *
     * @param string $endpoint
     * @param mixed[] $config
     * @return resource
     * @throws LogicException
     */
    protected function createClient($endpoint, $config = [])
    {
        $ssl = $this->config['ssl'];
        $name = $this->config['ssl_name'];
        $verifySign = $this->config['ssl_verify_sign'];
        $verifyPeer = $this->config['ssl_verify_peer'];
        $verifyDepth = $this->config['ssl_verify_depth'];

        $context['socket'] = [
            'connect' => $endpoint
        ];

        $context['ssl'] = [
            'capture_peer_cert' => true,
            'capture_peer_chain' => true,
            'capture_peer_cert_chain' => true,
            'allow_self_signed' => !$verifySign,
            'verify_peer' => $verifyPeer,
            'verify_peer_name' => $verifyPeer,
            'verify_depth' => $verifyDepth,
            'SNI_enabled' => $name !== '',
            'SNI_server_name' => $name,
            'peer_name' => $name,
            'disable_compression' => true,
            'honor_cipher_order' => true,
        ];

        if ($ssl && isset($config['ssl_cafile']))
        {
            $context['ssl']['cafile'] = $config['ssl_cafile'];
        }

        if ($ssl && isset($config['ssl_capath']))
        {
            $context['ssl']['capath'] = $config['ssl_capath'];
        }

        $context = stream_context_create($context);
        // Error reporting suppressed since stream_socket_client() emits an E_WARNING on failure.
        $socket = @stream_socket_client(
            $endpoint,
            $errno,
            $errstr,
            0, // Timeout does not apply for async connect.
            STREAM_CLIENT_CONNECT | STREAM_CLIENT_ASYNC_CONNECT,
            $context
        );

        if (!$socket || $errno)
        {
            throw new LogicException(
                sprintf('Could not connect socket [%s] because of error [%d; %s]', $endpoint, $errno, $errstr)
            );
        }

        stream_set_blocking($socket, false);

        return $socket;
    }

    /**
     * Handle socket encryption.
     *
     * @internal
     */
    public function handleEncrypt()
    {
        $ex = null;

        try
        {
            if ($this->isEncrypted())
            {
                return;
            }

            $this->encrypt($this->config['ssl_method']);

            if ($this->isEncrypted())
            {
                $this->pause();
                $this->resume();
            }
        }
        catch (Error $ex)
        {}
        catch (Exception $ex)
        {}

        if ($ex !== null)
        {
            $this->close();
            $this->emit('error', [ $this, $ex ]);
        }
    }

    /**
     * @internal
     * @override
     * @inheritDoc
     */
    public function handleRead()
    {
        $ex = null;

        try
        {
            $data = fread($this->resource, $this->bufferSize);

            if ($data !== '' && $data !== false)
            {
                $this->emit('data', [ $this, $data ]);
            }

            if ($data === '' || $data === false || !is_resource($this->resource) || feof($this->resource))
            {
                $this->close();
            }
        }
        catch (Error $ex)
        {}
        catch (Exception $ex)
        {}

        if ($ex !== null)
        {
            $this->emit('error', [ $this, $ex ]);
        }
    }

    /**
     * @internal
     * @override
     * @inheritDoc
     */
    public function handleWrite()
    {
        $ex = null;

        try
        {
            parent::handleWrite();
        }
        catch (Error $ex)
        {}
        catch (Exception $ex)
        {}

        if ($ex !== null)
        {
            $this->emit('error', [ $this, $ex ]);
        }
    }

    /**
     * @internal
     * @override
     * @inheritDoc
     */
    public function handleClose()
    {
        $this->pause();

        if (is_resource($this->resource))
        {
            // http://chat.stackoverflow.com/transcript/message/7727858#7727858
            stream_socket_shutdown($this->resource, STREAM_SHUT_RDWR);
            stream_set_blocking($this->resource, 0);
            fclose($this->resource);
        }
    }

    /**
     * Get function that should be invoked on read event.
     *
     * @return callable
     */
    protected function getHandleReadFunction()
    {
        return $this->config['ssl'] === true && !$this->isEncrypted()
            ? [ $this, 'handleEncrypt' ]
            : [ $this, 'handleRead' ];
    }

    /**
     * Get function that should be invoked on write event.
     *
     * @return callable
     */
    protected function getHandleWriteFunction()
    {
        return $this->config['ssl'] === true && !$this->isEncrypted()
            ? [ $this, 'handleEncrypt' ]
            : [ $this, 'handleWrite' ];
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
     * Configure config variable.
     *
     * @param $configKey
     */
    private function configureVariable($configKey)
    {
        $configStaticKey = 'CONFIG_DEFAULT_' . strtoupper($configKey);
        $this->config[$configKey] = isset($this->config[$configKey]) ? $this->config[$configKey] : constant("static::$configStaticKey");
    }

    /**
     * @param bool $wantPeer
     * @return string
     */
    private function parseEndpoint($wantPeer = false)
    {
        $wantIndex = (int)$wantPeer;

        if (isset($this->cachedEndpoint[$wantIndex]))
        {
            return $this->cachedEndpoint[$wantIndex];
        }

        if (get_resource_type($this->resource) === Socket::TYPE_UNKNOWN)
        {
            return '';
        }

        $name = stream_socket_get_name($this->resource, $wantPeer);
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

        $this->cachedEndpoint[$wantIndex] = $endpoint;

        return $endpoint;
    }

    /**
     * @override
     * @inheritDoc
     */
    private function encrypt($method)
    {
        $type = $this->selectCryptoType($method);

        if ($type === self::CRYPTO_TYPE_UNKNOWN)
        {
            throw new ExecutionException('Socket encryption method is invalid!');
        }

        $resource = $this->getResource();

        if ($type === self::CRYPTO_TYPE_SERVER && $this->cryptoMethod === 0)
        {
            $raw = @stream_socket_recvfrom($resource, 11, STREAM_PEEK);

            if ($raw === '')
            {
                return;
            }

            if (11 > strlen($raw))
            {
                throw new ExecutionException('Failed to read crypto handshake.');
            }

            $data = unpack('ctype/nversion/nlength/Nembed/nmax-version', $raw);
            if (0x16 !== $data['type'])
            {
                throw new ExecutionException('Invalid crypto handshake.');
            }

            // Check if version was available in $method.
            $version = $this->selectCryptoVersion($data['max-version']);
            if ($method & $version)
            {
                $method = $version;
            }
        }

        $this->cryptoMethod = $method;
        $result = @stream_socket_enable_crypto($resource, true, $this->cryptoMethod);

        if ($result === 0)
        {
            return;
        }
        else if (!$result)
        {
            $message = 'Socket encryption failed.';
            if ($error = error_get_last())
            {
                $message .= sprintf(' Errno: %d; %s', $error['type'], $error['message']);
            }
            throw new ExecutionException($message);
        }
        else
        {
            $this->crypto = $this->cryptoMethod;
        }
    }

    /**
     * Checks type of crypto.
     *
     * @param int $version
     * @return int
     */
    private function selectCryptoType($version)
    {
        switch ($version)
        {
            case STREAM_CRYPTO_METHOD_SSLv3_SERVER:
            case STREAM_CRYPTO_METHOD_TLSv1_0_SERVER:
            case STREAM_CRYPTO_METHOD_TLSv1_1_SERVER:
            case STREAM_CRYPTO_METHOD_TLSv1_2_SERVER:
                return self::CRYPTO_TYPE_SERVER;

            case STREAM_CRYPTO_METHOD_SSLv3_CLIENT:
            case STREAM_CRYPTO_METHOD_TLSv1_0_CLIENT:
            case STREAM_CRYPTO_METHOD_TLSv1_1_CLIENT:
            case STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT:
                return self::CRYPTO_TYPE_CLIENT;

            default:
                return self::CRYPTO_TYPE_UNKNOWN;
        }
    }

    /**
     * Returns highest supported crypto method constant based on protocol version identifier.
     *
     * @param int $version
     * @return int
     */
    private function selectCryptoVersion($version)
    {
        switch ($version)
        {
            case 0x300: return STREAM_CRYPTO_METHOD_SSLv3_SERVER;
            case 0x301: return STREAM_CRYPTO_METHOD_TLSv1_0_SERVER;
            case 0x302: return STREAM_CRYPTO_METHOD_TLSv1_1_SERVER;
            default:    return STREAM_CRYPTO_METHOD_TLSv1_2_SERVER;
        }
    }
}
