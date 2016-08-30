<?php

namespace Kraken\_Module\Throwable;

use Kraken\Throwable\Exception\Logic\ResourceUndefinedException;
use Kraken\Throwable\Exception\Logic\ResourceOccupiedException;
use Kraken\Throwable\Exception\Logic\IllegalCallException;
use Kraken\Throwable\Exception\Logic\IllegalFieldException;
use Kraken\Throwable\Exception\Logic\InstantiationException;
use Kraken\Throwable\Exception\Logic\InvalidArgumentException;
use Kraken\Throwable\Exception\Logic\InvalidFormatException;
use Kraken\Throwable\Exception\Logic\ResourceException;
use Kraken\Throwable\Exception\Runtime\CancellationException;
use Kraken\Throwable\Exception\Runtime\RejectionException;
use Kraken\Throwable\Exception\Runtime\TimeoutException;
use Kraken\Throwable\Exception\Runtime\ReadException;
use Kraken\Throwable\Exception\Runtime\WriteException;
use Kraken\Throwable\Exception\Runtime\ExecutionException;
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
            ResourceUndefinedException::class   => LogicException::class,
            ResourceOccupiedException::class    => LogicException::class,
            IllegalCallException::class         => LogicException::class,
            IllegalFieldException::class        => LogicException::class,
            InstantiationException::class       => LogicException::class,
            InvalidArgumentException::class     => LogicException::class,
            InvalidFormatException::class       => LogicException::class,
            ResourceException::class            => LogicException::class,
            CancellationException::class        => RuntimeException::class,
            RejectionException::class           => RuntimeException::class,
            TimeoutException::class             => RuntimeException::class,
            ReadException::class                => RuntimeException::class,
            WriteException::class               => RuntimeException::class,
            ExecutionException::class           => RuntimeException::class,
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
