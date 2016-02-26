<?php

namespace Kraken\Container;

use Kraken\Throwable\Exception\Runtime\Io\IoReadException;
use Kraken\Throwable\Exception\Runtime\Io\IoWriteException;

interface ContainerInterface
{
    /**
     * @param string $alias
     * @param mixed[] $definition
     * @throws IoWriteException
     */
    public function bind($alias, $definition = []);

    /**
     * @param string $alias
     * @param object $object
     * @throws IoWriteException
     */
    public function instance($alias, $object);

    /**
     * @param string $new
     * @param string $existing
     * @throws IoWriteException
     */
    public function alias($new, $existing);

    /**
     * @param string $alias
     * @param mixed[] $definition
     * @throws IoWriteException
     */
    public function singleton($alias, $definition = []);

    /**
     * @param string $alias
     * @param scalar $param
     * @throws IoWriteException
     */
    public function param($alias, $param);

    /**
     * @param string $alias
     * @param callable $factoryMethod
     * @param array $parameters
     * @throws IoWriteException
     */
    public function factory($alias, callable $factoryMethod, $parameters = []);

    /**
     * @param string $alias
     * @param mixed[] $parameters
     * @return mixed
     * @throws IoReadException
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
     * @throws IoReadException
     */
    public function call(callable $callable, $parameters = []);
}
