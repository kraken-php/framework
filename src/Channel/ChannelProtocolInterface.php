<?php

namespace Kraken\Channel;

interface ChannelProtocolInterface
{
    /**
     * @param string $type
     * @param bool $reassign
     * @return ChannelProtocolInterface
     */
    public function setType($type, $reassign = false);

    /**
     * @param string $pid
     * @param bool $reassign
     * @return ChannelProtocolInterface
     */
    public function setPid($pid, $reassign = false);

    /**
     * @param string $destination
     * @param bool $reassign
     * @return ChannelProtocolInterface
     */
    public function setDestination($destination, $reassign = false);

    /**
     * @param string $origin
     * @param bool $reassign
     * @return ChannelProtocolInterface
     */
    public function setOrigin($origin, $reassign = false);

    /**
     * @param string $message
     * @param bool $reassign
     * @return ChannelProtocolInterface
     */
    public function setMessage($message, $reassign = false);

    /**
     * @param string $exception
     * @param bool $reassign
     * @return ChannelProtocolInterface
     */
    public function setException($exception, $reassign = false);

    /**
     * @param int $timestamp
     * @param bool $reassign
     * @return ChannelProtocolInterface
     */
    public function setTimestamp($timestamp, $reassign = false);

    /**
     * @return string
     */
    public function getType();

    /**
     * @return string
     */
    public function getPid();

    /**
     * @return string
     */
    public function getDestination();

    /**
     * @return string
     */
    public function getOrigin();

    /**
     * @return string
     */
    public function getMessage();

    /**
     * @return string
     */
    public function getException();

    /**
     * @return int
     */
    public function getTimestamp();

    /**
     * @param mixed[] $args
     * @param bool $reassign
     * @return ChannelProtocolInterface
     */
    public function setAll($args = [], $reassign = false);

    /**
     * @return mixed[]
     */
    public function getAll();
}
