<?php

namespace Kraken\Supervision;

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
            'CmdDoNothing'          => 'Kraken\Supervision\Handler\Cmd\CmdDoNothing',
            'CmdEscalateManager'    => 'Kraken\Supervision\Handler\Cmd\CmdEscalateManager',
            'CmdEscalateSupervisor' => 'Kraken\Supervision\Handler\Cmd\CmdEscalateSupervisor',
            'CmdLog'                => 'Kraken\Supervision\Handler\Cmd\CmdLog',
            'RuntimeContinue'       => 'Kraken\Supervision\Handler\Runtime\RuntimeContinue',
            'RuntimeDestroy'        => 'Kraken\Supervision\Handler\Runtime\RuntimeDestroy',
            'RuntimeDestroySoft'    => 'Kraken\Supervision\Handler\Runtime\RuntimeDestroySoft',
            'RuntimeDestroyHard'    => 'Kraken\Supervision\Handler\Runtime\RuntimeDestroyHard',
            'RuntimeRecreate'       => 'Kraken\Supervision\Handler\Runtime\RuntimeRecreate',
            'RuntimeStart'          => 'Kraken\Supervision\Handler\Runtime\RuntimeStart',
            'RuntimeStop'           => 'Kraken\Supervision\Handler\Runtime\RuntimeStop',
            'ContainerContinue'     => 'Kraken\Supervision\Handler\Container\ContainerContinue',
            'ContainerDestroy'      => 'Kraken\Supervision\Handler\Container\ContainerDestroy',
            'ContainerStart'        => 'Kraken\Supervision\Handler\Container\ContainerStart',
            'ContainerStop'         => 'Kraken\Supervision\Handler\Container\ContainerStop'
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
