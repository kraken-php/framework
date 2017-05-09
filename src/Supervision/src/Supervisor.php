<?php

namespace Kraken\Supervision;

use Kraken\Promise\Promise;
use Kraken\Throwable\Exception\Runtime\ExecutionException;
use Kraken\Throwable\Exception\Logic\IllegalCallException;
use Error;
use Exception;

class Supervisor implements SupervisorInterface
{
    /**
     * @var SolverFactoryInterface
     */
    protected $factory;

    /**
     * @var mixed[]
     */
    protected $params;

    /**
     * @var SolverInterface[]
     */
    protected $rules;

    /**
     * @param SolverFactoryInterface $factory
     * @param mixed[] $params
     * @param SolverInterface[]|string[] $rules
     */
    public function __construct(SolverFactoryInterface $factory, $params = [], $rules = [])
    {
        $this->factory = $factory;
        $this->params = [];
        $this->rules = [];

        foreach ($params as $paramKey=>$paramValue)
        {
            $this->setParam($paramKey, $paramValue);
        }

        foreach ($rules as $ruleException=>$ruleHandler)
        {
            $this->setSolver($ruleException, $ruleHandler);
        }
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->factory);
        unset($this->params);
        unset($this->rules);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function __invoke($ex, $params = [])
    {
        return $this->solve($ex, $params);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function existsParam($key)
    {
        return array_key_exists($key, $this->params);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function setParam($key, $value)
    {
        $this->params[$key] = $value;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getParam($key)
    {
        return $this->existsParam($key) ? $this->params[$key] : null;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function removeParam($key)
    {
        unset($this->params[$key]);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function existsSolver($exception)
    {
        return isset($this->rules[$exception]);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function setSolver($exception, $handler)
    {
        if (is_array($handler))
        {
            $names = $handler;
            $handlers = [];
            foreach ($names as $name)
            {
                $handlers[] = $this->resolveHandler($name);
            }

            $handler = new SolverComposite($handlers);
        }

        $this->rules[$exception] = $this->resolveHandler($handler);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getSolver($exception)
    {
        return $this->existsSolver($exception) ? $this->rules[$exception] : null;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function removeSolver($exception)
    {
        unset($this->rules[$exception]);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function solve($ex, $params = [], &$try = 0)
    {
        $classBaseEx = get_class($ex);
        $classes = array_merge([ $classBaseEx ], class_parents($ex));

        $indexMin = -1;
        $chosen = null;
        foreach ($classes as $class)
        {
            $indexCurrent = array_search($class, array_keys($this->rules), true);
            if ($indexCurrent !== false && ($indexMin === -1 || $indexCurrent < $indexMin))
            {
                $indexMin = $indexCurrent;
                $chosen = $class;
            }
        }

        if ($chosen === null)
        {
            return Promise::doReject(
                new ExecutionException("SolverInterface for [$classBaseEx] is not registered.")
            );
        }

        $try++;
        $params = array_merge($this->params, $params);
        $valueOrPromise = $this->getSolver($chosen)->solve($ex, $params);

        return Promise::doResolve($valueOrPromise);
    }

    /**
     * Resolve handler.
     *
     * This method returns passed argument if it is instance of SolverInterface or newly created object of passed class
     * if the $solverOrName argument was string.
     *
     * IllegalCallException is thrown if passed argument is string of invalid class.
     *
     * @param SolverInterface|string $solverOrName
     * @return SolverInterface
     * @throws IllegalCallException
     */
    protected function resolveHandler($solverOrName)
    {
        if (!is_string($solverOrName))
        {
            return $solverOrName;
        }

        $ex = null;
        $handler = null;

        try
        {
            $handler = $this->factory->create($solverOrName);
        }
        catch (Error $ex)
        {}
        catch (Exception $ex)
        {}

        if ($ex !== null)
        {
            throw new IllegalCallException("Tried to invoke [$solverOrName] which is undefined.");
        }

        return $handler;
    }
}
