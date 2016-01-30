<?php

namespace Kraken\Container;

use Kraken\Exception\Io\ReadException;
use Kraken\Exception\Io\WriteException;

interface ContainerInterface
{
    /**
     * @param string $alias
     * @param mixed[] $definition
     * @throws WriteException
     */
    public function bind($alias, $definition = []);

    /**
     * @param string $alias
     * @param object $object
     * @throws WriteException
     */
    public function instance($alias, $object);

    /**
     * @param string $new
     * @param string $existing
     * @throws WriteException
     */
    public function alias($new, $existing);

    /**
     * @param string $alias
     * @param mixed[] $definition
     * @throws WriteException
     */
    public function singleton($alias, $definition = []);

    /**
     * @param string $alias
     * @param scalar $param
     * @throws WriteException
     */
    public function param($alias, $param);

    /**
     * @param string $alias
     * @param callable $factoryMethod
     * @param array $parameters
     * @throws WriteException
     */
    public function factory($alias, callable $factoryMethod, $parameters = []);

    /**
     * @param string $alias
     * @param mixed[] $parameters
     * @return mixed
     * @throws ReadException
     */
    public function make($alias, $parameters = []);

    /**
     * @param string $alias
     * @return bool
     */
    public function has($alias);

    /**
     * @param string $alias
     */
    public function remove($alias);

    /**
     * @param callable $callable
     * @param mixed[] $parameters
     * @return mixed
     * @throws ReadException
     */
    public function call(callable $callable, $parameters = []);
}
