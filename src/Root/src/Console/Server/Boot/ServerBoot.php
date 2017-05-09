<?php

namespace Kraken\Root\Console\Server\Boot;

use Kraken\Runtime\Container\Process\ProcessController;
use Kraken\Runtime\RuntimeContainerInterface;
use Kraken\Util\Support\StringSupport;
use ReflectionClass;

class ServerBoot
{
    /**
     * @var ProcessController
     */
    protected $runtimeController;

    /**
     * @var mixed[]
     */
    protected $controllerParams;

    /**
     * @var string
     */
    protected $controllerClass;

    /**
     * @var string[]
     */
    protected $params;

    /**
     * @param ProcessController $runtimeController
     */
    public function __construct(ProcessController $runtimeController = null)
    {
        global $loader;

        $this->runtimeController = ($runtimeController !== null) ? $runtimeController : new ProcessController($loader);
        $this->controllerParams = [
            null,
            'Server',
            'Server'
        ];
        $this->controllerClass = '\\Kraken\\Console\\Server\\Server';
        $this->params = [
            'prefix' => 'Kraken',
            'name'   => 'Undefined'
        ];
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->runtimeController);
        unset($this->controllerParams);
        unset($this->controllerClass);
        unset($this->params);
    }

    /**
     * @param string $class
     * @return ServerBoot
     */
    public function controller($class)
    {
        $this->controllerClass = $class;

        return $this;
    }

    /**
     * @param mixed[] $args
     * @return ServerBoot
     */
    public function constructor($args)
    {
        $this->controllerParams = $args;

        return $this;
    }

    /**
     * @param string[] $params
     * @return ServerBoot
     */
    public function params($params)
    {
        $this->params = array_merge($this->params, $params);

        return $this;
    }

    /**
     * @param string $path
     * @return RuntimeContainerInterface
     */
    public function boot($path)
    {
        $datapath = realpath($path);
        $core = require(
            $datapath . '/bootstrap/Console/Server/bootstrap.php'
        );

        $controller = (new ReflectionClass(
            StringSupport::parametrize($this->controllerClass, $this->params)
        ))
        ->newInstanceArgs(
            array_merge($this->controllerParams)
        );

        $controller
            ->setCore($core);

        $core->config(
            $controller->internalConfig($core)
        );

        $controller
            ->internalBoot($core);

        $core
            ->boot();

        $controller
            ->getLoop()
            ->setFlowController($this->runtimeController);

        $controller
            ->internalConstruct($core);

        return $controller;
    }
}
