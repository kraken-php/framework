<?php

namespace Kraken\Console\Client\Boot;

use ReflectionClass;
use Kraken\Console\Client\ConsoleClientInterface;
use Kraken\Support\StringSupport;

class ConsoleBoot
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
        $this->controllerClass = '\\Kraken\\Console\\Client\\ConsoleClient';
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
     * @return ConsoleClientInterface
     */
    public function boot($path)
    {
        $core = require(
            realpath($path) . '/bootstrap/ConsoleClient/bootstrap.php'
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
            ->internalConstruct($core);

        return $controller;
    }
}
