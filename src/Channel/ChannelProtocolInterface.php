<?php

namespace Kraken\Channel;

interface ChannelProtocolInterface
{
    /**
     * Set type of protocol.
     *
     * This method sets type only if it was not set already. To force replacement $reassing flag needs to be set to
     * true.
     *
     * @param string $type
     * @param bool $reassign
     * @return ChannelProtocolInterface
     */
    public function setType($type, $reassign = false);

    /**
     * Set ID of protocol.
     *
     * This method sets ID only if it was not set already. To force replacement $reassing flag needs to be set to
     * true.
     *
     * @param string $pid
     * @param bool $reassign
     * @return ChannelProtocolInterface
     */
    public function setPid($pid, $reassign = false);

    /**
     * Set destination of protocol.
     *
     * This method sets destination only if it was not set already. To force replacement $reassing flag needs to be set
     * to true.
     *
     * @param string $destination
     * @param bool $reassign
     * @return ChannelProtocolInterface
     */
    public function setDestination($destination, $reassign = false);

    /**
     * Set origin of protocol.
     *
     * This method sets origin only if it was not set already. To force replacement $reassing flag needs to be set to
     * true.
     *
     * @param string $origin
     * @param bool $reassign
     * @return ChannelProtocolInterface
     */
    public function setOrigin($origin, $reassign = false);

    /**
     * Set message of protocol.
     *
     * This method sets message only if it was not set already. To force replacement $reassing flag needs to be set to
     * true.
     *
     * @param string $message
     * @param bool $reassign
     * @return ChannelProtocolInterface
     */
    public function setMessage($message, $reassign = false);

    /**
     * Set exception of protocol.
     *
     * This method sets exception only if it was not set already. To force replacement $reassing flag needs to be set to
     * true.
     *
     * @param string $exception
     * @param bool $reassign
     * @return ChannelProtocolInterface
     */
    public function setException($exception, $reassign = false);

    /**
     * Set timestamp of protocol.
     *
     * This method sets timestamp only if it was not set already. To force replacement $reassing flag needs to be set to
     * true.
     *
     * @param int $timestamp
     * @param bool $reassign
     * @return ChannelProtocolInterface
     */
    public function setTimestamp($timestamp, $reassign = false);

    /**
     * Return protocol type.
     *
     * @return string
     */
    public function getType();

    /**
     * Return protocol ID.
     *
     * @return string
     */
    public function getPid();

    /**
     * Return protocol destination.
     *
     * @return string
     */
    public function getDestination();

    /**
     * Return protocol origin.
     *
     * @return string
     */
    public function getOrigin();

    /**
     * Return protocol message.
     *
     * @return string
     */
    public function getMessage();

    /**
     * Return protocol exception.
     *
     * @return string
     */
    public function getException();

    /**
     * Return protocol timestamp.
     *
     * @return int
     */
    public function getTimestamp();

    /**
     * Set all fields of protocol simultaneously.
     *
     * @param mixed[] $args
     * @param bool $reassign
     * @return ChannelProtocolInterface
     */
    public function setAll($args = [], $reassign = false);

    /**
     * Get all fields of protocol in form of an array.
     *
     * @return mixed[]
     */
    public function getAll();
}
