<?php

namespace Kraken\Command;

use Kraken\Promise\PromiseInterface;
use Kraken\Throwable\Exception\Runtime\ExecutionException;

class CommandManager implements CommandManagerInterface
{
    /**
     * @var CommandInterface[]
     */
    protected $commands;

    /**
     * @param CommandInterface[] $commands
     */
    public function __construct($commands = [])
    {
        $this->import($commands);
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->commands);
    }

    /**
     * @param string $name
     * @param mixed[] $params
     * @return PromiseInterface
     * @throws ExecutionException
     */
    public function __invoke($name, $params = [])
    {
        return $this->execute($name, $params);
    }

    /**
     * @param CommandInterface[] $commands
     */
    public function import($commands)
    {
        foreach ($commands as $name=>$command)
        {
            $this->set($name, $command);
        }
    }

    /**
     * @return CommandInterface[]
     */
    public function export()
    {
        return $this->commands;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function exists($name)
    {
        return isset($this->commands[$name]);
    }

    /**
     * @param string $name
     * @param CommandInterface $command
     */
    public function set($name, CommandInterface $command)
    {
        $this->commands[$name] = $command;
    }

    /**
     * @param string $name
     * @return CommandInterface|null
     */
    public function get($name)
    {
        if (!$this->exists($name))
        {
            return null;
        }

        return $this->commands[$name];
    }

    /**
     * @param string $name
     */
    public function remove($name)
    {
        unset($this->commands[$name]);
    }

    /**
     * @param string $name
     * @param mixed[] $params
     * @return PromiseInterface
     * @throws ExecutionException
     */
    public function execute($name, $params = [])
    {
        $command = $this->get($name);

        if ($command === null)
        {
            throw new ExecutionException("Command [$name] is not registered.");
        }

        return $command->execute($params);
    }
}
