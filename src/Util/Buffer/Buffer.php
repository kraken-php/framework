<?php

namespace Kraken\Util\Buffer;

class Buffer implements BufferInterface
{
    /**
     * @var string
     */
    private $data;
    
    /**
     * @param string $data
     */
    public function __construct($data = '')
    {
        $this->data = (string) $data;
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->data);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function __toString()
    {
        return $this->data;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function length()
    {
        return strlen($this->data);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function count()
    {
        return $this->length();
    }

    /**
     * @override
     * @inheritDoc
     */
    public function isEmpty()
    {
        return '' === $this->data;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function push($data)
    {
        $this->data .= $data;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function unshift($data)
    {
        $this->data = $data . $this->data;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function shift($length)
    {
        $length = (int) $length;
        if (0 >= $length)
        {
            return '';
        }

        if (strlen($this->data) <= $length)
        {
            $buffer = $this->data;
            $this->data = '';
            return $buffer;
        }

        $buffer = (string) substr($this->data, 0, $length);

        $this->data = (string) substr($this->data, $length);

        return $buffer;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function peek($length = 0, $offset = 0)
    {
        $length = (int) $length;
        if (0 > $length)
        {
            return '';
        }
        else if (0 === $length)
        {
            return $this->data;
        }

        $offset = (int) $offset;
        if (0 > $offset)
        {
            $offset = 0;
        }

        if (0 === $offset && strlen($this->data) <= $length)
        {
            return $this->data;
        }

        $result = (string) substr($this->data, $offset, $length);

        return $result;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function pop($length)
    {
        $length = (int) $length;
        if (0 >= $length)
        {
            return '';
        }

        $buffer = (string) substr($this->data, -$length);

        $this->data = (string) substr($this->data, 0, -$length);

        return $buffer;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function remove($length, $offset = 0)
    {
        $length = (int) $length;
        if (0 >= $length)
        {
            return '';
        }

        $offset = (int) $offset;
        if (0 > $offset)
        {
            $offset = 0;
        }

        $buffer = (string) substr($this->data, $offset, $length);

        if (0 === $offset)
        {
            $this->data = (string) substr($this->data, $length);
        }
        else
        {
            $this->data = (string) (substr($this->data, 0, $offset) . substr($this->data, $offset + $length));
        }

        return $buffer;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function drain()
    {
        $buffer = $this->data;
        $this->data = '';
        return $buffer;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function insert($string, $position)
    {
        $this->data = substr_replace($this->data, $string, $position, 0);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function replace($search, $replace)
    {
        $this->data = str_replace($search, $replace, $this->data, $count);

        return $count;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function search($string, $reverse = false)
    {
        if ($reverse)
        {
            return strrpos($this->data, $string);
        }

        return strpos($this->data, $string);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function offsetExists($index)
    {
        return isset($this->data[$index]);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function offsetGet($index)
    {
        return $this->data[$index];
    }

    /**
     * @override
     * @inheritDoc
     */
    public function offsetSet($index, $data)
    {
        $this->data = substr_replace($this->data, $data, $index, 1);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function offsetUnset($index)
    {
        if (isset($this->data[$index]))
        {
            $this->data = substr_replace($this->data, null, $index, 1);
        }
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getIterator()
    {
        return new BufferIterator($this);
    }
}
