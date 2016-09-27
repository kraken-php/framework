<?php

namespace Kraken\Root\Runtime\Boot;

use Kraken\Runtime\Container\Thread\ThreadController;
use Kraken\Runtime\RuntimeContainerInterface;
use Kraken\Throwable\Exception\Logic\InstantiationException;
use Kraken\Util\Support\StringSupport;
use Exception;
use ReflectionClass;

class ThreadBoot
{
    /**
     * @var ThreadController
     */
    protected $threadController;

    /**
     * @var mixed[]
     */
    protected $controllerParams;

    /**
     * @var string|string[]
     */
    protected $controllerClass;

    /**
     * @var string[]
     */
    protected $params;

    /**
     * @param ThreadController $threadController
     */
    public function __construct(ThreadController $threadController = null)
    {
        global $loader;

        $this->runtimeController = ($threadController !== null) ? $threadController : new ThreadController($loader);
        $this->controllerParams = [];
        $this->controllerClass  = [
            '\\%prefix%\\Thread\\Container\\%name%\\%name%Container',
            '\\%prefix%\\Runtime\\Container\\%name%\\%name%Container'
        ];
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
     * @param string|string[] $class
     * @return ThreadBoot
     */
    public function controller($class)
    {
        $this->controllerClass = (array) $class;

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
     * @return RuntimeContainerInterface
     * @throws Exception
     */
    public function boot($path)
    {
        $controllerClass = '';
        $controllerClassFound = false;

        foreach ($this->controllerClass as $controllerClass)
        {
            $controllerClass = StringSupport::parametrize($controllerClass, $this->params);

            if (class_exists($controllerClass))
            {
                $controllerClassFound = true;
                break;
            }
        }

        if (!$controllerClassFound)
        {
            throw new InstantiationException('Runtime class not found');
        }

        $controller = (new ReflectionClass($controllerClass))->newInstanceArgs($this->controllerParams);
        $datapath = realpath($path);

        if (file_exists($datapath . '/bootstrap/' . $controller->getName() . '/bootstrap.php'))
        {
            $core = require(
                $datapath . '/bootstrap/' . $controller->getName() . '/bootstrap.php'
            );
        }
        else
        {
            $core = require(
                $datapath . '/bootstrap/Thread/bootstrap.php'
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
