<?php

namespace Kraken\Runtime\Supervision;

use Kraken\Runtime\RuntimeContainerInterface;
use Kraken\Supervision\SolverFactoryInterface;
use Kraken\Util\Factory\Factory;

class SolverFactory extends Factory implements SolverFactoryInterface
{
    /**
     * @var RuntimeContainerInterface
     */
    protected $runtime;

    /**
     * @param RuntimeContainerInterface $runtime
     */
    public function __construct(RuntimeContainerInterface $runtime)
    {
        parent::__construct();

        $this->runtime = $runtime;

        $handlers = [
            'CmdDoNothing'          => 'Kraken\Runtime\Supervision\Cmd\CmdDoNothing',
            'CmdEscalate'           => 'Kraken\Runtime\Supervision\Cmd\CmdEscalate',
            'CmdSolve'              => 'Kraken\Runtime\Supervision\Cmd\CmdSolve',
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
