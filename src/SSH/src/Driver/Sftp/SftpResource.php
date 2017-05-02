<?php

namespace Kraken\SSH\Driver\Sftp;

use Kraken\Loop\LoopAwareTrait;
use Kraken\SSH\SSH2DriverInterface;
use Kraken\SSH\SSH2ResourceInterface;
use Kraken\Stream\Stream;
use Kraken\Throwable\Exception\Runtime\ReadException;
use Kraken\Throwable\Exception\Runtime\WriteException;
use Kraken\Util\Buffer\Buffer;
use Kraken\Util\Buffer\BufferInterface;
use Error;
use Exception;

class SftpResource extends Stream implements SSH2ResourceInterface
{
    use LoopAwareTrait;

    /**
     * @var bool
     */
    protected $writing;

    /**
     * @var bool
     */
    protected $reading;

    /**
     * @var bool
     */
    protected $readingStarted;

    /**
     * @var bool
     */
    protected $paused;

    /**
     * @var BufferInterface
     */
    protected $buffer;

    /**
     * @param SSH2DriverInterface $driver
     * @param resource $resource
     */
    public function __construct(SSH2DriverInterface $driver, $resource)
    {
        parent::__construct($resource);

        $this->loop = $driver->getLoop();
        $this->writing = false;
        $this->reading = false;
        $this->readingStarted = false;
        $this->paused = false;
        $this->buffer = new Buffer();
    }

    /**
     * @return string
     */
    public function getId()
    {
        return (string) $this->getResourceId();
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
    public function pause()
    {
        if (!$this->paused)
        {
            $this->paused = true;
            $this->writing = false;
            $this->reading = false;
        }
    }

    /**
     * @override
     * @inheritDoc
     */
    public function resume()
    {
        if (($this->writable || $this->readable) && $this->paused)
        {
            $this->paused = false;

            if ($this->readable && $this->readingStarted)
            {
                $this->reading = true;
            }

            if ($this->writable && $this->buffer->isEmpty() === false)
            {
                $this->writing = true;
            }
        }
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

        $this->buffer->push($text);

        if (!$this->writing && !$this->paused)
        {
            $this->writing = true;
        }

        return $this->buffer->length() < $this->bufferSize;
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

        if (!$this->reading && !$this->paused)
        {
            $this->reading = true;
            $this->readingStarted = true;
        }

        return '';
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

        if ($this->buffer->isEmpty() === false)
        {
            $this->writeEnd();
        }

        $this->emit('close', [ $this ]);
        $this->handleClose();
        $this->emit('done', [ $this ]);
    }

    /**
     * Handle the outcoming stream.
     *
     * @internal
     */
    public function handleWrite()
    {
        try
        {
            if (!$this->writing)
            {
                return;
            }

            $text = $this->buffer->peek();

            if ($text !== '')
            {
                $sent = fwrite($this->resource, $text);
            }
            else
            {
                $sent = 0;
            }

            if ($text !== '' && !$sent)
            {
                $this->emit('error', [ $this, new WriteException('Error occurred while writing to the stream resource.') ]);
                return;
            }

            $lenBefore = strlen($text);
            $lenAfter = $lenBefore - $sent;
            $this->buffer->remove($sent);

            $this->emit('drain', [ $this ]);

            if ($lenAfter === 0 && $this->buffer->isEmpty())
            {
                $this->writing = false;
                $this->emit('finish', [ $this ]);
            }
        }
        catch (Error $ex)
        {
            $this->emit('error', [ $this, $ex ]);
        }
        catch (Exception $ex)
        {
            $this->emit('error', [ $this, $ex ]);
        }
    }

    /**
     * Handle the incoming stream.
     *
     * @internal
     */
    public function handleRead()
    {
        try
        {
            if (!$this->reading)
            {
                return;
            }

            $length = $this->bufferSize;
            $ret = fread($this->resource, $length);

            if ($ret === false)
            {
                $this->emit('error', [ $this, new ReadException('Error occurred while reading from the stream resource.') ]);
                return;
            }

            if ($ret !== '')
            {
                $this->emit('data', [ $this, $ret ]);

                if (strlen($ret) < $length)
                {
                    $this->reading = false;
                    $this->emit('end', [ $this ]);
                }
            }
        }
        catch (Error $ex)
        {
            $this->emit('error', [ $this, $ex ]);
        }
        catch (Exception $ex)
        {
            $this->emit('error', [ $this, $ex ]);
        }
    }

    /**
     * Handle close.
     *
     * @internal
     */
    public function handleClose()
    {
        $this->pause();

        parent::handleClose();
    }

    /**
     *
     */
    private function writeEnd()
    {
        do
        {
            try
            {
                $sent = fwrite($this->resource, $this->buffer->peek());
                $this->buffer->remove($sent);
            }
            catch (Error $ex)
            {
                $sent = 0;
            }
            catch (Exception $ex)
            {
                $sent = 0;
            }
        }
        while (is_resource($this->resource) && $sent > 0 && !$this->buffer->isEmpty());
    }
}
