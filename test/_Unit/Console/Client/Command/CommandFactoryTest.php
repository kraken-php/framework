<?php

namespace Kraken\_Unit\Runtime\Command;

use Kraken\Console\Client\Command\CommandFactory;
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
            'ArchStartCommand'          => 'Kraken\Console\Client\Command\Arch\ArchStartCommand',
            'ArchStopCommand'           => 'Kraken\Console\Client\Command\Arch\ArchStopCommand',
            'ArchStatusCommand'         => 'Kraken\Console\Client\Command\Arch\ArchStatusCommand',
            'ProjectCreateCommand'      => 'Kraken\Console\Client\Command\Project\ProjectCreateCommand',
            'ProjectDestroyCommand'     => 'Kraken\Console\Client\Command\Project\ProjectDestroyCommand',
            'ProjectStartCommand'       => 'Kraken\Console\Client\Command\Project\ProjectStartCommand',
            'ProjectStopCommand'        => 'Kraken\Console\Client\Command\Project\ProjectStopCommand',
            'ProjectStatusCommand'      => 'Kraken\Console\Client\Command\Project\ProjectStatusCommand',
            'ProcessExistsCommand'      => 'Kraken\Console\Client\Command\Process\ProcessExistsCommand',
            'ProcessCreateCommand'      => 'Kraken\Console\Client\Command\Process\ProcessCreateCommand',
            'ProcessDestroyCommand'     => 'Kraken\Console\Client\Command\Process\ProcessDestroyCommand',
            'ProcessStartCommand'       => 'Kraken\Console\Client\Command\Process\ProcessStartCommand',
            'ProcessStopCommand'        => 'Kraken\Console\Client\Command\Process\ProcessStopCommand',
            'ThreadExistsCommand'       => 'Kraken\Console\Client\Command\Thread\ThreadExistsCommand',
            'ThreadCreateCommand'       => 'Kraken\Console\Client\Command\Thread\ThreadCreateCommand',
            'ThreadDestroyCommand'      => 'Kraken\Console\Client\Command\Thread\ThreadDestroyCommand',
            'ThreadStartCommand'        => 'Kraken\Console\Client\Command\Thread\ThreadStartCommand',
            'ThreadStopCommand'         => 'Kraken\Console\Client\Command\Thread\ThreadStopCommand',
            'RuntimeExistsCommand'      => 'Kraken\Console\Client\Command\Runtime\RuntimeExistsCommand',
            'RuntimeDestroyCommand'     => 'Kraken\Console\Client\Command\Runtime\RuntimeDestroyCommand',
            'RuntimeStartCommand'       => 'Kraken\Console\Client\Command\Runtime\RuntimeStartCommand',
            'RuntimeStopCommand'        => 'Kraken\Console\Client\Command\Runtime\RuntimeStopCommand',
            'ContainerDestroyCommand'   => 'Kraken\Console\Client\Command\Container\ContainerDestroyCommand',
            'ContainerStartCommand'     => 'Kraken\Console\Client\Command\Container\ContainerStartCommand',
            'ContainerStopCommand'      => 'Kraken\Console\Client\Command\Container\ContainerStopCommand',
            'ContainerStatusCommand'    => 'Kraken\Console\Client\Command\Container\ContainerStatusCommand'
        ];

        foreach ($commands as $alias=>$class)
        {
            $this->assertTrue($factory->hasDefinition($alias));
            $this->assertTrue($factory->hasDefinition($class));
        }
    }
}
