<?php

namespace Kraken\Pattern\Buffer;

use Kraken\Exception\Io\ReadException;
use Kraken\Exception\Io\WriteException;

class BufferMemorySoft implements BufferInterface
{
    /**
     * @var bool
     */
    protected $open;

    /**
     * @var int
     */
    protected $bufferSize;

    /**
     * @var string
     */
    protected $text;

    /**
     *
     */
    public function __construct()
    {
        $this->open = true;
        $this->bufferSize = 4096;
        $this->text = '';
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->open);
        unset($this->bufferSize);
        unset($this->text);
    }

    /**
     * @override
     */
    public function setBufferSize($bufferSize)
    {
        $this->bufferSize = $bufferSize;
    }

    /**
     * @override
     */
    public function getBufferSize()
    {
        return $this->bufferSize;
    }

    /**
     * @override
     */
    public function write($text)
    {
        if (!$this->open)
        {
            throw new WriteException('Buffer is no longer writable.');
        }

        $this->text .= $text;

        return strlen($this->text) < $this->bufferSize;
    }

    /**
     * @override
     */
    public function read()
    {
        if (!$this->open)
        {
            throw new ReadException('Buffer is no longer readable.');
        }

        return $this->text;
    }

    /**
     * @override
     */
    public function flush($length = null)
    {
        if ($length === null)
        {
            $this->text = '';
        }
        else
        {
            $this->text = (string) substr($this->text, $length);
        }
    }

    /**
     * @override
     */
    public function close()
    {
        $this->open = false;
        $this->bufferSize = 0;
        $this->text = '';
    }
}
