<?php

namespace Kraken\_Unit\Loop;

use Kraken\Loop\Model\StreamSelectLoop;
use Kraken\Test\TUnit;

class StreamSelectLoopTest extends TUnit
{
    /**
     * @var resource
     */
    private $fp;

    public function tearDown()
    {
        $this->destroyStream();
    }

    public function testConstructor()
    {
        $loop = $this->createLoop();
    }

    public function testApiAddReadStream_CallsReadHandler_OnTick()
    {
        $loop = $this->createLoop();
        $stream = $this->createStream();

        $loop->addReadStream($stream, $this->expectCallableExactly(2));

        $this->writeToStream($stream, "foo\n");
        $loop->tick();

        $this->writeToStream($stream, "bar\n");
        $loop->tick();
    }

    public function testApiAddWriteStream_CallsWriteHandler_OnTick()
    {
        $loop = $this->createLoop();
        $stream = $this->createStream();

        $loop->addWriteStream($stream, $this->expectCallableExactly(2));
        $loop->tick();
        $loop->tick();
    }

    public function testApiRemoveReadStream_RemovesReadHandler_Instantly()
    {
        $loop = $this->createLoop();
        $stream = $this->createStream();

        $loop->addReadStream($stream, $this->expectCallableNever());
        $loop->removeReadStream($stream);

        $this->writeToStream($stream, "bar\n");
        $loop->tick();
    }

    public function testApiRemoveReadStream_RemovesReadHandler_AfterReading()
    {
        $loop = $this->createLoop();
        $stream = $this->createStream();

        $loop->addReadStream($stream, $this->expectCallableOnce());

        $this->writeToStream($stream, "foo\n");
        $loop->tick();

        $loop->removeReadStream($stream);

        $this->writeToStream($stream, "bar\n");
        $loop->tick();
    }

    public function testApiRemoveReadStream_ThrowsNoErrors_OnInvalidStream()
    {
        $loop = $this->createLoop();
        $stream = $this->createStream();

        $loop->removeWriteStream($stream);
    }

    public function testApiRemoveWriteStream_RemovesWriteHandler_Instantly()
    {
        $loop = $this->createLoop();
        $stream = $this->createStream();

        $loop->addWriteStream($stream, $this->expectCallableNever());
        $loop->removeWriteStream($stream);
        $loop->tick();
    }

    public function testApiRemoveWriteStream_RemovesWriteHandler_AfterWriting()
    {
        $loop = $this->createLoop();
        $stream = $this->createStream();

        $loop->addWriteStream($stream, $this->expectCallableOnce());
        $loop->tick();

        $loop->removeWriteStream($stream);
        $loop->tick();
    }

    public function testApiRemoveWriteStream_ThrowsNoErrors_OnInvalidStream()
    {
        $loop = $this->createLoop();
        $stream = $this->createStream();

        $loop->removeWriteStream($stream);
    }

    public function testApiRemoveStream_RemovesWriteReadHandlers_Instantly()
    {
        $loop = $this->createLoop();
        $stream = $this->createStream();

        $loop->addReadStream($stream, $this->expectCallableNever());
        $loop->addWriteStream($stream, $this->expectCallableNever());
        $loop->removeStream($stream);

        $this->writeToStream($stream, "bar\n");
        $loop->tick();
    }

    public function testApiRemoveStream_RemovesWriteReadHandlers_AfterHandling()
    {
        $loop = $this->createLoop();
        $stream = $this->createStream();

        $loop->addReadStream($stream, $this->expectCallableOnce());
        $loop->addWriteStream($stream, $this->expectCallableOnce());

        $this->writeToStream($stream, "bar\n");
        $loop->tick();

        $loop->removeStream($stream);

        $this->writeToStream($stream, "bar\n");
        $loop->tick();
    }

    public function testApiRemoveStream_ThrowsNoErrors_OnInvalidStream()
    {
        $loop = $this->createLoop();
        $stream = $this->createStream();

        $loop->removeStream($stream);
    }

    public function testApiAddTimer_AddsTimer()
    {
        $loop = $this->createLoop();

        $expectedData = 'next-tick';
        $receivedData = null;

        $loop->addTimer(1e-3, function() use($loop, &$receivedData) {
            $loop->stop();
            $receivedData = 'next-tick';
        });

        $loop->start();

        $this->assertEquals($expectedData, $receivedData);
    }

    public function testApiAddPeriodicTimer_AddsPeriodicTimer()
    {
        $loop = $this->createLoop();
        $cnt = 0;

        $loop->addPeriodicTimer(1e-3, function() use($loop, &$cnt) {
            $cnt++;
            if ($cnt == 5)
            {
                $loop->stop();
            }
        });

        $loop->addTimer(1e-2, function() use($loop) {
            $loop->stop();
        });

        $loop->start();

        $this->assertEquals(5, $cnt);
    }

    public function testApiCancelTimer_CancelsTimer()
    {
        $loop = $this->createLoop();

        $timer = $loop->addTimer(1e-3, $this->expectCallableNever());
        $loop->cancelTimer($timer);

        $loop->tick();
    }

    public function testApiIsTimerActive_ReturnsTrue_ForActiveTimer()
    {
        $loop = $this->createLoop();

        $timer = $loop->addTimer(1e-3, $this->expectCallableNever());

        $this->assertTrue($loop->isTimerActive($timer));
    }

    public function testApiIsTimerActive_ReturnsFalse_ForInActiveTimer()
    {
        $loop = $this->createLoop();

        $timer = $loop->addTimer(1e-3, $this->expectCallableNever());
        $loop->cancelTimer($timer);

        $this->assertFalse($loop->isTimerActive($timer));
    }

    public function testApiStartTick_AddsActiveHandler_OnStart()
    {
        $loop = $this->createLoop();

        $loop->afterTick(function() use($loop) {
            $loop->stop();
        });
        $loop->startTick($this->expectCallableOnce());

        $loop->start();
    }

    public function testApiStopTick_AddsActiveHandler_OnStop()
    {
        $loop = $this->createLoop();

        $loop->afterTick(function() use($loop) {
            $loop->stop();
        });
        $loop->stopTick($this->expectCallableOnce());

        $loop->start();
    }

    public function testApiBeforeTick_AddsActiveHandler_BeforeTick()
    {
        $loop = $this->createLoop();

        $loop->beforeTick($this->expectCallableOnce());

        $loop->tick();
    }

    public function testApiAfterTick_AddsActiveHandler_AfterTick()
    {
        $loop = $this->createLoop();

        $loop->afterTick($this->expectCallableOnce());

        $loop->tick();
    }

    public function testApiTick_TicksLoop()
    {
        $loop = $this->createLoop();

        $loop->beforeTick($this->expectCallableOnce());
        $loop->afterTick($this->expectCallableOnce());

        $loop->tick();
    }

    public function testApiStartAndApiStop_StartsAndStopsLoop()
    {
        $loop = $this->createLoop();

        $loop->afterTick(function() use($loop) {
            $this->assertTrue($loop->isRunning());
            $loop->stop();
            $this->assertFalse($loop->isRunning());
        });

        $loop->start();
    }

    public function testApiFlush_FlushesOnlyTickHandlers_OnAllEqualsFalse()
    {
        $loop = $this->createLoop();
        $stream = $this->createStream();

        $loop->afterTick($this->expectCallableNever());
        $loop->beforeTick($this->expectCallableNever());
        $loop->addWriteStream($stream, $this->expectCallableOnce());
        $loop->addReadStream($stream, $this->expectCallableOnce());

        $loop->flush();
        $loop->tick();
    }

    public function testApiFlush_FlushesAllHandlers_OnAllEqualsTrue()
    {
        $loop = $this->createLoop();
        $stream = $this->createStream();

        $loop->afterTick($this->expectCallableNever());
        $loop->beforeTick($this->expectCallableNever());
        $loop->addWriteStream($stream, $this->expectCallableNever());
        $loop->addReadStream($stream, $this->expectCallableNever());

        $loop->flush(true);
        $loop->tick();
    }

    /**
     * @return StreamSelectLoop
     */
    protected function createLoop()
    {
        return new StreamSelectLoop();
    }

    /**
     * @param resource $stream
     * @param string $content
     */
    private function writeToStream($stream, $content)
    {
        fwrite($stream, $content);
        rewind($stream);
    }

    /**
     * @return resource
     */
    private function createStream()
    {
        $this->fp = fopen('php://temp', 'r+');
        return $this->fp;
    }

    /**
     *
     */
    private function destroyStream()
    {
        if (is_resource($this->fp))
        {
            unset($this->fp);
        }
    }
}
