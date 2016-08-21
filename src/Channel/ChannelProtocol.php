<?php

namespace Kraken\Channel;

class ChannelProtocol implements ChannelProtocolInterface
{
    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $pid;

    /**
     * @var string
     */
    protected $destination;

    /**
     * @var string
     */
    protected $origin;

    /**
     * @var string
     */
    protected $message;

    /**
     * @var string
     */
    protected $exception;

    /**
     * @var int
     */
    protected $timestamp;

    /**
     * @param string $type
     * @param string $pid
     * @param string $destination
     * @param string $origin
     * @param string $message
     * @param string $exception
     * @param int $timestamp
     */
    public function __construct($type = '', $pid = '', $destination = '', $origin = '', $message = '', $exception = '', $timestamp = 0)
    {
        $this->type = $type;
        $this->pid = $pid;
        $this->destination = $destination;
        $this->origin = $origin;
        $this->message = $message;
        $this->exception = $exception;
        $this->timestamp = $timestamp;
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->type);
        unset($this->pid);
        unset($this->destination);
        unset($this->origin);
        unset($this->message);
        unset($this->exception);
        unset($this->timestamp);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function setType($type, $reassign = false)
    {
        if ($this->type === '' || $reassign)
        {
            $this->type = $type;
        }

        return $this;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function setPid($pid, $reassign = false)
    {
        if ($this->pid === '' || $reassign)
        {
            $this->pid = $pid;
        }

        return $this;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function setDestination($destination, $reassign = false)
    {
        if ($this->destination === '' || $reassign)
        {
            $this->destination = $destination;
        }

        return $this;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function setOrigin($origin, $reassign = false)
    {
        if ($this->origin === '' || $reassign)
        {
            $this->origin = $origin;
        }

        return $this;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function setMessage($message, $reassign = false)
    {
        if ($this->message === '' || $reassign)
        {
            $this->message = $message;
        }

        return $this;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function setException($exception, $reassign = false)
    {
        if ($this->exception === '' || $reassign)
        {
            $this->exception = $exception;
        }

        return $this;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function setTimestamp($timestamp, $reassign = false)
    {
        if ($this->timestamp == 0 || $reassign)
        {
            $this->timestamp = $timestamp;
        }

        return $this;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getPid()
    {
        return $this->pid;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getDestination()
    {
        return $this->destination;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getOrigin()
    {
        return $this->origin;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getException()
    {
        return $this->exception;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function setAll($args = [], $reassign = false)
    {
        $this->type         = isset($args[0]) && ($this->type === '' || $reassign)          ? $args[0] : $this->type;
        $this->pid          = isset($args[1]) && ($this->pid === '' || $reassign)           ? $args[1] : $this->pid;
        $this->destination  = isset($args[2]) && ($this->destination === '' || $reassign)   ? $args[2] : $this->destination;
        $this->origin       = isset($args[3]) && ($this->origin === '' || $reassign)        ? $args[3] : $this->origin;
        $this->message      = isset($args[4]) && ($this->message === '' || $reassign)       ? $args[4] : $this->message;
        $this->exception    = isset($args[5]) && ($this->exception === '' || $reassign)     ? $args[5] : $this->exception;
        $this->timestamp    = isset($args[6]) && ($this->timestamp == 0 || $reassign)       ? $args[6] : $this->timestamp;

        return $this;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getAll()
    {
        return [
            $this->type,
            $this->pid,
            $this->destination,
            $this->origin,
            $this->message,
            $this->exception,
            $this->timestamp
        ];
    }
}
