<?php

namespace Kraken\SSH\Driver;

use Kraken\Event\BaseEventEmitterTrait;
use Kraken\Loop\Timer\TimerInterface;
use Kraken\Loop\LoopAwareTrait;
use Kraken\SSH\Driver\Sftp\SftpResource;
use Kraken\SSH\SSH2DriverInterface;
use Kraken\SSH\SSH2Interface;
use Kraken\SSH\SSH2ResourceInterface;
use Kraken\Throwable\Exception\Logic\ResourceUndefinedException;
use Kraken\Throwable\Exception\Runtime\ExecutionException;
use Error;
use Exception;


class Sftp implements SSH2DriverInterface
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
     * @var SftpResource[]|SSH2ResourceInterface[]
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
        return 'sftp';
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

        $resource = $this->createConnection($this->conn);

        if (!$resource || !is_resource($resource))
        {
            $this->emit('error', [ $this, new ExecutionException('SSH2:Sftp could not be connected.') ]);
            return;
        }

        $this->resource = $resource;

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

        $stream = @fopen("ssh2.sftp://" . $this->resource . $resource, $flags);

        if (!$stream)
        {
            throw new ResourceUndefinedException("Access to SFTP resource [$resource] denied!");
        }

        $resource = new SftpResource($this, $stream);
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
     * Determine whether connection is still open.
     *
     * @internal
     */
    public function handleHeartbeat()
    {
        $fp = @fopen("ssh2.sftp://" . $this->resource . "/.", "r");

        if (!$fp || !is_resource($fp))
        {
            return $this->ssh2->disconnect();
        }

        fclose($fp);

        $this->handleData();
    }

    /**
     * Handle data.
     *
     * @internal
     */
    public function handleData()
    {
        if ($this->paused || $this->resourcesCounter === 0)
        {
            return;
        }

        // handle all reading
        foreach ($this->resources as $resource)
        {
            if (!$resource->isPaused() && $resource->isReadable())
            {
                $resource->handleRead();
            }
        }

        // handle all writing
        foreach ($this->resources as $resource)
        {
            if (!$resource->isPaused() && $resource->isWritable())
            {
                $resource->handleWrite();
            }
        }
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
