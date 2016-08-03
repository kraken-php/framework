<?php

namespace Kraken\Container;

use Kraken\Throwable\Exception\Runtime\Io\IoReadException;

interface ContainerReaderInterface
{
    /**
     * Check if $aliasOrClass is resolvable by Container.
     *
     * @param string $aliasOrClass
     * @return bool
     */
    public function exists($aliasOrClass);

    /**
     * Create object of class $aliasOrClass.
     *
     * @param string $aliasOrClass
     * @param mixed[] $args
     * @return mixed
     * @throws IoReadException
     */
    public function make($aliasOrClass, $args = []);

    /**
     * Call given method using Container autowiring.
     *
     * @param callable $callable
     * @param mixed[] $args
     * @return mixed
     * @throws IoReadException
     */
    public function call(callable $callable, $args = []);
}
