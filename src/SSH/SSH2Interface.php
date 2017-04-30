<?php

namespace Kraken\SSH;

use Kraken\Event\EventEmitterInterface;
use Kraken\Loop\LoopGetterAwareInterface;
use Kraken\Throwable\Exception\LogicException;
use Kraken\Throwable\Exception\RuntimeException;

/**
 * Interface SSH2Interface
 *
 * @event connect    : callable(SSH2Interface)
 * @event disconnect : callable(SSH2Interface)
 * @event error      : callable(SSH2Interface, Error|Exception)
 * @event connect:shell    : callable(SSH2DriverInterface)
 * @event disconnect:shell : callable(SSH2DriverInterface)
 * @event error:shell      : callable(SSH2DriverInterface, Error|Exception)
 * @event connect:sftp     : callable(SSH2DriverInterface)
 * @event disconnect:sftp  : callable(SSH2DriverInterface)
 * @event error:sftp       : callable(SSH2DriverInterface, Error|Exception)
 */
interface SSH2Interface extends EventEmitterInterface, LoopGetterAwareInterface
{
    /**
     * Connect to the SSH server.
     */
    public function connect();

    /**
     * Disconnect SSH from the server.
     */
    public function disconnect();

    /**
     * Check if connection has been established.
     *
     * @return bool
     */
    public function isConnected();

    /**
     * Create SSH2 Driver.
     *
     * @param $name string
     * @return SSH2DriverInterface
     * @throws LogicException
     * @throws RuntimeException
     */
    public function createDriver($name);
}
