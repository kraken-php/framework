<?php

namespace Kraken\Runtime\Command;

use Dazzle\Throwable\Exception\Runtime\ExecutionException;

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
     * @override
     * @inheritDoc
     */
    public function __invoke($name, $params = [])
    {
        return $this->execute($name, $params);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function import($commands)
    {
        foreach ($commands as $name=>$command)
        {
            $this->set($name, $command);
        }
    }

    /**
     * @override
     * @inheritDoc
     */
    public function export()
    {
        return $this->commands;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function exists($name)
    {
        return isset($this->commands[$name]);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function set($name, CommandInterface $command)
    {
        $this->commands[$name] = $command;
    }

    /**
     * @override
     * @inheritDoc
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
     * @override
     * @inheritDoc
     */
    public function remove($name)
    {
        unset($this->commands[$name]);
    }

    /**
     * @override
     * @inheritDoc
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
