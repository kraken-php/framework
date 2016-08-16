<?php

namespace Kraken\_Unit\Throwable;

use Kraken\Throwable\Error;
use Kraken\Test\TUnit;

class ErrorTest extends TUnit
{
    /**
     *
     */
    public function testApiConstructor_CreatesInstance()
    {
        $ex = $this->createError('Error');

        $this->assertInstanceOf(Error::class, $ex);
        $this->assertInstanceOf(\Error::class, $ex);
    }

    /**
     *
     */
    public function testApiConstructor_ChainsErrors()
    {
        $previous = $this->createError('Previous');
        $ex = $this->createError('Error', $previous);

        $this->assertSame($previous, $ex->getPrevious());
    }

    /**
     *
     */
    public function testApiDestructor_DoesNotThrowError()
    {
        $ex = $this->createError('Error');
        unset($ex);
    }

    /**
     *
     */
    public function testApiToString_ReturnsErrorStack()
    {
        $prev = $this->createError('Previous');
        $ex   = $this->createError('Error', $prev);

        $this->assertString((string) $ex);
    }

    /**
     *
     */
    public function testStaticApiToString_ReturnsErrorStack()
    {
        $prev = $this->createError('Previous');
        $ex   = $this->createError('Error', $prev);

        $this->assertSame((string) $ex, Error::toString($ex));
    }

    /**
     *
     */
    public function testStaticApiToTrace_ReturnsTrace()
    {
        $prev = $this->createError('Previous');
        $ex   = $this->createError('Error', $prev);

        $this->assertTrace(Error::toTrace($ex));
    }

    /**
     *
     */
    public function testStaticApiToStackTrace_ReturnsStackTrace()
    {
        $prev = $this->createError('Previous');
        $ex   = $this->createError('Error', $prev);

        $this->assertStackTrace(Error::toStackTrace($ex));
    }

    /**
     *
     */
    public function testStaticApiToThrowableTrace_ReturnsStackThrowable()
    {
        $prev = $this->createError('Previous');
        $ex   = $this->createError('Error', $prev);

        $this->assertThrowableTrace(Error::toThrowableTrace($ex));
    }

    /**
     *
     */
    public function testStaticApiToStackString_ReturnsStackTraceAsString()
    {
        $prev = $this->createError('Previous');
        $ex   = $this->createError('Error', $prev);

        $this->assertStackString(Error::toStackString($ex));
    }

    /**
     *
     */
    public function testStaticApiToThrowableString_ReturnsThrowableTraceAsString()
    {
        $prev = $this->createError('Previous');
        $ex   = $this->createError('Error', $prev);

        $this->assertThrowableString(Error::toThrowableString($ex));
    }

    /**
     * @param string $message
     * @param null $previous
     * @return Error
     */
    public function createError($message, $previous = null)
    {
        return new Error($message, $previous);
    }

    /**
     * @param mixed[] $data
     */
    public function assertTrace($data)
    {
        $this->assertTrue(is_string($data['message']));
        $this->assertTrue($data['class'] === Error::class );
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
        $stackRegex = "\t" . '([0-9 ]*?)\. \[([a-zA-Z0-9\\\-_\. ]*?)\] "(.*?)" in ([a-zA-Z0-9\\\-_\.," ]*?)(.+)([0-9]*?)';
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
        $throwRegex = '\[([a-zA-Z0-9\\\-_\. ]*?)\] "(.*?)" in ([a-zA-Z0-9\\\-_\.," ]*?)(.+)([0-9]*?)';
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
        $throwRegex = "\t" . '([0-9 ]*?)\. \[([a-zA-Z0-9\\\-_\. ]*?)\] "(.*?)" in ([a-zA-Z0-9\\\-_\.," ]*?)(.+)([0-9]*?)';
        $regex = '(' . implode('|', [ $throwRegex ]) . ')';

        $this->assertRegExp('#^' . $regex . '$#msi', $string);
    }
}
