<?php

namespace Kraken\Container;

use Kraken\Throwable\Exception\Runtime\WriteException;

interface ContainerWriterInterface
{
    /**
     * Wire default parameters to object definition of class $alias.
     *
     * @param string $aliasOrClass
     * @param mixed[] $defaultParams
     * @throws WriteException
     */
    public function wire($aliasOrClass, $defaultParams);

    /**
     * Mark object definition of class $alias as singleton and/or pass default parameters.
     *
     * @param string $aliasOrClass
     * @param mixed[] $defaultParams
     * @throws WriteException
     */
    public function share($aliasOrClass, $defaultParams = []);

    /**
     * Bind param, object, class or factoryMethod as definition for $aliasOrClass.
     *
     * @param string $aliasOrClass
     * @param mixed $mixed
     * @throws WriteException
     */
    public function bind($aliasOrClass, $mixed);

    /**
     * Create alias $aliasOrClass pointing to $existingAliasOrClass.
     *
     * @param string $aliasOrClass
     * @param string $existingAliasOrClass
     * @throws WriteException
     */
    public function alias($aliasOrClass, $existingAliasOrClass);

    /**
     * Bind object as definition for $aliasOrClass.
     *
     * @param string $aliasOrClass
     * @param object $object
     * @throws WriteException
     */
    public function instance($aliasOrClass, $object);

    /**
     * Bind param as definition for $aliasOrClass.
     *
     * @param string $aliasOrClass
     * @param string|float|int|null $param
     * @throws WriteException
     */
    public function param($aliasOrClass, $param);

    /**
     * Bind factory method as definition for $aliasOrClass
     *
     * @param string $aliasOrClass
     * @param callable $factoryMethod
     * @param mixed[] $args
     * @throws WriteException
     */
    public function factory($aliasOrClass, callable $factoryMethod, $args = []);

    /**
     * Remove custom definition for $aliasOrClass.
     *
     * @param string $aliasOrClass
     */
    public function remove($aliasOrClass);
}
