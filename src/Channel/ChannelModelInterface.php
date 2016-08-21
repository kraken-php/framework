<?php

namespace Kraken\Channel;

use Kraken\Event\EventEmitterInterface;

/**
 * @event start : callable()
 * @event stop  : callable()
 * @event connect    : callable(string)
 * @event disconnect : callable(string)
 * @event recv  : callable(string, string[])
 * @event send  : callable(string, string[])
 * @event error : callable(Exception)
 */
interface ChannelModelInterface extends EventEmitterInterface
{
    /**
     * Start Channel.
     *
     * @param bool $blockEvent
     * @return bool
     */
    public function start($blockEvent = false);

    /**
     * Stop Channel.
     *
     * @param bool $blockEvent
     * @return bool
     */
    public function stop($blockEvent = false);

    /**
     * Send unicast message.
     *
     * Flags might be one of:
     * Channel::MODE_STANDARD - sends message if both sender and receiver are online.
     * Channel::MODE_BUFFER_OFFLINE - works in similar way as MODE_STANDARD, but also enables buffering messages in case
     * that sender is offline.
     * Channel::MODE_BUFFER_ONLINE - works in similar way as MODE_STANDARD, but also enables buffering messages in case
     * that receiver is offline.
     * Channel::MODE_BUFFER - sends message if both sender and receiver are online or buffers it if one of them is
     * offline.
     *
     * @param string $alias
     * @param string[]|string $message
     * @param int $flags
     * @return bool
     */
    public function unicast($alias, $message, $flags);

    /**
     * Send broadcast message.
     *
     * @param string[]|string $message
     * @return bool[]
     */
    public function broadcast($message);

    /**
     * Check if specific channel is connected.
     *
     * @param string|null $id
     * @return bool
     */
    public function isConnected($id = null);

    /**
     * Return array of all connected channels' IDs.
     *
     * @return string[]
     */
    public function getConnected();
}
