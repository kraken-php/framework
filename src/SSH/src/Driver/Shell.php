<?php

namespace Kraken\SSH\Driver;

use Kraken\Event\BaseEventEmitterTrait;
use Kraken\Loop\Timer\TimerInterface;
use Kraken\Loop\LoopAwareTrait;
use Kraken\SSH\Driver\Shell\ShellResource;
use Kraken\SSH\SSH2;
use Kraken\SSH\SSH2DriverInterface;
use Kraken\SSH\SSH2Interface;
use Kraken\SSH\SSH2ResourceInterface;
use Kraken\Throwable\Exception\Logic\ResourceUndefinedException;
use Kraken\Throwable\Exception\Runtime\ExecutionException;
use Kraken\Throwable\Exception\Runtime\ReadException;

class Shell implements SSH2DriverInterface
{
    use BaseEventEmitterTrait;
    use LoopAwareTrait;

    /**
     * @var int
     */
    const BUFFER_SIZE = 4096;

    /**
     * @var SSH2Interface
     */
    protected $ssh2;

    /**
     * @var resource
     */
    protected $conn;

    /**
     * @var float
     */
    protected $interval;

    /**
     * @var resource
     */
    protected $resource;

    /**
     * @var ShellResource[]|SSH2ResourceInterface[]
     */
    protected $resources;

    /**
     * @var bool
     */
    protected $paused;

    /**
     * @var TimerInterface|null
     */
    private $timer;

    /**
     * @var int
     */
    private $resourcesCounter;

    /**
     * @var string
     */
    private $buffer;

    /**
     * @var string
     */
    private $prefix;

    /**
     * @param SSH2Interface $ssh2
     * @param resource $conn
     * @param float $interval
     */
    public function __construct(SSH2Interface $ssh2, $conn, $interval = 1e-1)
    {
        $this->ssh2 = $ssh2;
        $this->conn = $conn;
        $this->interval = $interval;

        $this->loop = $ssh2->getLoop();

        $this->resource = null;
        $this->resources = [];
        $this->paused = true;

        $this->timer = null;
        $this->resourcesCounter = 0;
        $this->buffer = '';
        $this->prefix = '';

        $this->resume();
    }

    /**
     *
     */
    public function __destruct()
    {
        $this->disconnect();
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getName()
    {
        return SSH2::DRIVER_SHELL;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function connect()
    {
        if ($this->resource !== null)
        {
            return;
        }

        $shell = $this->createConnection($this->conn);

        if (!$shell || !is_resource($shell))
        {
            $this->emit('error', [ $this, new ExecutionException('SSH2:Shell could not be connected.') ]);
            return;
        }

        $this->resource = $shell;

        $this->emit('connect', [ $this ]);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function disconnect()
    {
        if ($this->resource === null || !is_resource($this->resource))
        {
            return;
        }

        $this->pause();

        foreach ($this->resources as $resource)
        {
            $resource->close();
        }

        $this->handleDisconnect();
        $this->emit('disconnect', [ $this ]);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function isConnected()
    {
        return $this->resource !== null && is_resource($this->resource);
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

            if ($this->timer !== null)
            {
                $this->timer->cancel();
                $this->timer = null;
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

            if ($this->timer === null)
            {
                $this->timer = $this->loop->addPeriodicTimer($this->interval, [ $this, 'handleHeartbeat' ]);
            }
        }
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
    public function open($resource = null, $flags = 'r')
    {
        if (!$this->isConnected())
        {
            throw new ResourceUndefinedException('Tried to open resource before establishing SSH2 connection!');
        }

        $resource = new ShellResource($this, $this->resource);
        $resource->on('open', function(SSH2ResourceInterface $resource) {
            $this->emit('resource:open', [ $this, $resource ]);
        });
        $resource->on('close', function(SSH2ResourceInterface $resource) {
            $this->removeResource($resource->getId());
            $this->emit('resource:close', [ $this, $resource ]);
        });

        $this->resources[$resource->getId()] = $resource;
        $this->resourcesCounter++;
        $this->resume();

        return $resource;
    }

    /**
     * Handle data.
     *
     * @internal
     */
    public function handleHeartbeat()
    {
        if (fwrite($this->resource, "\n") === 0)
        {
            return $this->ssh2->disconnect();
        }

        $this->handleRead();
    }

    /**
     * Handle incoming data.
     *
     * @internal
     */
    protected function handleRead()
    {
        if ($this->paused)
        {
            return;
        }

        $data = @fread($this->resource, static::BUFFER_SIZE);

        if ($data === false || $data === '')
        {
            return;
        }

        $this->buffer .= $data;

        while ($this->buffer !== '')
        {
            if ($this->prefix !== '' && !isset($this->resources[$this->prefix]))
            {
                $this->prefix = '';
            }

            if ($this->prefix === '')
            {
                if (!preg_match('/([a-zA-Z0-9]{32})\r?\n(.*)/s', $this->buffer, $matches))
                {
                    return;
                }

                $this->prefix = $matches[1];
                $this->buffer = $matches[2];
            }

            $resource = $this->resources[$this->prefix];
            $data = '';
            $status = -1;
            $successSuffix = $resource->getSuccessSuffix();
            $failureSuffix = $resource->getFailureSuffix();

            $this->buffer = preg_replace_callback(
                sprintf('/(.*)(%s|%s):(\d*)\r?\n/s', $successSuffix, $failureSuffix),
                function($matches) use($resource, &$data, &$status) {
                    $data   = $matches[1];
                    $status = (int) $matches[3];
                    return '';
                },
                $this->buffer
            );

            if ($status === -1)
            {
                $data = $this->buffer;
                $this->buffer = '';
            }
            else
            {
                $this->removeResource($this->prefix);
                $this->prefix = '';
            }

            $parts = str_split($data, $resource->getBufferSize());
            unset($data);

            foreach ($parts as &$part)
            {
                $resource->emit('data', [ $resource, $part ]);
            }
            unset($parts);
            unset($part);

            if ($status === 0)
            {
                $resource->emit('end', [ $resource ]);
                $resource->close();
            }
            else if ($status > 0)
            {
                $resource->emit('error', [ $resource, new ReadException($status) ]);
                $resource->close();
            }
        }

        $this->handleRead();
    }

    /**
     *
     */
    protected function handleDisconnect()
    {
        @fclose($this->resource);
        $this->resource = null;
    }

    /**
     * @param resource $conn
     * @return resource
     */
    protected function createConnection($conn)
    {
        return @ssh2_shell($conn);
    }

    /**
     * Remove resource from known collection.
     *
     * @param string $prefix
     */
    private function removeResource($prefix)
    {
        if (!isset($this->resources[$prefix]))
        {
            return;
        }

        unset($this->resources[$prefix]);
        $this->resourcesCounter--;

        if ($this->resourcesCounter === 0)
        {
            $this->pause();
        }
    }
}
