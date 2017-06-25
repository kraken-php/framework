<?php

namespace Kraken\Log;

use Kraken\Log\Formatter\Formatter;
use Kraken\Log\Formatter\FormatterInterface;
use Kraken\Log\Handler\Handler;
use Kraken\Log\Handler\HandlerInterface;
use Dazzle\Throwable\Exception\Logic\InvalidArgumentException;
use ReflectionClass;

class LoggerFactory
{
    /**
     * Create one of Monolog handlers by specifying it name or full class.
     *
     * @param string $classOrName
     * @param mixed[] $args
     * @return HandlerInterface
     * @throws InvalidArgumentException
     */
    public function createHandler($classOrName, $args = [])
    {
        $classes = [
            $classOrName,
            '\\Monolog\\Handler\\' . $classOrName
        ];

        foreach ($classes as $class)
        {
            if (class_exists($class))
            {
                $object = (new ReflectionClass($class))->newInstanceArgs($args);

                return new Handler($object);
            }
        }

        throw new InvalidArgumentException("Monolog handler [$classOrName] does not exist.");
    }

    /**
     * Create one of Monolog formatters by specyfing it name of full class.
     *
     * @param string $classOrName
     * @param mixed[] $args
     * @return FormatterInterface
     * @throws InvalidArgumentException
     */
    public function createFormatter($classOrName, $args = [])
    {
        $classes = [
            $classOrName,
            '\\Monolog\\Formatter\\' . $classOrName
        ];

        foreach ($classes as $class)
        {
            if (class_exists($class))
            {
                $object = (new ReflectionClass($class))->newInstanceArgs($args);

                return new Formatter($object);
            }
        }

        throw new InvalidArgumentException("Monolog formatter [$classOrName] does not exist.");
    }

    /**
     * Create one of Monolog processors by specyfing it name of full class.
     *
     * @param string $classOrName
     * @param mixed[] $args
     * @return callable
     * @throws InvalidArgumentException
     */
    public function createProcessor($classOrName, $args = [])
    {
        $classes = [
            $classOrName,
            '\\Monolog\\Processor\\' . $classOrName
        ];

        foreach ($classes as $class)
        {
            if (class_exists($class))
            {
                return (new ReflectionClass($class))->newInstanceArgs($args);
            }
        }

        throw new InvalidArgumentException("Monolog processor [$classOrName] does not exist.");
    }
}
