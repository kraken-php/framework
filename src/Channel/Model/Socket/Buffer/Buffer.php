<?php

namespace Kraken\Channel\Model\Socket\Buffer;

/**
 * @codeCoverageIgnore
 */
class Buffer
{
    /**
     * @var string[][]
     */
    protected $messageBuffer;

    /**
     * @var int
     */
    protected $messageBufferSize;

    /**
     * @var int
     */
    protected $messageBufferMax;

    /**
     * @param int $bufferSize
     */
    public function __construct($bufferSize = 0)
    {
        $this->messageBuffer = [];
        $this->messageBufferSize = 0;
        $this->messageBufferMax = $bufferSize;
    }

    /**
     *
     */
    public function __destruct()
    {
        $this->erase();
        unset($this->messageBufferMax);
    }

    /**
     * @param string $id
     * @param string $frame
     * @return bool
     */
    public function push($id, $frame)
    {
        if ($this->messageBufferSize >= $this->messageBufferMax && $this->messageBufferMax > 0)
        {
            return false;
        }

        if (!isset($this->messageBuffer[$id]))
        {
            $this->messageBuffer[$id] = [];
        }

        $this->messageBuffer[$id][] = $frame;
        $this->messageBufferSize++;

        return true;
    }

    /**
     * @param string|null $id
     * @return string[]
     */
    public function pull($id = null)
    {
        $messages = [];

        if ($id === null)
        {
            foreach ($this->messageBuffer as $id=>$buffer)
            {
                foreach ($buffer as $message)
                {
                    $messages[] = [ $id, $message ];
                }

                $this->messageBuffer[$id] = [];
            }

            $this->messageBuffer = [];
            $this->messageBufferSize = 0;
        }
        else if (isset($this->messageBuffer[$id]))
        {
            $cnt = 0;

            foreach ($this->messageBuffer[$id] as $message)
            {
                $messages[] = [ $id, $message ];
                $cnt++;
            }

            unset($this->messageBuffer[$id]);
            $this->messageBufferSize -= $cnt;
        }

        return $messages;
    }

    /**
     *
     */
    public function erase()
    {
        $this->messageBuffer = [];
        $this->messageBufferSize = 0;
    }
}
