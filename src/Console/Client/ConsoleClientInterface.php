<?php

namespace Kraken\Console\Client;

use Kraken\Core\CoreSetterAwareInterface;
use Kraken\Core\CoreInputContextInterface;
use Kraken\Event\EventHandler;
use Kraken\Loop\LoopExtendedAwareInterface;

/**
 * @event start
 * @event stop
 * @event command
 */
interface ConsoleClientInterface extends CoreInputContextInterface, CoreSetterAwareInterface, LoopExtendedAwareInterface
{
    /**
     * @param callable $callback
     * @return EventHandler
     */
    public function onStart(callable $callback);

    /**
     * @param callable $callback
     * @return EventHandler
     */
    public function onStop(callable $callback);

    /**
     * @param callable $callback
     * @return EventHandler
     */
    public function onCommand(callable $callback);

    /**
     *
     */
    public function start();

    /**
     *
     */
    public function stop();
}
