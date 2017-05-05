<?php

namespace Kraken\SSH;

use Kraken\Event\EventEmitterInterface;
use Kraken\Loop\LoopResourceInterface;
use Kraken\Throwable\Exception\LogicException;

/**
 * Interface SSH2DriverInterface
 *
 * @event connect        : callable(SSH2DriverInterface)
 * @event disconnect     : callable(SSH2DriverInterface)
 * @event error          : callable(SSH2DriverInterface, Error|Exception)
 * @event resource:open  : callable(SSH2DriverInterface, SSH2ResourceInterface)
 * @event resource:close : callable(SSH2DriverInterface, SSH2ResourceInterface)
 */
interface SSH2DriverInterface extends EventEmitterInterface, LoopResourceInterface
{
    /**
     * Return driver name.
     *
     * @return string
     */
    public function getName();

    /**
     * Connect the driver.
     */
    public function connect();

    /**
     * Disconnect the driver.
     */
    public function disconnect();

    /**
     * Check if connection has been established.
     *
     * @return bool
     */
    public function isConnected();

    /**
     * Open new resource.
     *
     * @param string|null $resource
     * @param string $flags
     * @return SSH2ResourceInterface
     * @throws LogicException
     */
    public function open($resource = null, $flags = 'r');
}
