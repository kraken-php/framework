<?php

namespace Kraken\Throwable;

/**
 * Throwables were designed to handle exceptional states that can occur in application during execution. They were not
 * meant to be used in business logic, however in practice, many design patterns emerged that used Throwables as a type
 * representation of failure, for which application needed to react. In other programming languages Throwables are
 * populated with data not on creation but while throwing. In PHP it works differently, and Throwables are populated
 * on creation. This can lead to major problems with memory and performance with usage of previously mentioned design
 * patterns. In those cases the valuable pieces of information are Throwable class, message, code and previous element,
 * but not stack trace that requires most of memory allocation. For this exclusive need ThrowableProxy has been created.
 * Its main purpose is to create a placeholders for Throwable most needed data discarding all not needed traces.
 *
 * TLDR: This class should be used in design patterns which logic represents failures as throwables, and does not
 * necessarily need stack information.
 */
class ThrowableProxy
{
    /**
     * @var string
     */
    protected $class;

    /**
     * @var string
     */
    protected $message;

    /**
     * @var \Error|\Exception|ThrowableProxy|null
     */
    protected $prev;

    /**
     * @param \Error|\Exception|string[]|string $throwableOrMessage
     */
    public function __construct($throwableOrMessage)
    {
        if ($throwableOrMessage instanceof \Error || $throwableOrMessage instanceof \Exception)
        {
            $prev = $throwableOrMessage->getPrevious();

            $this->class = get_class($throwableOrMessage);
            $this->message = $throwableOrMessage->getMessage();
            $this->prev = $prev === null ? null : new ThrowableProxy($prev);
        }
        else if (is_array($throwableOrMessage))
        {
            $this->class = $throwableOrMessage[0];
            $this->message = $throwableOrMessage[1];
            $this->prev = null;
        }
        else
        {
            $this->class = 'Exception';
            $this->message = $throwableOrMessage;
            $this->prev = null;
        }
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->class);
        unset($this->message);
        unset($this->prev);
    }

    /**
     * Format proxied Throwable as string.
     *
     * @return string
     */
    public function __toString()
    {
        return Exception::toString($this->toThrowable());
    }

    /**
     * Return proxied Throwable.
     *
     * @return \Error|\Exception
     */
    public function toThrowable()
    {
        $class = $this->class;
        $prev  = $this->prev !== null ? $this->prev->toThrowable() : $this->prev;

        return preg_match('#^(\\\?)Kraken#si', $class)
            ? new $class($this->message, $prev)
            : new $class($this->message, 0, $prev);
    }

    /**
     * Return proxied Throwable message.
     *
     * @return string
     */
    final public function getMessage()
    {
        return $this->message;
    }

    /**
     * Return proxied Throwable code.
     *
     * @return int
     */
    final public function getCode()
    {
        return $this->toThrowable()->getCode();
    }

    /**
     * Return proxied Throwable file.
     *
     * @return string
     */
    final public function getFile()
    {
        return $this->toThrowable()->getFile();
    }

    /**
     * Return proxied Throwable code.
     *
     * @return int
     */
    final public function getLine()
    {
        return $this->toThrowable()->getLine();
    }

    /**
     * Return proxied Throwable trace.
     *
     * @return array
     */
    final public function getTrace()
    {
        return $this->toThrowable()->getTrace();
    }

    /**
     * Return proxied Throwable previous element.
     *
     * @return \Error|\Exception|null
     */
    final public function getPrevious()
    {
        return $this->prev !== null ? $this->prev->toThrowable() : $this->prev;
    }

    /**
     * Return proxied Throwable trace in string format.
     *
     * @return string
     */
    final public function getTraceAsString()
    {
        return $this->toThrowable()->getTraceAsString();
    }
}
