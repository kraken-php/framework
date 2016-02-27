<?php

namespace Kraken\Runtime\Boot;

use ReflectionClass;
use Kraken\Runtime\RuntimeInterface;
use Kraken\Support\StringSupport;

class ThreadBoot
{
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
     *
     */
    public function __construct()
    {
        $this->controllerParams = [];
        $this->controllerClass = '\\%prefix%\\Thread\\%name%\\%name%Controller';
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
        unset($this->controllerParams);
        unset($this->controllerClass);
        unset($this->params);
    }

    /**
     * @param string $class
     * @return ThreadBoot
     */
    public function controller($class)
    {
        $this->controllerClass = $class;

        return $this;
    }

    /**
     * @param mixed[] $args
     * @return ThreadBoot
     */
    public function constructor($args)
    {
        $this->controllerParams = $args;

        return $this;
    }

    /**
     * @param string[] $params
     * @return ThreadBoot
     */
    public function params($params)
    {
        $this->params = array_merge($this->params, $params);

        return $this;
    }

    /**
     * @param string $path
     * @return RuntimeInterface
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

        if (file_exists($datapath . '/bootstrap/' . $controller->name() . '/bootstrap.php'))
        {
            $core = require(
                $datapath . '/bootstrap/' . $controller->name() . '/bootstrap.php'
            );
        }
        else
        {
            $core = require(
                $datapath . '/bootstrap-global/Thread/bootstrap.php'
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
            ->internalConstruct($core);

        return $controller;
    }
}
