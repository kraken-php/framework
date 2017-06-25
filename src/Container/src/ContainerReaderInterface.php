<?php

namespace Kraken\Container;

use Dazzle\Throwable\Exception\Runtime\ReadException;

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
     * @throws ReadException
     */
    public function make($aliasOrClass, $args = []);

    /**
     * Call given method using Container autowiring.
     *
     * @param callable $callable
     * @param mixed[] $args
     * @return mixed
     * @throws ReadException
     */
    public function call(callable $callable, $args = []);
}
