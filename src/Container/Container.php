<?php

namespace Kraken\Container;

use Kraken\Container\Method\FactoryMethod;
use Kraken\Throwable\Exception\Runtime\Io\IoReadException;
use Kraken\Throwable\Exception\Runtime\Io\IoWriteException;
use League\Container\Container as LeagueContainer;
use League\Container\ContainerInterface as LeagueContainerInterface;
use Error;
use Exception;

class Container implements ContainerInterface
{
    /**
     * @var LeagueContainerInterface
     */
    protected $container;

    /**
     *
     */
    public function __construct()
    {
        $this->container = $this->createContainer();
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->container);
    }

    /**
     * @param string $alias
     * @param mixed[] $definition
     * @throws IoWriteException
     */
    public function bind($alias, $definition = [])
    {
        $object = null;

        try
        {
            $object = $this->container->add($alias);
        }
        catch (Error $ex)
        {
            throw new IoWriteException("Binding definition of [$alias] to Container failed.", $ex);
        }
        catch (Exception $ex)
        {
            throw new IoWriteException("Binding definition of [$alias] to Container failed.", $ex);
        }

        if ($object === null)
        {
            throw new IoWriteException("Binding definition of [$alias] to Container failed.");
        }

        $object->withArguments($definition);
    }

    /**
     * @param string $alias
     * @param object $object
     * @throws IoWriteException
     */
    public function instance($alias, $object)
    {
        try
        {
            $this->container->add($alias, $object, true);
        }
        catch (Error $ex)
        {
            throw new IoWriteException("Binding instance of [$alias] to Container failed.", $ex);
        }
        catch (Exception $ex)
        {
            throw new IoWriteException("Binding instance of [$alias] to Container failed.", $ex);
        }
    }

    /**
     * @param string $new
     * @param string $existing
     * @throws IoWriteException
     */
    public function alias($new, $existing)
    {
        try
        {
//            $this->container->add($new, new ServiceFactoryProxy(function() {
//                return null;
//            }, [ $existing ]), true);

            // TODO Kraken-28
            $this->container->add($new, $this->container->get($existing));
        }
        catch (Error $ex)
        {
            throw new IoWriteException("Binding alias of [$new] as [$existing] to Container failed.", $ex);
        }
        catch (Exception $ex)
        {
            throw new IoWriteException("Binding alias of [$new] as [$existing] to Container failed.", $ex);
        }
    }

    /**
     * @param string $alias
     * @param mixed[] $definition
     * @throws IoWriteException
     */
    public function singleton($alias, $definition = [])
    {
        $object = null;

        try
        {
            $object = $this->container->singleton($alias);
        }
        catch (Error $ex)
        {
            throw new IoWriteException("Binding singleton of [$alias] to Container failed.", $ex);
        }
        catch (Exception $ex)
        {
            throw new IoWriteException("Binding singleton of [$alias] to Container failed.", $ex);
        }

        if ($object === null)
        {
            throw new IoWriteException("Binding singleton of [$alias] to Container failed.");
        }

        $object->withArguments($definition);
    }

    /**
     * @param string $alias
     * @param scalar $param
     * @throws IoWriteException
     */
    public function param($alias, $param)
    {
        try
        {
            $this->container->add($alias, $param);
        }
        catch (Error $ex)
        {
            throw new IoWriteException("Binding param of [$alias] to Container failed.");
        }
        catch (Exception $ex)
        {
            throw new IoWriteException("Binding param of [$alias] to Container failed.");
        }
    }

    /**
     * @param string $alias
     * @param callable $factoryMethod
     * @param mixed[] $parameters
     * @throws IoWriteException
     */
    public function factory($alias, callable $factoryMethod, $parameters = [])
    {
        try
        {
            $this->container->add($alias, new FactoryMethod($factoryMethod, $parameters), true);
        }
        catch (Error $ex)
        {
            throw new IoWriteException("Binding factory of [$alias] to Container failed.", $ex);
        }
        catch (Exception $ex)
        {
            throw new IoWriteException("Binding factory of [$alias] to Container failed.", $ex);
        }
    }

    /**
     * @param string $alias
     * @param mixed[] $parameters
     * @return mixed
     * @throws IoReadException
     */
    public function make($alias, $parameters = [])
    {
        try
        {
            $object = $this->container->get($alias, $parameters);

            if ($object instanceof FactoryMethod === true)
            {
                $parameters = (count($parameters) === 0) ? $object->getArgs() : $parameters;

                return $this->container->call($object->getCallback(), $parameters);
            }

            return $object;
        }
        catch (Error $ex)
        {
            throw new IoReadException("Resolving object of [$alias] from Container failed.", $ex);
        }
        catch (Exception $ex)
        {
            throw new IoReadException("Resolving object of [$alias] from Container failed.", $ex);
        }
    }

    /**
     * @param string $alias
     * @return bool
     */
    public function has($alias)
    {
        return $this->container->isRegistered($alias);
    }

    /**
     * @param string $alias
     */
    public function remove($alias)
    {
        unset($this->container[$alias]);
    }

    /**
     * @param callable $callable
     * @param mixed[] $parameters
     * @return mixed
     * @throws IoReadException
     */
    public function call(callable $callable, $parameters = [])
    {
        try
        {
            return $this->container->call($callable, $parameters);
        }
        catch (Error $ex)
        {
            throw new IoReadException("Calling function with Container failed.", $ex);
        }
        catch (Exception $ex)
        {
            throw new IoReadException("Calling function with Container failed.", $ex);
        }
    }

    /**
     * @return LeagueContainerInterface
     */
    protected function createContainer()
    {
        return new LeagueContainer();
    }
}
