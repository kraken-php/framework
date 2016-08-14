<?php

namespace Kraken\_Module\Throwable;

use Kraken\Test\TModule;
use Error;
use Exception;

class ThrowableTest extends TModule
{
    /**
     *
     */
    public function testCaseError_IsHandledByErrorCatchBlock()
    {
        $throwable = new Error('Error');

        try
        {
            throw $throwable;
        }
        catch (Error $ex)
        {
            $this->assertSame($throwable, $ex);
        }
        catch (Exception $ex)
        {
            $this->fail('Errors should no be catched by Exception-block.');
        }
    }

    /**
     *
     */
    public function testCaseException_IsHandledByExceptionCatchBlock()
    {
        $throwable = new Exception('Exception');

        try
        {
            throw $throwable;
        }
        catch (Error $ex)
        {
            $this->fail('Exceptions should no be catched by Error-block.');
        }
        catch (Exception $ex)
        {
            $this->assertSame($throwable, $ex);
        }
    }
}
