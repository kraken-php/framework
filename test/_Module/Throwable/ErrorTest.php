<?php

namespace Kraken\_Module\Throwable;

use Kraken\Throwable\Error\FatalError;
use Kraken\Throwable\Error\NoticeError;
use Kraken\Throwable\Error\UndefinedError;
use Kraken\Throwable\Error\WarningError;
use Kraken\Throwable\Error;
use Kraken\Test\TModule;

class ErrorTest extends TModule
{
    /**
     *
     */
    public function testCaseErrorStructure_IsValid()
    {
        $structure = [
            Error::class            => \Error::class,
            FatalError::class       => Error::class,
            WarningError::class     => Error::class,
            NoticeError::class      => Error::class
        ];

        foreach ($structure as $class=>$extended)
        {
            $this->assertInstanceOf($extended, new $class);
        }
    }


}
