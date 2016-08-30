<?php

namespace Kraken\Container;

use Kraken\Container\Model\ContainerModel;
use Kraken\Container\Object\InvokableObject;
use Kraken\Throwable\Exception\Runtime\ReadException;
use Kraken\Throwable\Exception\Runtime\WriteException;
use League\Container\ReflectionContainer as ContainerReflection;
use Closure;
use Error;
use Exception;
use ReflectionFunction;
use ReflectionMethod;

class Container implements ContainerInterface
{
    /**
     * @var ContainerModel
     */
    protected $container;

    /**
     * @var ContainerReflection
     */
    protected $reflector;

    /**
     *
     */
    public function __construct()
    {
        $this->createContainer();
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->container);
        unset($this->reflector);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function exists($aliasOrClass)
    {
        return $this->container->has($aliasOrClass);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function wire($aliasOrClass, $defaultParams)
    {
        $object = null;
        $ex = null;

        try
        {
            $object = $this->container->add($aliasOrClass);
        }
        catch (Error $ex)
        {}
        catch (Exception $ex)
        {}

        if ($object === null || $ex !== null)
        {
            throw new WriteException("Binding definition of [$aliasOrClass] to Container failed.", $ex);
        }

        $object->withArguments($defaultParams);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function share($aliasOrClass, $defaultParams = [])
    {
        $object = null;
        $ex = null;

        if (!is_array($defaultParams))
        {
            throw new WriteException("Binding singleton of [$aliasOrClass] to Container failed.", $ex);
        }

        try
        {
            $object = $this->container->add($aliasOrClass, null, true);
        }
        catch (Error $ex)
        {}
        catch (Exception $ex)
        {}

        if ($object === null || $ex !== null)
        {
            throw new WriteException("Binding singleton of [$aliasOrClass] to Container failed.", $ex);
        }

        $object->withArguments($defaultParams);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function bind($aliasOrClass, $mixed)
    {
        $isObject = is_object($mixed);
        $isCallable = is_callable($mixed);
        $isValid  = class_exists($aliasOrClass) ? ($isObject && $mixed instanceof $aliasOrClass) || ($isCallable) : true;
        $ex = null;

        if (!$isValid)
        {
            throw new WriteException("Binding instance of [$aliasOrClass] to Container failed.");
        }

        try
        {
            if ($isObject && $isCallable && ! $mixed instanceof Closure)
            {
                $mixed = new InvokableObject($mixed);
            }

            $this->container->add($aliasOrClass, $mixed, $isObject);
            return;
        }
        catch (Error $ex)
        {}
        catch (Exception $ex)
        {}

        throw new WriteException("Binding instance of [$aliasOrClass] to Container failed.", $ex);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function alias($aliasOrClass, $existingAliasOrClass)
    {
        if (!is_string($aliasOrClass) || !is_string($existingAliasOrClass))
        {
            throw new WriteException("Binding alias of [$aliasOrClass] as [$existingAliasOrClass] to Container failed.");
        }

        try
        {
            // TODO Kraken-28
            $this->container->add($aliasOrClass, $this->container->get($existingAliasOrClass));
            return;
        }
        catch (Error $ex)
        {}
        catch (Exception $ex)
        {}

        throw new WriteException("Binding alias of [$aliasOrClass] as [$existingAliasOrClass] to Container failed.", $ex);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function instance($aliasOrClass, $object)
    {
        if (is_object($object) && ! $object instanceof Closure)
        {
            $this->bind($aliasOrClass, $object);
            return;
        }

        $name = is_callable($object) ? 'callable' : (string) $object;

        throw new WriteException("Binding object of [$name] as [$aliasOrClass] to Container failed.");

//        if (!is_object($object) || is_callable($object))
//        {
//            throw new WriteException(sprintf(
//                "Binding object of [%s] as [%s] to Container failed.",
//                is_callable($object) ? 'callable' : $object,
//                $aliasOrClass
//            ));
//        }
//
//        $this->bind($aliasOrClass, $object);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function param($aliasOrClass, $param)
    {
        if (is_object($param) || is_callable($param) || class_exists($aliasOrClass))
        {
            throw new WriteException("Binding param of [$aliasOrClass] to Container failed.");
        }

        try
        {
            $this->container->add($aliasOrClass, $param);
            return;
        }
        catch (Error $ex)
        {}
        catch (Exception $ex)
        {}

        throw new WriteException("Binding param of [$aliasOrClass] to Container failed.", $ex);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function factory($aliasOrClass, callable $factoryMethod, $args = [])
    {
        $object = null;
        $ex = null;

        try
        {
            $object = $this->container->add($aliasOrClass, $factoryMethod);
        }
        catch (Error $ex)
        {}
        catch (Exception $ex)
        {}

        if ($object === null || $ex !== null)
        {
            throw new WriteException("Binding factory of [$aliasOrClass] to Container failed.", $ex);
        }

        $object->withArguments($args);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function make($aliasOrClass, $args = [])
    {
        try
        {
            $made = $this->container->get($aliasOrClass, $args);

            if ($made instanceof InvokableObject)
            {
                $made = $made->getObject();
            }

            return $made;
        }
        catch (Error $ex)
        {}
        catch (Exception $ex)
        {}

        throw new ReadException("Resolving object of [$aliasOrClass] from Container failed.", $ex);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function remove($aliasOrClass)
    {
        $this->container->remove($aliasOrClass);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function call(callable $callable, $args = [])
    {
        try
        {
            if (is_string($callable) && strpos($callable, '::') !== false)
            {
                $callable = explode('::', $callable);
            }

            if (is_array($callable))
            {
                $reflection = new ReflectionMethod($callable[0], $callable[1]);
                $isArray = true;

                if ($reflection->isStatic())
                {
                    $callable[0] = null;
                }
            }
            else
            {
                $reflection = new ReflectionFunction($callable);
                $isArray = false;
            }

            $params = $reflection->getParameters();
            $tmp    = $args;
            $args   = [];

            foreach ($tmp as $key=>$arg)
            {
                $args[$params[$key]->name] = $arg;
            }

            if ($isArray)
            {
                return $reflection->invokeArgs($callable[0], $this->reflector->reflectArguments($reflection, $args));
            }
            else
            {
                return $reflection->invokeArgs($this->reflector->reflectArguments($reflection, $args));
            }
        }
        catch (Error $ex)
        {}
        catch (Exception $ex)
        {}

        throw new ReadException("Calling function with Container failed.", $ex);
    }

    /**
     * Prepare Container internals.
     */
    protected function createContainer()
    {
        $this->container = new ContainerModel();
        $this->reflector = new ContainerReflection();

        $this->container->delegate($this->reflector);
    }
}
