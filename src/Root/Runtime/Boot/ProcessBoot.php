<?php

namespace Kraken\Root\Runtime\Boot;

use Kraken\Runtime\Container\Process\ProcessController;
use Kraken\Runtime\RuntimeContainerInterface;
use Kraken\Util\Support\StringSupport;
use ReflectionClass;

class ProcessBoot
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
        $this->controllerParams = [];
        $this->controllerClass = '\\%prefix%\\Process\\%name%\\%name%Container';
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
     * @return ProcessBoot
     */
    public function controller($class)
    {
        $this->controllerClass = $class;

        return $this;
    }

    /**
     * @param mixed[] $args
     * @return ProcessBoot
     */
    public function constructor($args)
    {
        $this->controllerParams = $args;

        return $this;
    }

    /**
     * @param string[] $params
     * @return ProcessBoot
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
        $controller = (new ReflectionClass(
            StringSupport::parametrize($this->controllerClass, $this->params)
        ))
        ->newInstanceArgs(
            array_merge($this->controllerParams)
        );

        if (file_exists($datapath . '/bootstrap/' . $controller->getName() . '/bootstrap.php'))
        {
            $core = require(
                $datapath . '/bootstrap/' . $controller->getName() . '/bootstrap.php'
            );
        }
        else
        {
            $core = require(
                $datapath . '/bootstrap/Process/bootstrap.php'
            );
        }

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
