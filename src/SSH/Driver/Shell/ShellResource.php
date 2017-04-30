<?php

namespace Kraken\SSH\Driver\Shell;

use Kraken\Event\BaseEventEmitterTrait;
use Kraken\Loop\LoopInterface;
use Kraken\SSH\SSH2DriverInterface;
use Kraken\SSH\SSH2ResourceInterface;
use Kraken\Throwable\Exception\Runtime\ReadException;
use Kraken\Throwable\Exception\Runtime\WriteException;
use Error;
use Exception;

class ShellResource implements SSH2ResourceInterface
{
    use BaseEventEmitterTrait;

    /**
     * @var SSH2DriverInterface
     */
    protected $driver;

    /**
     * @var resource
     */
    protected $resource;

    /**
     * @var bool
     */
    private $paused;

    /**
     * @var bool
     */
    private $closing;

    /**
     * @var bool
     */
    private $readable;

    /**
     * @var bool
     */
    private $writable;

    /**
     * @var int
     */
    private $bufferSize;

    /**
     * @var string
     */
    private $prefix;

    /**
     * @var string
     */
    private $successSuffix;

    /**
     * @var string
     */
    private $failureSuffix;

    /**
     * @param SSH2DriverInterface $driver
     * @param resource $resource
     */
    public function __construct(SSH2DriverInterface $driver, $resource)
    {
        $this->driver = $driver;
        $this->resource = $resource;

        $this->paused = true;
        $this->closing = false;
        $this->readable = true;
        $this->writable = true;

        $this->bufferSize = 4096;

        $this->prefix        = md5(microtime());
        $this->successSuffix = md5(microtime());
        $this->failureSuffix = md5(microtime());
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->prefix;
    }

    /**
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * @return string
     */
    public function getSuccessSuffix()
    {
        return $this->successSuffix;
    }

    /**
     * @return string
     */
    public function getFailureSuffix()
    {
        return $this->failureSuffix;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function setLoop(LoopInterface $loop = null)
    {
        $this->driver->setLoop($loop);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getLoop()
    {
        return $this->driver->getLoop();
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
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getResourceId()
    {
        return (int) $this->resource;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getMetadata()
    {
        return stream_get_meta_data($this->resource);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getStreamType()
    {
        return $this->getMetadata()['stream_type'];
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getWrapperType()
    {
        return $this->getMetadata()['wrapper_type'];
    }

    /**
     * @override
     * @inheritDoc
     */
    public function isOpen()
    {
        return !$this->closing;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function isSeekable()
    {
        return $this->getMetadata()['seekable'];
    }

    /**
     * @override
     * @inheritDoc
     */
    public function tell()
    {
        throw new ReadException('Cannot tell offset of this kind of stream.');
    }

    /**
     * @override
     * @inheritDoc
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        throw new WriteException('Cannot seek on this kind of stream.');
    }

    /**
     * @override
     * @inheritDoc
     */
    public function rewind()
    {
        throw new WriteException('Cannot rewind this kind of stream.');
    }

    /**
     * @override
     * @inheritDoc
     */
    public function close()
    {
        if ($this->closing)
        {
            return;
        }

        $this->closing = true;
        $this->readable = false;
        $this->writable = false;

        $this->emit('close', [ $this ]);
        $this->pause();
        $this->emit('done', [ $this ]);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function isReadable()
    {
        return $this->readable;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function setBufferSize($bufferSize)
    {
        $this->bufferSize = $bufferSize;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getBufferSize()
    {
        return $this->bufferSize;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function read($length = null)
    {
        if (!$this->readable)
        {
            return $this->throwAndEmitException(
                new ReadException('Stream is no longer readable.')
            );
        }

        if ($length === null)
        {
            $length = $this->bufferSize;
        }

        $ret = fread($this->resource, $length);

        if ($ret === false)
        {
            return $this->throwAndEmitException(
                new ReadException('Cannot read stream.')
            );
        }
        else if ($ret !== '')
        {
            $this->emit('data', [ $this, $ret ]);

            if (strlen($ret) < $length)
            {
                $this->emit('end', [ $this ]);
            }
        }

        return $ret;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function isWritable()
    {
        return $this->writable;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function write($text = '')
    {
        if (!$this->writable)
        {
            return $this->throwAndEmitException(
                new WriteException('Stream is no longer writable.')
            );
        }

        $command = sprintf(
            "echo %s && %s && echo %s || echo %s\n",
            $this->prefix,
            $text,
            $this->successSuffix . ':$?',
            $this->failureSuffix . ':$?'
        );

        $sent = fwrite($this->resource, $command);

        if ($sent === false)
        {
            return $this->throwAndEmitException(
                new WriteException('Error occurred while writing to the stream resource.')
            );
        }

        $this->writable = false; // this is single-use stream only!
        $this->emit('drain', [ $this ]);
        $this->emit('finish', [ $this ]);

        return true;
    }

    /**
     * Emit error event and the throws it too.
     *
     * @param Error|Exception $ex
     * @return null
     * @throws Error|Exception
     */
    protected function throwAndEmitException($ex)
    {
        $this->emit('error', [ $this, $ex ]);
        throw $ex;
    }
}
