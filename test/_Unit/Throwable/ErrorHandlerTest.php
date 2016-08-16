<?php

namespace Kraken\_Unit\Throwable;

use Kraken\Throwable\Error\FatalError;
use Kraken\Throwable\Error\NoticeError;
use Kraken\Throwable\Error\UndefinedError;
use Kraken\Throwable\Error\WarningError;
use Kraken\Throwable\ErrorHandler;
use Kraken\Test\TUnit;
use Error;

class ErrorHandlerTest extends TUnit
{
    /**
     * @dataProvider noticeProvider
     */
    public function testStaticApiHandleError_ThrowsNotice_OnNoticeLevelErrors($code, $expected)
    {
        try
        {
            ErrorHandler::handleError($code, $message = 'message', $file = 'file', $line = 20);
            $this->fail('Error or Exception was not thrown.');
        }
        catch (Error $ex)
        {
            $this->assertInstanceOf(NoticeError::class, $ex);
        }
    }

    /**
     * @dataProvider warningProvider
     */
    public function testStaticApiHandleError_ThrowsWarning_OnWarningLevelErrors($code, $expected)
    {
        try
        {
            ErrorHandler::handleError($code, $message = 'message', $file = 'file', $line = 20);
            $this->fail('Error or Exception was not thrown.');
        }
        catch (Error $ex)
        {
            $this->assertInstanceOf(WarningError::class, $ex);
        }
    }

    /**
     * @dataProvider errorProvider
     */
    public function testStaticApiHandleError_ThrowsFatal_OnFatalLevelErrors($code, $expected)
    {
        try
        {
            ErrorHandler::handleError($code, $message = 'message', $file = 'file', $line = 20);
            $this->fail('Error or Exception was not thrown.');
        }
        catch (Error $ex)
        {
            $this->assertInstanceOf(FatalError::class, $ex);
        }
    }

    /**
     * @dataProvider undefinedProvider
     */
    public function testStaticApiHandleError_ReturnsImmediately_OnUndefinedLevelErrors($code, $expected)
    {
        ErrorHandler::handleError($code, $message = 'message', $file = 'file', $line = 20);
    }

    /**
     * @return mixed[]
     */
    public function noticeProvider()
    {
        return [
            [ E_NOTICE,            ErrorHandler::E_NOTICE ],
            [ E_USER_NOTICE,       ErrorHandler::E_NOTICE ],
            [ E_DEPRECATED,        ErrorHandler::E_NOTICE ],
            [ E_USER_DEPRECATED,   ErrorHandler::E_NOTICE ]
        ];
    }

    /**
     * @return mixed[]
     */
    public function warningProvider()
    {
        return [
            [ E_WARNING,           ErrorHandler::E_WARNING ],
            [ E_CORE_WARNING,      ErrorHandler::E_WARNING ],
            [ E_COMPILE_WARNING,   ErrorHandler::E_WARNING ],
            [ E_USER_WARNING,      ErrorHandler::E_WARNING ],
            [ E_RECOVERABLE_ERROR, ErrorHandler::E_WARNING ]
        ];
    }

    /**
     * @return mixed[]
     */
    public function errorProvider()
    {
        return [
            [ E_ERROR,             ErrorHandler::E_ERROR ],
            [ E_PARSE,             ErrorHandler::E_ERROR ],
            [ E_CORE_ERROR,        ErrorHandler::E_ERROR ],
            [ E_COMPILE_ERROR,     ErrorHandler::E_ERROR ],
            [ E_USER_ERROR,        ErrorHandler::E_ERROR ],
            [ E_STRICT,            ErrorHandler::E_ERROR ]
        ];
    }

    /**
     * @return mixed[]
     */
    public function undefinedProvider()
    {
        return [
            [ 'E_UNKNOWN',         ErrorHandler::E_UNSUPPORTED ]
        ];
    }
}
