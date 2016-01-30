<?php

namespace Kraken\Channel\Model\Null;

use Kraken\Channel\ChannelModelInterface;
use Kraken\Event\BaseEventEmitter;

class NullModel extends BaseEventEmitter implements ChannelModelInterface
{
    /**
     * @return bool
     */
    public function start()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function stop()
    {
        return true;
    }

    /**
     * @param string $id
     * @return bool
     */
    public function connect($id)
    {
        return true;
    }

    /**
     * @param string $id
     * @return bool
     */
    public function disconnect($id)
    {
        return true;
    }

    /**
     * @param string $alias
     * @param string[]|string $message
     * @param int $flags
     * @return bool
     */
    public function unicast($alias, $message, $flags)
    {
        return true;
    }

    /**
     * @param string[]|string $message
     * @param int $flags
     * @return bool[]
     */
    public function broadcast($message, $flags)
    {
        return [];
    }

    /**
     * @param string|null $alias
     * @return bool
     */
    public function isConnected($alias = null)
    {
        return false;
    }

    /**
     * @return string[]
     */
    public function getConnected()
    {
        return [];
    }
}
