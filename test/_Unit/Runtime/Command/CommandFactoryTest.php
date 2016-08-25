<?php

namespace Kraken\_Unit\Runtime\Command;

use Kraken\Runtime\Command\CommandFactory;
use Kraken\Test\TUnit;

class CommandFactoryTest extends TUnit
{
    /**
     *
     */
    public function testCaseFactory_PossesAllDefinitions()
    {
        $factory  = new CommandFactory();
        $commands = [
            'ArchStartCommand'          => 'Kraken\Runtime\Command\Arch\ArchStartCommand',
            'ArchStopCommand'           => 'Kraken\Runtime\Command\Arch\ArchStopCommand',
            'ArchStatusCommand'         => 'Kraken\Runtime\Command\Arch\ArchStatusCommand',
            'ProcessExistsCommand'      => 'Kraken\Runtime\Command\Process\ProcessExistsCommand',
            'ProcessCreateCommand'      => 'Kraken\Runtime\Command\Process\ProcessCreateCommand',
            'ProcessDestroyCommand'     => 'Kraken\Runtime\Command\Process\ProcessDestroyCommand',
            'ProcessStartCommand'       => 'Kraken\Runtime\Command\Process\ProcessStartCommand',
            'ProcessStopCommand'        => 'Kraken\Runtime\Command\Process\ProcessStopCommand',
            'ProcessesGetCommand'       => 'Kraken\Runtime\Command\Processes\ProcessesGetCommand',
            'ProcessesCreateCommand'    => 'Kraken\Runtime\Command\Processes\ProcessesCreateCommand',
            'ProcessesDestroyCommand'   => 'Kraken\Runtime\Command\Processes\ProcessesDestroyCommand',
            'ProcessesStartCommand'     => 'Kraken\Runtime\Command\Processes\ProcessesStartCommand',
            'ProcessesStopCommand'      => 'Kraken\Runtime\Command\Processes\ProcessesStopCommand',
            'ThreadExistsCommand'       => 'Kraken\Runtime\Command\Thread\ThreadExistsCommand',
            'ThreadCreateCommand'       => 'Kraken\Runtime\Command\Thread\ThreadCreateCommand',
            'ThreadDestroyCommand'      => 'Kraken\Runtime\Command\Thread\ThreadDestroyCommand',
            'ThreadStartCommand'        => 'Kraken\Runtime\Command\Thread\ThreadStartCommand',
            'ThreadStopCommand'         => 'Kraken\Runtime\Command\Thread\ThreadStopCommand',
            'ThreadsGetCommand'         => 'Kraken\Runtime\Command\Threads\ThreadsGetCommand',
            'ThreadsCreateCommand'      => 'Kraken\Runtime\Command\Threads\ThreadsCreateCommand',
            'ThreadsDestroyCommand'     => 'Kraken\Runtime\Command\Threads\ThreadsDestroyCommand',
            'ThreadsStartCommand'       => 'Kraken\Runtime\Command\Threads\ThreadsStartCommand',
            'ThreadsStopCommand'        => 'Kraken\Runtime\Command\Threads\ThreadsStopCommand',
            'RuntimeExistsCommand'      => 'Kraken\Runtime\Command\Runtime\RuntimeExistsCommand',
            'RuntimeDestroyCommand'     => 'Kraken\Runtime\Command\Runtime\RuntimeDestroyCommand',
            'RuntimeStartCommand'       => 'Kraken\Runtime\Command\Runtime\RuntimeStartCommand',
            'RuntimeStopCommand'        => 'Kraken\Runtime\Command\Runtime\RuntimeStopCommand',
            'RuntimesGetCommand'        => 'Kraken\Runtime\Command\Runtimes\RuntimesGetCommand',
            'RuntimesDestroyCommand'    => 'Kraken\Runtime\Command\Runtimes\RuntimesDestroyCommand',
            'RuntimesStartCommand'      => 'Kraken\Runtime\Command\Runtimes\RuntimesStartCommand',
            'RuntimesStopCommand'       => 'Kraken\Runtime\Command\Runtimes\RuntimesStopCommand',
            'ContainerContinueCommand'  => 'Kraken\Runtime\Command\Container\ContainerContinueCommand',
            'ContainerDestroyCommand'   => 'Kraken\Runtime\Command\Container\ContainerDestroyCommand',
            'ContainerStartCommand'     => 'Kraken\Runtime\Command\Container\ContainerStartCommand',
            'ContainerStopCommand'      => 'Kraken\Runtime\Command\Container\ContainerStopCommand',
            'ContainerStatusCommand'    => 'Kraken\Runtime\Command\Container\ContainerStatusCommand',
            'CmdErrorCommand'           => 'Kraken\Runtime\Command\Cmd\CmdErrorCommand',
            'CmdPingCommand'            => 'Kraken\Runtime\Command\Cmd\CmdPingCommand',
        ];

        foreach ($commands as $alias=>$class)
        {
            $this->assertTrue($factory->hasDefinition($alias));
            $this->assertTrue($factory->hasDefinition($class));
        }
    }
}
