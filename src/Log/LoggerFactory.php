<?php

namespace Kraken\Log;

use Monolog\Formatter\FormatterInterface;
use ReflectionClass;
use Kraken\Log\Handler\HandlerInterface;

class LoggerFactory
{
    /**
     * @param string $name
     * @param mixed[] $args
     * @return HandlerInterface
     */
    public function createHandler($name, $args = [])
    {
        $class = '\\Monolog\\Handler\\' . $name;

        return (new ReflectionClass($class))->newInstanceArgs($args);
    }

    /**
     * @param string $name
     * @param mixed[] $args
     * @return FormatterInterface
     */
    public function createFormatter($name, $args = [])
    {
        $class = '\\Monolog\\Formatter\\' . $name;

        return (new ReflectionClass($class))->newInstanceArgs($args);
    }

    /**
     * @param string $name
     * @param mixed[] $args
     * @return callable
     */
    public function createProcessor($name, $args = [])
    {
        $class = '\\Monolog\\Processor\\' . $name;

        return (new ReflectionClass($class))->newInstanceArgs($args);
    }
}
