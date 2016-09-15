<?php

namespace Kraken\_Unit\Runtime\Command;

use Kraken\Runtime\Supervision\SolverFactory;
use Kraken\Runtime\RuntimeContainerInterface;
use Kraken\Test\TUnit;

class SolverFactoryTest extends TUnit
{
    /**
     *
     */
    public function testCaseFactory_PossesAllDefinitions()
    {
        $runtime  = $this->getMock(RuntimeContainerInterface::class, [], [], '', false);
        $factory  = new SolverFactory($runtime);
        $commands = [
            'CmdDoNothing'          => 'Kraken\Runtime\Supervision\Cmd\CmdDoNothing',
            'CmdEscalateManager'    => 'Kraken\Runtime\Supervision\Cmd\CmdEscalateManager',
            'CmdEscalateSupervisor' => 'Kraken\Runtime\Supervision\Cmd\CmdEscalateSupervisor',
            'CmdLog'                => 'Kraken\Runtime\Supervision\Cmd\CmdLog',
            'RuntimeContinue'       => 'Kraken\Runtime\Supervision\Runtime\RuntimeContinue',
            'RuntimeDestroy'        => 'Kraken\Runtime\Supervision\Runtime\RuntimeDestroy',
            'RuntimeDestroySoft'    => 'Kraken\Runtime\Supervision\Runtime\RuntimeDestroySoft',
            'RuntimeDestroyHard'    => 'Kraken\Runtime\Supervision\Runtime\RuntimeDestroyHard',
            'RuntimeRecreate'       => 'Kraken\Runtime\Supervision\Runtime\RuntimeRecreate',
            'RuntimeStart'          => 'Kraken\Runtime\Supervision\Runtime\RuntimeStart',
            'RuntimeStop'           => 'Kraken\Runtime\Supervision\Runtime\RuntimeStop',
            'ContainerContinue'     => 'Kraken\Runtime\Supervision\Container\ContainerContinue',
            'ContainerDestroy'      => 'Kraken\Runtime\Supervision\Container\ContainerDestroy',
            'ContainerStart'        => 'Kraken\Runtime\Supervision\Container\ContainerStart',
            'ContainerStop'         => 'Kraken\Runtime\Supervision\Container\ContainerStop'
        ];

        foreach ($commands as $alias=>$class)
        {
            $this->assertTrue($factory->hasDefinition($alias));
            $this->assertTrue($factory->hasDefinition($class));
        }
    }
}
