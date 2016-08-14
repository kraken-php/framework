<?php

namespace Kraken\_Module\Throwable;

use Kraken\Throwable\Exception\Logic\Resource\ResourceUndefinedException;
use Kraken\Throwable\Exception\Logic\Resource\ResourceDefinedException;
use Kraken\Throwable\Exception\Logic\IllegalCallException;
use Kraken\Throwable\Exception\Logic\IllegalFieldException;
use Kraken\Throwable\Exception\Logic\InstantiationException;
use Kraken\Throwable\Exception\Logic\InvalidArgumentException;
use Kraken\Throwable\Exception\Logic\InvalidFormatException;
use Kraken\Throwable\Exception\Logic\ResourceException;
use Kraken\Throwable\Exception\Runtime\Execution\CancellationException;
use Kraken\Throwable\Exception\Runtime\Execution\RejectionException;
use Kraken\Throwable\Exception\Runtime\Execution\TimeoutException;
use Kraken\Throwable\Exception\Runtime\Io\IoReadException;
use Kraken\Throwable\Exception\Runtime\Io\IoWriteException;
use Kraken\Throwable\Exception\Runtime\ExecutionException;
use Kraken\Throwable\Exception\Runtime\IoException;
use Kraken\Throwable\Exception\Runtime\OutOfBoundsException;
use Kraken\Throwable\Exception\Runtime\OverflowException;
use Kraken\Throwable\Exception\Runtime\UnderflowException;
use Kraken\Throwable\Exception\System\ChildUnresponsiveException;
use Kraken\Throwable\Exception\System\ParentUnresponsiveException;
use Kraken\Throwable\Exception\System\TaskIncompleteException;
use Kraken\Throwable\Exception\LogicException;
use Kraken\Throwable\Exception\RuntimeException;
use Kraken\Throwable\Exception\SystemException;
use Kraken\Throwable\Exception;
use Kraken\Test\TModule;

class ExceptionTest extends TModule
{
    /**
     *
     */
    public function testCaseExceptionStructure_IsValid()
    {
        $structure = [
            ResourceUndefinedException::class   => ResourceException::class,
            ResourceDefinedException::class     => ResourceException::class,
            IllegalCallException::class         => LogicException::class,
            IllegalFieldException::class        => LogicException::class,
            InstantiationException::class       => LogicException::class,
            InvalidArgumentException::class     => LogicException::class,
            InvalidFormatException::class       => LogicException::class,
            ResourceException::class            => LogicException::class,
            CancellationException::class        => ExecutionException::class,
            RejectionException::class           => ExecutionException::class,
            TimeoutException::class             => ExecutionException::class,
            IoReadException::class              => IoException::class,
            IoWriteException::class             => IoException::class,
            ExecutionException::class           => RuntimeException::class,
            IoException::class                  => RuntimeException::class,
            OutOfBoundsException::class         => RuntimeException::class,
            OverflowException::class            => RuntimeException::class,
            UnderflowException::class           => RuntimeException::class,
            ChildUnresponsiveException::class   => SystemException::class,
            ParentUnresponsiveException::class  => SystemException::class,
            TaskIncompleteException::class      => SystemException::class,
            LogicException::class               => Exception::class,
            RuntimeException::class             => Exception::class,
            SystemException::class              => Exception::class
        ];

        foreach ($structure as $class=>$extended)
        {
            $this->assertInstanceOf($extended, new $class);
        }
    }


}
