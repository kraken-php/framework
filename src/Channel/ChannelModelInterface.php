<?php

namespace Kraken\Channel;

use Kraken\Event\EventEmitterInterface;

/**
 * @event start
 * @event stop
 * @event connect string
 * @event disconnect string
 * @event recv string string[]
 * @event send string
 * @event error int string
 */
interface ChannelModelInterface extends EventEmitterInterface
{
    /**
     * @return bool
     */
    public function start();

    /**
     * @return bool
     */
    public function stop();

    /**
     * @param string $id
     * @return bool
     */
    public function connect($id);

    /**
     * @param string $id
     * @return bool
     */
    public function disconnect($id);

    /**
     * @param string $alias
     * @param string[]|string $message
     * @param int $flags
     * @return bool
     */
    public function unicast($alias, $message, $flags);

    /**
     * @param string[]|string $message
     * @param int $flags
     * @return bool[]
     */
    public function broadcast($message, $flags);

    /**
     * @param string|null $alias
     * @return bool
     */
    public function isConnected($alias = null);

    /**
     * @return string[]
     */
    public function getConnected();
}
