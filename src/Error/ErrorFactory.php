<?php

namespace Kraken\Error;

use Kraken\Pattern\Factory\Factory;

class ErrorFactory extends Factory implements ErrorFactoryInterface
{
    /**
     *
     */
    public function __construct()
    {
        parent::__construct();

        $handlers = [
            'CmdDoNothing'          => 'Kraken\Error\Handler\Cmd\CmdDoNothing',
            'CmdEscalateManager'    => 'Kraken\Error\Handler\Cmd\CmdEscalateManager',
            'CmdEscalateSupervisor' => 'kraken\Error\Handler\Cmd\CmdEscalateSupervisor',
            'CmdLog'                => 'Kraken\Error\Handler\Cmd\CmdLog',
            'RuntimeContinue'       => 'Kraken\Error\Handler\Runtime\RuntimeContinue',
            'RuntimeDestroy'        => 'Kraken\Error\Handler\Runtime\RuntimeDestroy',
            'RuntimeDestroySoft'    => 'Kraken\Error\Handler\Runtime\RuntimeDestroySoft',
            'RuntimeDestroyHard'    => 'Kraken\Error\Handler\Runtime\RuntimeDestroyHard',
            'RuntimeRecreate'       => 'Kraken\Error\Handler\Runtime\RuntimeRecreate',
            'RuntimeStart'          => 'Kraken\Error\Handler\Runtime\RuntimeStart',
            'RuntimeStop'           => 'Kraken\Error\Handler\Runtime\RuntimeStop',
            'ContainerContinue'     => 'Kraken\Error\Handler\Container\ContainerContinue',
            'ContainerDestroy'      => 'Kraken\Error\Handler\Container\ContainerDestroy',
            'ContainerStart'        => 'Kraken\Error\Handler\Container\ContainerStart',
            'ContainerStop'         => 'Kraken\Error\Handler\Container\ContainerStop'
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
