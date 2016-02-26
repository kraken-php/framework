<?php

namespace Kraken\Command;

use Kraken\Promise\PromiseInterface;
use Kraken\Throwable\Exception\Runtime\ExecutionException;

interface CommandManagerInterface
{
    /**
     * @param string $name
     * @param mixed[] $params
     * @return PromiseInterface
     * @throws ExecutionException
     */
    public function __invoke($name, $params = []);

    /**
     * @param CommandInterface[] $commands
     */
    public function import($commands);

    /**
     * @return CommandInterface[]
     */
    public function export();

    /**
     * @param string $name
     * @return bool
     */
    public function exists($name);

    /**
     * @param string $name
     * @param CommandInterface $command
     */
    public function set($name, CommandInterface $command);

    /**
     * @param string $name
     * @return CommandInterface|null
     */
    public function get($name);

    /**
     * @param string $name
     */
    public function remove($name);

    /**
     * @param string $name
     * @param mixed[] $params
     * @return PromiseInterface
     * @throws ExecutionException
     */
    public function execute($name, $params = []);
}