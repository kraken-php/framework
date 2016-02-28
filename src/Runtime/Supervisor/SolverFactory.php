<?php

namespace Kraken\Runtime\Supervisor;

use Kraken\Runtime\RuntimeInterface;
use Kraken\Supervisor\SolverFactoryInterface;
use Kraken\Util\Factory\Factory;

class SolverFactory extends Factory implements SolverFactoryInterface
{
    /**
     * @var RuntimeInterface
     */
    protected $runtime;

    /**
     * @param RuntimeInterface $runtime
     */
    public function __construct(RuntimeInterface $runtime)
    {
        parent::__construct();

        $this->runtime = $runtime;

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
     *
     */
    public function __destruct()
    {
        unset($this->runtime);
    }

    /**
     * @param string $name
     */
    private function registerHandler($name, $class)
    {
        $runtime = $this->runtime;
        $this
            ->define($name, function($context = []) use($class, $runtime) {
                return new $class(array_merge(
                    [ 'runtime' => $runtime ],
                    $context
                ));
            })
            ->define($class, function($context = []) use($class, $runtime) {
                return new $class(array_merge(
                    [ 'runtime' => $runtime ],
                    $context
                ));
            })
        ;
    }
}
