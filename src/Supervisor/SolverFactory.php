<?php

namespace Kraken\Supervisor;

use Kraken\Util\Factory\Factory;

class SolverFactory extends Factory implements SolverFactoryInterface
{
    /**
     *
     */
    public function __construct()
    {
        parent::__construct();

        $handlers = [
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

        foreach ($handlers as $handlerName=>$handlerClass)
        {
            $this->registerHandler($handlerName, $handlerClass);
        }
    }

    /**
     * @param string $name
     */
    private function registerHandler($name, $class)
    {
        $this
            ->define($name, function($runtime, $context = []) use($class) {
                return new $class($runtime, $context);
            })
            ->define($class, function($runtime, $context = []) use($class) {
                return new $class($runtime, $context);
            })
        ;
    }
}
