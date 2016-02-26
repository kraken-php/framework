<?php

namespace Kraken\Channel;

use Kraken\Event\EventEmitterInterface;
use Kraken\Event\EventHandler;
use Kraken\Loop\LoopAwareInterface;
use Kraken\Throwable\Exception\Logic\Resource\ResourceUndefinedException;

/**
 * @event start
 * @event stop
 * @event connect string
 * @event disconnect string
 * @event input string ChannelProtocolInterface
 * @event output string ChannelProtocolInterface
 */
interface ChannelBaseInterface extends EventEmitterInterface, LoopAwareInterface
{
    /**
     * @return string
     */
    public function name();

    /**
     * @return ChannelModelInterface|null
     */
    public function model();

    /**
     * @return ChannelRouterCompositeInterface
     */
    public function router();

    /**
     * @return ChannelRouterBaseInterface|ChannelRouterCompositeInterface
     * @throws ResourceUndefinedException
     */
    public function input();

    /**
     * @return ChannelRouterBaseInterface|ChannelRouterCompositeInterface
     * @throws ResourceUndefinedException
     */
    public function output();

    /**
     * @param string|string[]|null $message
     * @return ChannelProtocolInterface
     */
    public function createProtocol($message = null);

    /**
     * @handle start
     * @param callable $handler
     * @return EventHandler
     */
    public function onStart(callable $handler);

    /**
     * @handle stop
     * @param callable $handler
     * @return EventHandler
     */
    public function onStop(callable $handler);

    /**
     * @handle connect
     * @param callable $handler
     * @return EventHandler
     */
    public function onConnect(callable $handler);

    /**
     * @handle disconnect
     * @param callable $handler
     * @return EventHandler
     */
    public function onDisconnect(callable $handler);

    /**
     * @handle receive
     * @param callable $handler
     * @return EventHandler
     */
    public function onInput(callable $handler);

    /**
     * @handle send
     * @param callable $handler
     * @return EventHandler
     */
    public function onOutput(callable $handler);

    /**
     *
     */
    public function start();

    /**
     *
     */
    public function stop();

    /**
     * @param string|string[] $name
     * @param string|string[]|ChannelProtocolInterface $message
     * @param int $flags
     * @param callable|null $success
     * @param callable|null $failure
     * @param callable|null $cancel
     * @param float $timeout
     * @return mixed|mixed[]
     */
    public function send($name, $message, $flags = Channel::MODE_DEFAULT, callable $success = null, callable $failure = null, callable $cancel = null, $timeout = 0.0);

    /**
     * @param string|string[] $name
     * @param string|string[]|ChannelProtocolInterface $message
     * @param int $flags
     * @param callable|null $success
     * @param callable|null $failure
     * @param callable|null $cancel
     * @param float $timeout
     * @return ChannelRequest|ChannelRequest[]|null|null[]|bool|bool[]
     */
    public function push($name, $message, $flags = Channel::MODE_DEFAULT, callable $success = null, callable $failure = null, callable $cancel = null, $timeout = 0.0);

    /**
     * @param string|string[] $name
     * @param string|string[]|ChannelProtocolInterface $message
     * @param int $flags
     * @return bool|bool[]
     */
    public function sendAsync($name, $message, $flags = Channel::MODE_DEFAULT);

    /**
     * @param string|string[] $name
     * @param string|string[]|ChannelProtocolInterface $message
     * @param int $flags
     * @return bool|bool[]
     */
    public function pushAsync($name, $message, $flags = Channel::MODE_DEFAULT);

    /**
     * @param string|string[] $name
     * @param string|string[]|ChannelProtocolInterface $message
     * @param int $flags
     * @param callable|null $success
     * @param callable|null $failure
     * @param callable|null $cancel
     * @param float $timeout
     * @return bool|bool[]
     */
    public function sendRequest($name, $message, $flags = Channel::MODE_DEFAULT, callable $success = null, callable $failure = null, callable $cancel = null, $timeout = 0.0);

    /**
     * @param string|string[] $name
     * @param string|string[]|ChannelProtocolInterface $message
     * @param int $flags
     * @param callable|null $success
     * @param callable|null $failure
     * @param callable|null $cancel
     * @param float $timeout
     * @return ChannelRequest|ChannelRequest[]|null|null[]
     */
    public function pushRequest($name, $message, $flags = Channel::MODE_DEFAULT, callable $success = null, callable $failure = null, callable $cancel = null, $timeout = 0.0);

    /**
     * @param string $sender
     * @param ChannelProtocolInterface $protocol
     */
    public function receive($sender, ChannelProtocolInterface $protocol);

    /**
     * @param string $sender
     * @param ChannelProtocolInterface $protocol
     */
    public function pull($sender, ChannelProtocolInterface $protocol);

    /**
     * @param string|string[]|null $name
     * @return bool|bool[]
     */
    public function isConnected($name = null);

    /**
     * @return string[]
     */
    public function getConnected();

    /**
     * @param string|string[] $name
     * @return string[]
     */
    public function matchConnected($name);
}
