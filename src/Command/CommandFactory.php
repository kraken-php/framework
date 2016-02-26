<?php

namespace Kraken\Command;

use Kraken\Util\Factory\Factory;

class CommandFactory extends Factory implements CommandFactoryInterface
{
    /**
     * @param $context
     * @param string[] $params
     */
    public function __construct($params = [])
    {
        parent::__construct();

        foreach ($params as $paramName=>$paramValue)
        {
            $this->bindParam($paramName, $paramValue);
        }

        $commands = [
            'ArchStartCommand'          => 'Kraken\Runtime\Container\Command\Arch\ArchStartCommand',
            'ArchStopCommand'           => 'Kraken\Runtime\Container\Command\Arch\ArchStopCommand',
            'ArchStatusCommand'         => 'Kraken\Runtime\Container\Command\Arch\ArchStatusCommand',
            'ProcessExistsCommand'      => 'Kraken\Runtime\Container\Command\Process\ProcessExistsCommand',
            'ProcessCreateCommand'      => 'Kraken\Runtime\Container\Command\Process\ProcessCreateCommand',
            'ProcessDestroyCommand'     => 'Kraken\Runtime\Container\Command\Process\ProcessDestroyCommand',
            'ProcessStartCommand'       => 'Kraken\Runtime\Container\Command\Process\ProcessStartCommand',
            'ProcessStopCommand'        => 'Kraken\Runtime\Container\Command\Process\ProcessStopCommand',
            'ProcessesGetCommand'       => 'Kraken\Runtime\Container\Command\Processes\ProcessesGetCommand',
            'ProcessesCreateCommand'    => 'Kraken\Runtime\Container\Command\Processes\ProcessesCreateCommand',
            'ProcessesDestroyCommand'   => 'Kraken\Runtime\Container\Command\Processes\ProcessesDestroyCommand',
            'ProcessesStartCommand'     => 'Kraken\Runtime\Container\Command\Processes\ProcessesStartCommand',
            'ProcessesStopCommand'      => 'Kraken\Runtime\Container\Command\Processes\ProcessesStopCommand',
            'ThreadExistsCommand'       => 'Kraken\Runtime\Container\Command\Thread\ThreadExistsCommand',
            'ThreadCreateCommand'       => 'Kraken\Runtime\Container\Command\Thread\ThreadCreateCommand',
            'ThreadDestroyCommand'      => 'Kraken\Runtime\Container\Command\Thread\ThreadDestroyCommand',
            'ThreadStartCommand'        => 'Kraken\Runtime\Container\Command\Thread\ThreadStartCommand',
            'ThreadStopCommand'         => 'Kraken\Runtime\Container\Command\Thread\ThreadStopCommand',
            'ThreadsGetCommand'         => 'Kraken\Runtime\Container\Command\Threads\ThreadsGetCommand',
            'ThreadsCreateCommand'      => 'Kraken\Runtime\Container\Command\Threads\ThreadsCreateCommand',
            'ThreadsDestroyCommand'     => 'Kraken\Runtime\Container\Command\Threads\ThreadsDestroyCommand',
            'ThreadsStartCommand'       => 'Kraken\Runtime\Container\Command\Threads\ThreadsStartCommand',
            'ThreadsStopCommand'        => 'Kraken\Runtime\Container\Command\Threads\ThreadsStopCommand',
            'RuntimeExistsCommand'      => 'Kraken\Runtime\Container\Command\Runtime\RuntimeExistsCommand',
            'RuntimeDestroyCommand'     => 'Kraken\Runtime\Container\Command\Runtime\RuntimeDestroyCommand',
            'RuntimeStartCommand'       => 'Kraken\Runtime\Container\Command\Runtime\RuntimeStartCommand',
            'RuntimeStopCommand'        => 'Kraken\Runtime\Container\Command\Runtime\RuntimeStopCommand',
            'RuntimesGetCommand'        => 'Kraken\Runtime\Container\Command\Runtimes\RuntimesGetCommand',
            'RuntimesDestroyCommand'    => 'Kraken\Runtime\Container\Command\Runtimes\RuntimesDestroyCommand',
            'RuntimesStartCommand'      => 'Kraken\Runtime\Container\Command\Runtimes\RuntimesStartCommand',
            'RuntimesStopCommand'       => 'Kraken\Runtime\Container\Command\Runtimes\RuntimesStopCommand',
            'ContainerContinueCommand'  => 'Kraken\Runtime\Container\Command\Container\ContainerContinueCommand',
            'ContainerDestroyCommand'   => 'Kraken\Runtime\Container\Command\Container\ContainerDestroyCommand',
            'ContainerStartCommand'     => 'Kraken\Runtime\Container\Command\Container\ContainerStartCommand',
            'ContainerStopCommand'      => 'Kraken\Runtime\Container\Command\Container\ContainerStopCommand',
            'ContainerStatusCommand'    => 'Kraken\Runtime\Container\Command\Container\ContainerStatusCommand',
            'CmdErrorCommand'           => 'Kraken\Runtime\Container\Command\Cmd\CmdErrorCommand',
            'CmdPingCommand'            => 'Kraken\Runtime\Container\Command\Cmd\CmdPingCommand',
        ];

        foreach ($commands as $alias=>$class)
        {
            $this->registerCommand($alias, $class);
        }
    }

    /**
     * @param string $alias
     * @param string $class
     */
    protected function registerCommand($alias, $class)
    {
        $this
            ->define($alias, function($runtime, $context = []) use($class) {
                return new $class($runtime, $context);
            })
            ->define($class, function($runtime, $context = []) use($class) {
                return new $class($runtime, $context);
            })
        ;
    }
}
