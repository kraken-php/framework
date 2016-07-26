<?php

namespace Kraken\Util\Buffer;

use ArrayAccess;
use Countable;
use IteratorAggregate;

interface BufferInterface extends ArrayAccess, Countable, IteratorAggregate
{
    /**
     * Return string stored in Buffer.
     *
     * @return string
     */
    public function __toString();

    /**
     * Return current length of the buffer.
     *
     * @return int
     */
    public function length();

    /**
     * Determine if the buffer is empty.
     *
     * @return bool
     */
    public function isEmpty();

    /**
     * Push the given string onto the end of the buffer.
     *
     * @param string $data
     */
    public function push($data);

    /**
     * Put the given string at the beginning of the buffer.
     *
     * @param string $data
     */
    public function unshift($data);

    /**
     * Remove the given number of characters (at most) from the buffer starting at the beginning.
     *
     * @param int $length
     * @return string
     */
    public function shift($length);

    /**
     * Return the given number of characters (at most) from the buffer without removing them from the buffer.
     *
     * @param int $length
     * @param int $offset
     * @return string
     */
    public function peek($length = 0, $offset = 0);

    /**
     * Remove the given number of characters (at most) from the buffer starting at the end.
     *
     * @param int $length
     * @return string
     */
    public function pop($length);

    /**
     * Remove and return the given number of characters (at most) from the buffer.
     *
     * @param int $length
     * @param int $offset
     * @return string
     */
    public function remove($length, $offset = 0);

    /**
     * Remove and return all data from the buffer.
     *
     * @return string
     */
    public function drain();

    /**
     * Insert the string at the given position in the buffer.
     *
     * @param string $string
     * @param int $position
     */
    public function insert($string, $position);

    /**
     * Replace all occurences of $search with $replace. See str_replace() function.
     *
     * @param mixed $search
     * @param mixed $replace
     * @return int
     */
    public function replace($search, $replace);

    /**
     * Return the position of the given pattern in the buffer if it exists, or false if it does not.
     *
     * @param string $string
     * @param bool $reverse
     * @return int|bool
     */
    public function search($string, $reverse = false);
}
