<?php

namespace Kraken\Root\Console\Client\Boot;

use Kraken\Console\Client\ClientInterface;
use Kraken\Util\Support\StringSupport;
use ReflectionClass;

class ClientBoot
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
        $this->controllerClass = '\\Kraken\\Console\\Client\\Client';
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
     * @return Client
     */
    public function controller($class)
    {
        $this->controllerClass = $class;

        return $this;
    }

    /**
     * @param mixed[] $args
     * @return Client
     */
    public function constructor($args)
    {
        $this->controllerParams = $args;

        return $this;
    }

    /**
     * @param string[] $params
     * @return Client
     */
    public function params($params)
    {
        $this->params = array_merge($this->params, $params);

        return $this;
    }

    /**
     * @param string $path
     * @return ClientInterface
     */
    public function boot($path)
    {
        $core = require(
            realpath($path) . '/bootstrap/Console/Client/bootstrap.php'
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
