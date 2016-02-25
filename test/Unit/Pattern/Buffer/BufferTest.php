<?php

namespace Kraken\Unit\Test\Pattern\Buffer;

use Kraken\Test\Unit\TestCase;
use Kraken\Util\Buffer\Buffer;
use Kraken\Util\Buffer\BufferInterface;
use Kraken\Util\Buffer\BufferIterator;

class BufferTest extends TestCase
{
    /**
     * @var string
     */
    protected $initialString = 'abcdefghijklmnopqrstuvwxyz';

    /**
     * @var string
     */
    protected $appendString = '1234567890';

    /**
     * @var BufferInterface
     */
    protected $buffer;

    public function setUp()
    {
        $this->buffer = new Buffer($this->initialString);
    }

    public function testConstructor()
    {
        $this->assertEquals($this->initialString, $this->buffer->peek());
    }

    public function testApi__toString_ReturnsWholeData()
    {
        $this->assertEquals($this->initialString, (string) $this->buffer);
    }
    
    public function testApiLength_ReturnsLengthOfData()
    {
        $this->assertSame(strlen($this->initialString), $this->buffer->length());
    }
    
    public function testApiCount_ReturnsLengthOfData()
    {
        $this->assertSame(strlen($this->initialString), count($this->buffer)); 
    }
    
    public function testApiIsEmpty_ReturnsTrueForEmptyBuffer()
    {
        $this->assertFalse($this->buffer->isEmpty());
    }

    public function testApiIsEmpty_ReturnsFalseForNotEmptyBuffer()
    {
        $buffer = new Buffer();
        $this->assertTrue($buffer->isEmpty());
    }

    public function testApiPush_AddsDataAtTheEnd()
    {
        $this->buffer->push($this->appendString);

        $this->assertSame($this->initialString . $this->appendString, (string) $this->buffer);
    }

    public function testApiUnshift_AddsDataAtTheBeginning()
    {
        $this->buffer->unshift($this->appendString);

        $this->assertSame($this->appendString . $this->initialString, (string) $this->buffer);
    }

    public function testApiShift_RemovesDataFromTheBeginning()
    {
        $length = 10;
        $result = $this->buffer->shift($length);

        $this->assertSame(substr($this->initialString, 0, $length), $result);
        $this->assertSame(substr($this->initialString, $length), (string) $this->buffer);
    }

    public function testApiShift_DoesNotChangeBufferState_WhenNegativeLengthIsPassed()
    {
        $length = -1;
        $result = $this->buffer->shift($length);

        $this->assertSame('', $result);
        $this->assertSame($this->initialString, (string) $this->buffer);
    }

    public function testApiPeek_ReturnsProperSubstring()
    {
        $length = 10;
        $result = $this->buffer->peek($length);

        $this->assertSame(substr($this->initialString, 0, $length), $result);
        $this->assertSame($this->initialString, (string) $this->buffer);
    }

    public function testApiPeek_ReturnsProperSubstring_WhenOffsetIsPassed()
    {
        $length = 10;
        $offset = 5;
        $result = $this->buffer->peek($length, $offset);

        $this->assertSame(substr($this->initialString, $offset, $length), $result);
        $this->assertSame($this->initialString, (string) $this->buffer);
    }

    public function testApiPeek_ReturnsEmptyString_WhenNegativeLengthIsPassed()
    {
        $length = -1;
        $result = $this->buffer->peek($length);

        $this->assertSame('', $result);
        $this->assertSame($this->initialString, (string) $this->buffer);
    }

    public function testApiPeek_TreatsOffsetAsZero_WhenNegativeOffsetIsPassed()
    {
        $length = 10;
        $offset = -1;
        $result = $this->buffer->peek($length, $offset);

        $this->assertSame(substr($this->initialString, 0, $length), $result);
        $this->assertSame($this->initialString, (string) $this->buffer);
    }

    public function testApiPeek_ReturnsWholeData_WhenLengthGreaterThanBufferLengthIsPassed()
    {
        $length = 100;
        $result = $this->buffer->peek($length);

        $this->assertSame($this->initialString, $result);
        $this->assertSame($this->initialString, (string) $this->buffer);
    }

    public function testApiPop_ReturnsProperString()
    {
        $length = 10;
        $result = $this->buffer->pop($length);

        $this->assertSame(substr($this->initialString, -$length), $result);
        $this->assertSame(substr($this->initialString, 0, -$length), (string) $this->buffer);
    }

    public function testApiPop_ReturnsEmptyString_WhenInvalidLengthIsPassed()
    {
        $length = -1;
        $result = $this->buffer->pop($length);

        $this->assertSame('', $result);
        $this->assertSame($this->initialString, (string) $this->buffer);
    }

    public function testApiRemove_RemovesAndReturnsValidData()
    {
        $length = 10;
        $result = $this->buffer->remove($length);

        $this->assertSame(substr($this->initialString, 0, $length), $result);
        $this->assertSame(substr($this->initialString, $length), (string) $this->buffer);
    }


    public function testApiRemove_RemovesAndReturnsValidData_WhenOffsetIsPassed()
    {
        $length = 10;
        $offset = 5;
        $result = $this->buffer->remove($length, $offset);

        $this->assertSame(substr($this->initialString, $offset, $length), $result);
        $this->assertSame(substr($this->initialString, 0, $offset) . substr($this->initialString, $offset + $length), (string) $this->buffer);
    }

    public function testApiRemove_RemovesNothingAndReturnsEmptyString_WhenInvalidLengthIsPassed()
    {
        $length = -1;
        $result = $this->buffer->remove($length);

        $this->assertSame('', $result);
        $this->assertSame($this->initialString, (string) $this->buffer);
    }

    public function testApiRemove_RemovesAndReturnsValidData_WhenInvalidOffsetIsPassed()
    {
        $length = 10;
        $offset = -1;
        $result = $this->buffer->remove($length, $offset);

        $this->assertSame(substr($this->initialString, 0, $length), $result);
        $this->assertSame(substr($this->initialString, $length), (string) $this->buffer);
    }

    public function testApiDrain_EmptiesBuffer()
    {
        $result = $this->buffer->drain();

        $this->assertSame($this->initialString, $result);
        $this->assertSame('', (string) $this->buffer);
        $this->assertTrue($this->buffer->isEmpty());
    }

    public function testApiInsert_InsertsDataAtProperPosition()
    {
        $position = 10;
        $this->buffer->insert($this->appendString, $position);

        $this->assertSame(substr($this->initialString, 0, $position) . $this->appendString . substr($this->initialString, $position), (string) $this->buffer);
    }

    public function testApiReplace_ReplacesProperSubstring()
    {
        $search = substr($this->initialString, 3, 3);
        $result = $this->buffer->replace($search, $this->appendString);

        $this->assertSame(1, $result);
        $this->assertSame(substr($this->initialString, 0, 3) . $this->appendString . substr($this->initialString, 6), (string) $this->buffer);
    }

    public function testApiSearch_FindsProperIndex()
    {
        $search = substr($this->initialString, 3, 3);
        $index = $this->buffer->search($search);

        $this->assertSame(3, $index);
        $this->assertFalse($this->buffer->search($this->appendString));
    }

    public function testApiSearch_FindsProperIndex_WhenReverseIsPassed()
    {
        $search = substr($this->initialString, 0, 3);
        $this->buffer->push($this->initialString);
        $index = $this->buffer->search($search, true);

        $this->assertSame(strlen($this->initialString), $index);
    }

    public function testOffsetExists()
    {
        $this->assertTrue($this->buffer->offsetExists(0));
        $this->assertTrue($this->buffer->offsetExists(strlen($this->initialString) - 1));
        $this->assertFalse($this->buffer->offsetExists(strlen($this->initialString)));
        $this->assertFalse($this->buffer->offsetExists(-1));
    }

    public function testOffsetGet()
    {
        $this->assertSame(substr($this->initialString, 0, 1), $this->buffer->offsetGet(0));
        $this->assertSame(substr($this->initialString, -1, 1), $this->buffer->offsetGet(strlen($this->initialString) - 1));
    }

    public function testOffsetSet()
    {
        $this->buffer->offsetSet(0, $this->appendString);

        $this->assertSame($this->appendString . substr($this->initialString, 1), (string) $this->buffer);
    }

    public function testOffsetUnset()
    {
        $this->buffer->offsetUnset(0);

        $this->assertSame(substr($this->initialString, 1), (string) $this->buffer);
    }

    public function testGetInterator()
    {
        $iterator = $this->buffer->getIterator();

        $this->assertInstanceOf(BufferIterator::class, $iterator);
    }
}
