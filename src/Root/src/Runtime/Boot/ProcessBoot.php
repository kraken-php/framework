<?php

namespace Kraken\Root\Runtime\Boot;

use Kraken\Runtime\Container\Process\ProcessController;
use Kraken\Runtime\Runtime;
use Kraken\Runtime\RuntimeContainerInterface;
use Kraken\Throwable\Exception\Logic\InstantiationException;
use Kraken\Util\Support\StringSupport;
use Exception;
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
     * @var string[]
     */
    protected $controllerPatterns;

    /**
     * @var string[]
     */
    protected $bootstrapPatterns;

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
        $this->controllerPatterns = [
            '\\%prefix%\\Process\\Container\\%name%\\%name%Container',
            '\\%prefix%\\Runtime\\Container\\%name%\\%name%Container'
        ];
        $this->bootstrapPatterns = [
            '%datapath%/bootstrap/Process/%name%/bootstrap.php',
            '%datapath%/bootstrap/Runtime/%name%/bootstrap.php',
            '%datapath%/bootstrap/Process/bootstrap.php',
            '%datapath%/bootstrap/Runtime/bootstrap.php'
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
        unset($this->controllerPatterns);
        unset($this->boostrapPatterns);
        unset($this->params);
    }

    /**
     * @param string|string[] $patternOrPatterns
     * @return ProcessBoot
     */
    public function controllers($patternOrPatterns)
    {
        $this->controllerPatterns = (array) $patternOrPatterns;

        return $this;
    }

    /**
     * @param string|string[] $patternOrPatterns
     * @return ProcessBoot
     */
    public function bootstraps($patternOrPatterns)
    {
        $this->bootstrapPatterns = (array) $patternOrPatterns;

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
     * @throws Exception
     */
    public function boot($path)
    {
        $controllerClass = '';
        $controllerClassFound = false;

        foreach ($this->controllerPatterns as $controllerClass)
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

        $params = array_merge(
            [
                'type'      => $type = Runtime::UNIT_PROCESS,
                'datapath'  => realpath($path)
            ],
            $this->params
        );

        $bootstrapFile = '';
        $bootstrapFileFound = false;

        foreach ($this->bootstrapPatterns as $bootstrapFile)
        {
            $bootstrapFile = StringSupport::parametrize($bootstrapFile, $params);

            if (file_exists($bootstrapFile))
            {
                $bootstrapFileFound = true;
                break;
            }
        }

        if (!$bootstrapFileFound)
        {
            throw new InstantiationException('Bootstrap file not found');
        }

        $core = require($bootstrapFile);

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
