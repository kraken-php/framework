<?php

namespace Kraken\Exception;

class LazyException
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
     * @var \Exception|LazyException|null
     */
    protected $prev;

    /**
     * @param \Exception|string[]|string $exceptionOrMessage
     * @param \Exception|LazyException $prev
     */
    public function __construct($exceptionOrMessage, $prev = null)
    {
        if ($exceptionOrMessage instanceof Exception)
        {
            $this->class = get_class($exceptionOrMessage);
            $this->message = $exceptionOrMessage->getMessage();
        }
        else if (is_array($exceptionOrMessage))
        {
            $this->class = $exceptionOrMessage[0];
            $this->message = $exceptionOrMessage[1];
        }
        else
        {
            $this->class = 'Exception';
            $this->message = $exceptionOrMessage;
        }

        $this->prev = $prev;
    }

    /**
     * @return Exception
     */
    public function toException()
    {
        $class = $this->class;
        $prev = ($this->prev instanceof LazyException) ? $this->prev->toException() : $this->prev;
        return new $class($this->message, $prev);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return Exception::toString($this->toException());
    }

    /**
     * @return string
     */
    final public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return int
     */
    final public function getCode()
    {
        return 0;
    }

    /**
     * @return string
     */
    final public function getFile()
    {
        return $this->toException()->getFile();
    }

    /**
     * @return int
     */
    final public function getLine()
    {
        return $this->toException()->getLine();
    }

    /**
     * @return array
     */
    final public function getTrace()
    {
        return $this->toException()->getTrace();
    }

    /**
     * @return \Exception|null
     */
    final public function getPrevious()
    {
        return ($this->prev instanceof LazyException) ? $this->prev->toException() : $this->prev;
    }

    /**
     * @return string
     */
    final public function getTraceAsString()
    {
        return $this->toException()->getTraceAsString();
    }
}
