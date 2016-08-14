<?php

namespace Kraken\_Unit\Throwable;

use Kraken\Throwable\Exception;
use Kraken\Throwable\ExceptionHandler;
use Kraken\Test\TUnit;

class ExceptionHandlerTest extends TUnit
{
    /**
     *
     */
    public function testStaticApiHandleException_HandlesExceptionAsString()
    {
        $ex = new Exception('Exception');
        $class = ExceptionHandler::class;
        $handler = function($passedEx) use($ex) {
            $this->assertSame($ex, $passedEx);
        };

        $default = $this->getProtectedProperty($class, 'handler');
        $this->setProtectedProperty($class, 'handler', $handler);

        ExceptionHandler::handleException($ex);
        $this->setProtectedProperty($class, 'handler', $default);
    }
}
