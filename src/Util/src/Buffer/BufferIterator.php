<?php

namespace Kraken\Util\Buffer;

use Kraken\Throwable\Exception\Runtime\OutOfBoundsException;
use SeekableIterator;

class BufferIterator implements SeekableIterator
{
    /**
     * @var BufferInterface
     */
    private $buffer;
    
    /**
     * @var int
     */
    private $current = 0;

    /**
     * @param BufferInterface $buffer
     */
    public function __construct(BufferInterface $buffer)
    {
        $this->buffer = $buffer;
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->buffer);
    }
    
    /**
     * Rewind the iterator to the beginning of the buffer.
     */
    public function rewind()
    {
        $this->current = 0;
    }
    
    /**
     * Determine if the iterator is valid.
     *
     * @return bool
     */
    public function valid()
    {
        return isset($this->buffer[$this->current]);
    }
    
    /**
     * Return the current position (key) of the iterator.
     *
     * @return int
     */
    public function key()
    {
        return $this->current;
    }
    
    /**
     * Return the current character in the buffer at the iterator position.
     *
     * @return string
     */
    public function current()
    {
        return $this->buffer[$this->current];
    }
    
    /**
     * Move to the next character in the buffer.
     */
    public function next()
    {
        ++$this->current;
    }
    
    /**
     * Move to the previous character in the buffer.
     */
    public function prev()
    {
        --$this->current;
    }
    
    /**
     * Move to the given position in the buffer.
     *
     * @param int $position
     */
    public function seek($position)
    {
        $position = (int) $position;
        if (0 > $position)
        {
            $position = 0;
        }
        
        $this->current = $position;
    }
    
    /**
     * Insert the given string into the buffer at the current iterator position.
     *
     * @param string $data
     * @throws OutOfBoundsException
     */
    public function insert($data)
    {
        if (!$this->valid())
        {
            throw new OutOfBoundsException('The iterator is not valid!');
        }
        
        $this->buffer[$this->current] = $data . $this->buffer[$this->current];
    }
    
    /**
     * Replace the byte at the current iterator position with the given string.
     *
     * @param string $data
     * @return string
     * @throws OutOfBoundsException
     */
    public function replace($data)
    {
        if (!$this->valid())
        {
            throw new OutOfBoundsException('The iterator is not valid!');
        }
        
        $temp = $this->buffer[$this->current];
        
        $this->buffer[$this->current] = $data;
        
        return $temp;
    }
    
    /**
     * Remove the byte at the current iterator position and moves the iterator to the previous character.
     *
     * @return string
     * @throws OutOfBoundsException
     */
    public function remove()
    {
        if (!$this->valid())
        {
            throw new OutOfBoundsException('The iterator is not valid!');
        }
        
        $temp = $this->buffer[$this->current];
        
        unset($this->buffer[$this->current]);
        
        --$this->current;

        return $temp;
    }
}
