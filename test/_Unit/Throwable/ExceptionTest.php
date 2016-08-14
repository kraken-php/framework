<?php

namespace Kraken\_Unit\Throwable;

use Kraken\Throwable\Exception;
use Kraken\Test\TUnit;

class ExceptionTest extends TUnit
{
    /**
     *
     */
    public function testApiConstructor_CreatesInstance()
    {
        $ex = $this->createException('Exception');

        $this->assertInstanceOf(Exception::class, $ex);
        $this->assertInstanceOf(\Exception::class, $ex);
    }

    /**
     *
     */
    public function testApiConstructor_ChainsExceptions()
    {
        $previous = $this->createException('Previous');
        $ex = $this->createException('Exception', $previous);

        $this->assertSame($previous, $ex->getPrevious());
    }

    /**
     *
     */
    public function testApiDestructor_DoesNotThrowException()
    {
        $ex = $this->createException('Exception');
        unset($ex);
    }

    /**
     *
     */
    public function testApiToString_ReturnsExceptionStack()
    {
        $prev = $this->createException('Previous');
        $ex   = $this->createException('Exception', $prev);

        $this->assertString((string) $ex);
    }

    /**
     *
     */
    public function testStaticApiToString_ReturnsExceptionStack()
    {
        $prev = $this->createException('Previous');
        $ex   = $this->createException('Exception', $prev);

        $this->assertSame((string) $ex, Exception::toString($ex));
    }

    /**
     *
     */
    public function testStaticApiToTrace_ReturnsTrace()
    {
        $prev = $this->createException('Previous');
        $ex   = $this->createException('Exception', $prev);

        $this->assertTrace(Exception::toTrace($ex));
    }

    /**
     *
     */
    public function testStaticApiToStackTrace_ReturnsStackTrace()
    {
        $prev = $this->createException('Previous');
        $ex   = $this->createException('Exception', $prev);

        $this->assertStackTrace(Exception::toStackTrace($ex));
    }

    /**
     *
     */
    public function testStaticApiToThrowableTrace_ReturnsStackThrowable()
    {
        $prev = $this->createException('Previous');
        $ex   = $this->createException('Exception', $prev);

        $this->assertThrowableTrace(Exception::toThrowableTrace($ex));
    }

    /**
     *
     */
    public function testStaticApiToStackString_ReturnsStackTraceAsString()
    {
        $prev = $this->createException('Previous');
        $ex   = $this->createException('Exception', $prev);

        $this->assertStackString(Exception::toStackString($ex));
    }

    /**
     *
     */
    public function testStaticApiToThrowableString_ReturnsThrowableTraceAsString()
    {
        $prev = $this->createException('Previous');
        $ex   = $this->createException('Exception', $prev);

        $this->assertThrowableString(Exception::toThrowableString($ex));
    }

    /**
     * @param string $message
     * @param null $previous
     * @return Exception
     */
    public function createException($message, $previous = null)
    {
        return new Exception($message, $previous);
    }

    /**
     * @param mixed[] $data
     */
    public function assertTrace($data)
    {
        $this->assertTrue(is_string($data['message']));
        $this->assertTrue($data['class'] === Exception::class );
        $this->assertRegExp('#^([a-zA-Z0-9\\\/\-_\.," ]*?)$#si', $data['file']);
        $this->assertRegExp('#^([0-9]*?)$#si', (string) $data['line']);
        $this->assertRegExp('#^([0-9]*?)$#si', (string) $data['code']);
        $this->assertStackTrace($data['trace']);
        $this->assertTrue(is_array($data['prev']) || $data['prev'] === null);
    }

    /**
     * @param string[] $elements
     */
    public function assertStackTrace($elements)
    {
        $throwRegex = '\[throwable\] ([a-zA-Z0-9\\\-_\. ]*?)\(\.\.\.\) in ([a-zA-Z0-9\\\-_\.," ]*?)(.+)([0-9]*?)';
        $callRegex  = '\[call\] ([a-zA-Z0-9\\\-_\. ]*?)(->|::)([a-zA-Z0-9\\\-_\. ]*?)\(([a-zA-Z0-9\\\-_\.," ]*?)\) in ([a-zA-Z0-9\\\-_\.," ]*?)(.+)([0-9]*?)';
        $mainRegex  = '\[main\]';
        $regex = '#^(' . implode('|', [ $throwRegex, $callRegex, $mainRegex ]) . ')$#si';

        foreach ($elements as $element)
        {
            $this->assertRegExp($regex, $element);
        }
    }

    /**
     * @param string string
     */
    public function assertString($string)
    {
        $throwRegex = "\t" . '([0-9 ]*?)\. \[throwable\] ([a-zA-Z0-9\\\-_\. ]*?)\(\.\.\.\) in ([a-zA-Z0-9\\\-_\.," ]*?)(.+)([0-9]*?)';
        $callRegex  = "\t" . '([0-9 ]*?)\. \[call\] ([a-zA-Z0-9\\\-_\. ]*?)(->|::)([a-zA-Z0-9\\\-_\. ]*?)\(([a-zA-Z0-9\\\-_\.," ]*?)\) in ([a-zA-Z0-9\\\-_\.," ]*?)(.+)([0-9]*?)';
        $mainRegex  = "\t" . '([0-9 ]*?)\. \[main\]';
        $stackRegex = "\t" . '([0-9 ]*?)\. \[([a-zA-Z0-9\\\-_\. ]*?)\] "(.*?)"';
        $throwTitleRegex = "\t" . 'Throwable trace:';
        $stackTitleRegex = "\t" . 'Stack trace:';
        $regex = '(' . implode('|', [ $throwRegex, $callRegex, $mainRegex,  $stackRegex, $throwTitleRegex, $stackTitleRegex ]) . ')';

        $this->assertRegExp('#^' . $regex . '$#msi', $string);
    }

    /**
     * @param string $string
     */
    public function assertStackString($string)
    {
        $throwRegex = "\t" . '([0-9 ]*?)\. \[throwable\] ([a-zA-Z0-9\\\-_\. ]*?)\(\.\.\.\) in ([a-zA-Z0-9\\\-_\.," ]*?)(.+)([0-9]*?)';
        $callRegex  = "\t" . '([0-9 ]*?)\. \[call\] ([a-zA-Z0-9\\\-_\. ]*?)(->|::)([a-zA-Z0-9\\\-_\. ]*?)\(([a-zA-Z0-9\\\-_\.," ]*?)\) in ([a-zA-Z0-9\\\-_\.," ]*?)(.+)([0-9]*?)';
        $mainRegex  = "\t" . '([0-9 ]*?)\. \[main\]';
        $regex = '(' . implode('|', [ $throwRegex, $callRegex, $mainRegex ]) . ')';

        $this->assertRegExp('#^' . $regex . '$#msi', $string);
    }

    /**
     * @param string[] $elements
     */
    public function assertThrowableTrace($elements)
    {
        $throwRegex = '\[([a-zA-Z0-9\\\-_\. ]*?)\] "(.*?)"';
        $regex = '#^(' . implode('|', [ $throwRegex ]) . ')$#si';

        foreach ($elements as $element)
        {
            $this->assertRegExp($regex, $element);
        }
    }

    /**
     * @param string $string
     */
    public function assertThrowableString($string)
    {
        $throwRegex = "\t" . '([0-9 ]*?)\. \[([a-zA-Z0-9\\\-_\. ]*?)\] "(.*?)"';
        $regex = '(' . implode('|', [ $throwRegex ]) . ')';

        $this->assertRegExp('#^' . $regex . '$#msi', $string);
    }
}
