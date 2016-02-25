<?php

namespace Kraken\Test\Unit\Pattern\Buffer;

use Kraken\Test\Unit\TestCase;
use Kraken\Throwable\Runtime\OutOfBoundsException;
use Kraken\Util\Buffer\Buffer;
use Kraken\Util\Buffer\BufferInterface;
use Kraken\Util\Buffer\BufferIterator;

class BufferIteratorTest extends TestCase
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

    /**
     * @var BufferIterator
     */
    protected $iterator;
    
    public function setUp()
    {
        $this->buffer = new Buffer($this->initialString);
        $this->iterator = $this->buffer->getIterator();
    }
    
    public function testApiSeek()
    {
        $this->iterator->seek($this->buffer->length()-1);
        
        $this->assertSame($this->buffer->length()-1, $this->iterator->key());
        $this->assertSame(substr($this->buffer, -1), $this->iterator->current());
    }
    
    public function testApiSeek_WhenInvalidPositionIsPassed()
    {
        $this->iterator->seek(-1);
        
        $this->assertSame(0, $this->iterator->key());
    }
    
    public function testApiInsert()
    {
        $this->iterator->next();
        $this->iterator->insert($this->appendString);
        
        $this->assertSame(substr($this->initialString, 0, 1) . $this->appendString . substr($this->initialString, 1), (string) $this->buffer);
    }

    public function testApiInsert_ThrowsException_OnInvalidIterator()
    {
        $this->setExpectedException(OutOfBoundsException::class);
        for ($this->iterator->rewind(); $this->iterator->valid(); $this->iterator->next());
        
        $this->iterator->insert($this->appendString);
    }
    
    public function testApiReplace()
    {
        $this->iterator->replace($this->appendString);
        
        $this->assertSame($this->appendString . substr($this->initialString, 1), (string) $this->buffer);
    }

    public function testApiReplace_ThrowsException_OnInvalidIterator()
    {
        $this->setExpectedException(OutOfBoundsException::class);
        for ($this->iterator->rewind(); $this->iterator->valid(); $this->iterator->next());
        
        $this->iterator->replace($this->appendString);
    }
    
    public function testApiRemove()
    {
        $this->iterator->remove();
        
        $this->assertSame(substr($this->initialString, 1), (string) $this->buffer);
        
        $this->iterator->next();
        $this->iterator->remove();
        
        $this->assertSame(substr($this->initialString, 2), (string) $this->buffer);
    }

    public function testApiRemove_ThrowsException_OnInvalidIterator()
    {
        $this->setExpectedException(OutOfBoundsException::class);
        for ($this->iterator->rewind(); $this->iterator->valid(); $this->iterator->next());
        
        $this->iterator->remove();
    }
}
