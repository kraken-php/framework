<?php

namespace Kraken\Error;

use Kraken\Promise\Promise;
use Kraken\Promise\PromiseInterface;
use Kraken\Throwable\Exception\Runtime\ExecutionException;
use Kraken\Throwable\Exception\Logic\IllegalCallException;
use Kraken\Throwable\Exception\LogicException;
use Kraken\Runtime\RuntimeInterface;
use Error;
use Exception;

class ErrorManager implements ErrorManagerInterface
{
    /**
     * @var RuntimeInterface
     */
    protected $runtime;

    /**
     * @var mixed[]
     */
    protected $params;

    /**
     * @var ErrorHandlerInterface[]
     */
    protected $rules;

    /**
     * @param RuntimeInterface $runtime
     * @param mixed[] $params
     * @param ErrorHandlerInterface[] $rules
     */
    public function __construct(RuntimeInterface $runtime, $params = [], $rules = [])
    {
        $this->runtime = $runtime;

        foreach ($params as $paramKey=>$paramValue)
        {
            $this->setParam($paramKey, $paramValue);
        }

        foreach ($rules as $ruleException=>$ruleHandler)
        {
            $this->setHandler($ruleException, $ruleHandler);
        }
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->runtime);
        unset($this->params);
        unset($this->rules);
    }

    /**
     * @param Error|Exception $ex
     * @param mixed[] $params
     * @return PromiseInterface
     * @throws ExecutionException
     */
    public function __invoke($ex, $params = [])
    {
        return $this->handle($ex, $params);
    }

    /**
     * @param string $key
     * @return bool
     */
    public function existsParam($key)
    {
        return isset($this->params[$key]);
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    public function setParam($key, $value)
    {
        $this->params[$key] = $value;
    }

    /**
     * @param string $key
     * @return mixed|null $value
     */
    public function getParam($key)
    {
        return $this->existsParam($key) ? $this->params[$key] : null;
    }

    /**
     * @param string $exception
     * @return bool
     */
    public function existsHandler($exception)
    {
        return isset($this->rules[$exception]);
    }

    /**
     * @param string $exception
     * @param ErrorHandlerInterface|string|string[] $handler
     * @throws LogicException
     */
    public function setHandler($exception, $handler)
    {
        if (is_string($handler))
        {
            $handler = $this->resolveHandler($handler);
        }
        else if (is_array($handler))
        {
            $names = $handler;
            $handlers = [];
            foreach ($names as $name)
            {
                $handlers[] = $this->resolveHandler($name);
            }

            $handler = new ErrorHandlerComposite($handlers);
        }

        $this->rules[$exception] = $handler;
    }

    /**
     * @param string $exception
     * @return ErrorHandlerInterface|null
     */
    public function getHandler($exception)
    {
        return $this->existsHandler($exception) ? $this->rules[$exception] : null;
    }

    /**
     * @param string $exception
     */
    public function removeHandler($exception)
    {
        unset($this->rules[$exception]);
    }

    /**
     * @param Error|Exception $ex
     * @param mixed[] $params
     * @param int $try
     * @return PromiseInterface
     */
    public function handle($ex, $params = [], &$try = 0)
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
                new ExecutionException("ErrorHandlerInterface [$classBaseEx] is not registered.")
            );
        }

        $try++;
        $params = array_merge($this->params, $params);
        $valueOrPromise = $this->getHandler($chosen)->handle($ex, $params);

        return Promise::doResolve($valueOrPromise);
    }

    /**
     * @param string $name
     * @return ErrorHandlerInterface
     * @throws LogicException
     */
    protected function resolveHandler($name)
    {
        if (!isset($this->params['factory']))
        {
            throw new LogicException('Tried to invoke handler as string without having set factory.');
        }

        try
        {
            $handler = $this->params['factory']->create($name, [ $this->runtime, [] ]);
        }
        catch (Error $ex)
        {
            throw new IllegalCallException("Tried to invoke [$name] which is undefined.");
        }
        catch (Exception $ex)
        {
            throw new IllegalCallException("Tried to invoke [$name] which is undefined.");
        }

        return $handler;
    }
}
