<?php

namespace Kraken\Ipc\Socket;

use Kraken\Exception\Runtime\InstantiationException;
use Kraken\Exception\Runtime\LogicException;
use Kraken\Exception\RuntimeException;
use Kraken\Loop\LoopInterface;
use Kraken\Stream\AsyncStream;
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
     * @param string|resource $endpointOrResource
     * @param LoopInterface $loop
     * @param mixed[] $config
     * @throws InstantiationException
     */
    public function __construct($endpointOrResource, LoopInterface $loop, $config = [])
    {
        try
        {
            if (!is_resource($endpointOrResource))
            {
                $endpointOrResource = $this->createClient($endpointOrResource, $config);
            }

            parent::__construct($endpointOrResource, $loop);
        }
        catch (Error $ex)
        {
            throw new InstantiationException('SocketClient could not be created.');
        }
        catch (Exception $ex)
        {
            throw new InstantiationException('SocketClient could not be created.');
        }
    }

    /**
     * @override
     */
    public function getLocalEndpoint()
    {
        return $this->parseEndpoint(false);
    }

    /**
     * @override
     */
    public function getRemoteEndpoint()
    {
        return $this->parseEndpoint(true);
    }

    /**
     * @override
     */
    public function getLocalAddress()
    {
        $endpoint = explode('://', $this->getLocalEndpoint(), 2);

        return $endpoint[1];
    }

    /**
     * @override
     */
    public function getRemoteAddress()
    {
        $endpoint = explode('://', $this->getRemoteEndpoint(), 2);

        return $endpoint[1];
    }

    /**
     * Create the client resource.
     *
     * This method creates client resource for socket connections.
     *
     * @param string $endpoint
     * @param mixed[] $options
     * @return resource
     * @throws RuntimeException
     */
    protected function createClient($endpoint, $options = [])
    {
        $context = [];
        $context['socket'] = [
            'connect'       => $endpoint
        ];

        $context = stream_context_create($context);
        // Error reporting suppressed since stream_socket_client() emits an E_WARNING on failure.
        $socket = @stream_socket_client(
            $endpoint,
            $errno,
            $errstr,
            null, // Timeout does not apply for async connect.
            STREAM_CLIENT_CONNECT | STREAM_CLIENT_ASYNC_CONNECT,
            $context
        );

        if (!$socket || $errno)
        {
            throw new LogicException(
                sprintf('Could not connect socket [%s] because of error [%d; %s]', $endpoint, $errno, $errstr)
            );
        }

        return $socket;
    }

    /**
     * @override
     */
    public function handleData()
    {
        $data = stream_socket_recvfrom($this->resource, $this->bufferSize);

        if ($data !== '' && $data !== false)
        {
            $this->emit('data', [ $this, $data ]);
        }

        if ($data === '' || $data === false || !is_resource($this->resource) || feof($this->resource))
        {
            $this->close();
        }
    }

    /**
     * @override
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
     * @param bool $wantPeer
     * @return string
     */
    private function parseEndpoint($wantPeer = false)
    {
        $name = stream_socket_get_name($this->resource, $wantPeer);
        $type = $this->getStreamType();

        switch ($type)
        {
            case Socket::TYPE_UNIX:
                $endpoint = 'unix://' . $name;
                break;

            case Socket::TYPE_TCP:
                if (substr_count($name, ':') > 1)
                {
                    $parts = explode(':', $name);
                    $count = count($parts);
                    $port = $parts[$count - 1];
                    unset($parts[$count - 1]);
                    $endpoint = 'tcp://[' . implode(':', $parts) . ']:' . $port;
                }
                else
                {
                    $endpoint = 'tcp://' . $name;
                }
                break;

            default:
                $endpoint = '';
        }

        return $endpoint;
    }
}
