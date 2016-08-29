<?php

namespace Kraken\_Unit\Runtime\Command;

use Kraken\Runtime\Supervisor\SolverFactory;
use Kraken\Runtime\RuntimeInterface;
use Kraken\Test\TUnit;

class SolverFactoryTest extends TUnit
{
    /**
     *
     */
    public function testCaseFactory_PossesAllDefinitions()
    {
        $runtime  = $this->getMock(RuntimeInterface::class, [], [], '', false);
        $factory  = new SolverFactory($runtime);
        $commands = [
            'CmdDoNothing'          => 'Kraken\Runtime\Supervisor\Cmd\CmdDoNothing',
            'CmdEscalateManager'    => 'Kraken\Runtime\Supervisor\Cmd\CmdEscalateManager',
            'CmdEscalateSupervisor' => 'Kraken\Runtime\Supervisor\Cmd\CmdEscalateSupervisor',
            'CmdLog'                => 'Kraken\Runtime\Supervisor\Cmd\CmdLog',
            'RuntimeContinue'       => 'Kraken\Runtime\Supervisor\Runtime\RuntimeContinue',
            'RuntimeDestroy'        => 'Kraken\Runtime\Supervisor\Runtime\RuntimeDestroy',
            'RuntimeDestroySoft'    => 'Kraken\Runtime\Supervisor\Runtime\RuntimeDestroySoft',
            'RuntimeDestroyHard'    => 'Kraken\Runtime\Supervisor\Runtime\RuntimeDestroyHard',
            'RuntimeRecreate'       => 'Kraken\Runtime\Supervisor\Runtime\RuntimeRecreate',
            'RuntimeStart'          => 'Kraken\Runtime\Supervisor\Runtime\RuntimeStart',
            'RuntimeStop'           => 'Kraken\Runtime\Supervisor\Runtime\RuntimeStop',
            'ContainerContinue'     => 'Kraken\Runtime\Supervisor\Container\ContainerContinue',
            'ContainerDestroy'      => 'Kraken\Runtime\Supervisor\Container\ContainerDestroy',
            'ContainerStart'        => 'Kraken\Runtime\Supervisor\Container\ContainerStart',
            'ContainerStop'         => 'Kraken\Runtime\Supervisor\Container\ContainerStop'
        ];

        foreach ($commands as $alias=>$class)
        {
            $this->assertTrue($factory->hasDefinition($alias));
            $this->assertTrue($factory->hasDefinition($class));
        }
    }
}
