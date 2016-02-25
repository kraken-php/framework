<?php

namespace Kraken\Util\Buffer;

class Buffer implements BufferInterface
{
    /**
     * @var string
     */
    protected $data;
    
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
     */
    public function __toString()
    {
        return $this->data;
    }

    /**
     * @override
     */
    public function length()
    {
        return strlen($this->data);
    }

    /**
     * @override
     */
    public function count()
    {
        return $this->length();
    }

    /**
     * @override
     */
    public function isEmpty()
    {
        return '' === $this->data;
    }

    /**
     * @override
     */
    public function push($data)
    {
        $this->data .= $data;
    }

    /**
     * @override
     */
    public function unshift($data)
    {
        $this->data = $data . $this->data;
    }

    /**
     * @override
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
     */
    public function drain()
    {
        $buffer = $this->data;
        $this->data = '';
        return $buffer;
    }

    /**
     * @override
     */
    public function insert($string, $position)
    {
        $this->data = substr_replace($this->data, $string, $position, 0);
    }

    /**
     * @override
     */
    public function replace($search, $replace)
    {
        $this->data = str_replace($search, $replace, $this->data, $count);

        return $count;
    }

    /**
     * @override
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
     */
    public function offsetExists($index)
    {
        return isset($this->data[$index]);
    }

    /**
     * @override
     */
    public function offsetGet($index)
    {
        return $this->data[$index];
    }

    /**
     * @override
     */
    public function offsetSet($index, $data)
    {
        $this->data = substr_replace($this->data, $data, $index, 1);
    }

    /**
     * @override
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
     */
    public function getIterator()
    {
        return new BufferIterator($this);
    }
}

