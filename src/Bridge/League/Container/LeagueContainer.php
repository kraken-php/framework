<?php

namespace Kraken\Bridge\League\Container;

use League\Container\ContainerInterface;

class LeagueContainer
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @param LeagueContainerAwareInterface $container
     */
    public function __construct(LeagueContainerAwareInterface $container)
    {
        $this->$container = $container->getLeagueContainer();
    }

    /**
     * Add a definition to the container
     *
     * @param string  $alias
     * @param mixed   $concrete
     * @param boolean $singleton
     * @return \League\Container\Definition\DefinitionInterface|\League\Container\ContainerInterface
     */
    public function add($alias, $concrete = null, $singleton = false)
    {
        return $this->container->add($alias, $concrete, $singleton);
    }

    /**
     * Adds a service provider to the container
     *
     * @param  string|\League\Container\ServiceProvider $provider
     * @return \League\Container\ContainerInterface
     */
    public function addServiceProvider($provider)
    {
        return $this->container->addServiceProvider($provider);
    }

    /**
     * Add a singleton definition to the container
     *
     * @param  string $alias
     * @param  mixed  $concrete
     * @return \League\Container\Definition\DefinitionInterface|\League\Container\ContainerInterface
     */
    public function singleton($alias, $concrete = null)
    {
        return $this->singleton($alias, $concrete);
    }

    /**
     * Allows for methods to be invoked on any object that is resolved of the tyoe
     * provided
     *
     * @param  string   $type
     * @param  callable $callback
     * @return \League\Container\Inflector|void
     */
    public function inflector($type, callable $callback = null)
    {
        return $this->inflector($type, $callback);
    }

    /**
     * Add a callable definition to the container
     *
     * @param  string   $alias
     * @param  callable $concrete
     * @return \League\Container\Definition\DefinitionInterface
     */
    public function invokable($alias, callable $concrete = null)
    {
        return $this->invokable($alias, $concrete);
    }

    /**
     * Modify the definition of an already defined service
     *
     * @param   string $alias
     * @throws  \InvalidArgumentException if the definition does not exist
     * @throws  \League\Container\Exception\ServiceNotExtendableException if service cannot be extended
     * @return  \League\Container\Definition\DefinitionInterface
     */
    public function extend($alias)
    {
        return $this->extend($alias);
    }

    /**
     * Get an item from the container
     *
     * @param  string $alias
     * @param  array  $args
     * @return mixed
     */
    public function get($alias, array $args = [])
    {
        return $this->get($alias, $args);
    }

    /**
     * Invoke
     *
     * @param  string $alias
     * @param  array  $args
     * @return mixed
     */
    public function call($alias, array $args = [])
    {
        return $this->call($alias, $args);
    }

    /**
     * Check if an item is registered with the container
     *
     * @param  string  $alias
     * @return boolean
     */
    public function isRegistered($alias)
    {
        return $this->isRegistered($alias);
    }

    /**
     * Check if an item is being managed as a singleton
     *
     * @param  string  $alias
     * @return boolean
     */
    public function isSingleton($alias)
    {
        return $this->isSingleton($alias);
    }

    /**
     * Determines if a definition is registered via a service provider.
     *
     * @param  string $alias
     * @return boolean
     */
    public function isInServiceProvider($alias)
    {
        return $this->isInServiceProvider($alias);
    }
}
