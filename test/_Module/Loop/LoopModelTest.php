<?php

namespace Kraken\_Module\Loop;

use Kraken\Loop\Flow\FlowController;
use Kraken\Loop\Model\SelectLoop;
use Kraken\Loop\Loop;
use Kraken\Loop\LoopExtendedInterface;
use Kraken\Loop\LoopModelInterface;
use Kraken\Test\TModule;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class LoopModelTest extends TModule
{
    /**
     *
     */
    public function testApiAddReadStream_CallsReadHandler_OnTick()
    {
        $loop = $this->createLoop();
        $stream = $this->createStream();

        $loop->addReadStream($stream, $this->expectCallableExactly(2));

        $this->writeToStream($stream, "foo\n");
        $loop->tick();

        $this->writeToStream($stream, "bar\n");
        $loop->tick();

        unset($stream);
        unset($loop);
    }

    /**
     *
     */
    public function testApiAddWriteStream_CallsWriteHandler_OnTick()
    {
        $loop = $this->createLoop();
        $stream = $this->createStream();

        $loop->addWriteStream($stream, $this->expectCallableExactly(2));
        $loop->tick();
        $loop->tick();

        unset($stream);
        unset($loop);
    }

    /**
     *
     */
    public function testApiRemoveReadStream_RemovesReadHandler_Instantly()
    {
        $loop = $this->createLoop();
        $stream = $this->createStream();

        $loop->addReadStream($stream, $this->expectCallableNever());
        $loop->removeReadStream($stream);

        $this->writeToStream($stream, "bar\n");
        $loop->tick();

        unset($stream);
        unset($loop);
    }

    /**
     *
     */
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

        unset($stream);
        unset($loop);
    }

    /**
     *
     */
    public function testApiRemoveReadStream_ThrowsNoErrors_OnInvalidStream()
    {
        $loop = $this->createLoop();
        $stream = $this->createStream();

        $loop->removeWriteStream($stream);

        unset($stream);
        unset($loop);
    }

    /**
     *
     */
    public function testApiRemoveWriteStream_RemovesWriteHandler_Instantly()
    {
        $loop = $this->createLoop();
        $stream = $this->createStream();

        $loop->addWriteStream($stream, $this->expectCallableNever());
        $loop->removeWriteStream($stream);
        $loop->tick();

        unset($stream);
        unset($loop);
    }

    /**
     *
     */
    public function testApiRemoveWriteStream_RemovesWriteHandler_AfterWriting()
    {
        $loop = $this->createLoop();
        $stream = $this->createStream();

        $loop->addWriteStream($stream, $this->expectCallableOnce());
        $loop->tick();

        $loop->removeWriteStream($stream);
        $loop->tick();

        unset($stream);
        unset($loop);
    }

    /**
     *
     */
    public function testApiRemoveWriteStream_ThrowsNoErrors_OnInvalidStream()
    {
        $loop = $this->createLoop();
        $stream = $this->createStream();

        $loop->removeWriteStream($stream);

        unset($stream);
        unset($loop);
    }

    /**
     *
     */
    public function testApiRemoveStream_RemovesWriteReadHandlers_Instantly()
    {
        $loop = $this->createLoop();
        $stream = $this->createStream();

        $loop->addReadStream($stream, $this->expectCallableNever());
        $loop->addWriteStream($stream, $this->expectCallableNever());
        $loop->removeStream($stream);

        $this->writeToStream($stream, "bar\n");
        $loop->tick();

        unset($stream);
        unset($loop);
    }

    /**
     *
     */
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

        unset($stream);
        unset($loop);
    }

    /**
     *
     */
    public function testApiRemoveStream_ThrowsNoErrors_OnInvalidStream()
    {
        $loop = $this->createLoop();
        $stream = $this->createStream();

        $loop->removeStream($stream);

        unset($stream);
    }

    /**
     *
     */
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

        unset($loop);
    }

    /**
     *
     */
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

        $loop->start();

        $this->assertEquals(5, $cnt);

        unset($loop);
    }

    /**
     *
     */
    public function testApiCancelTimer_CancelsTimer()
    {
        $loop = $this->createLoop();

        $timer = $loop->addTimer(1e-3, $this->expectCallableNever());
        $loop->cancelTimer($timer);

        $loop->tick();

        unset($timer);
        unset($loop);
    }

    /**
     *
     */
    public function testApiIsTimerActive_ReturnsTrue_ForActiveTimer()
    {
        $loop = $this->createLoop();

        $timer = $loop->addTimer(1e-3, $this->expectCallableNever());

        $this->assertTrue($loop->isTimerActive($timer));

        unset($timer);
        unset($loop);
    }

    /**
     *
     */
    public function testApiIsTimerActive_ReturnsFalse_ForInActiveTimer()
    {
        $loop = $this->createLoop();

        $timer = $loop->addTimer(1e-3, $this->expectCallableNever());
        $loop->cancelTimer($timer);

        $this->assertFalse($loop->isTimerActive($timer));

        unset($timer);
        unset($loop);
    }

    /**
     *
     */
    public function testApiOnStart_AddsActiveHandler_OnStart()
    {
        $loop = $this->createLoop();

        $loop->onAfterTick(function() use($loop) {
            $loop->stop();
        });
        $loop->onStart($this->expectCallableOnce());

        $loop->start();

        unset($loop);
    }

    /**
     *
     */
    public function testApiOnStop_AddsActiveHandler_OnStop()
    {
        $loop = $this->createLoop();

        $loop->onAfterTick(function() use($loop) {
            $loop->stop();
        });
        $loop->onStop($this->expectCallableOnce());

        $loop->start();

        unset($loop);
    }

    /**
     *
     */
    public function testApiOnBeforeTick_AddsActiveHandler_BeforeTick()
    {
        $loop = $this->createLoop();

        $loop->onBeforeTick($this->expectCallableOnce());

        $loop->tick();

        unset($loop);
    }

    /**
     *
     */
    public function testApiOnAfterTick_AddsActiveHandler_AfterTick()
    {
        $loop = $this->createLoop();

        $loop->onAfterTick($this->expectCallableOnce());
        $loop->tick();

        unset($loop);
    }

    /**
     */
    public function testApiTick_TicksLoop()
    {
        $loop = $this->createLoop();

        $loop->onBeforeTick($this->expectCallableOnce());
        $loop->onAfterTick($this->expectCallableOnce());

        $loop->tick();

        unset($loop);
    }

    /**
     *
     */
    public function testApiStartAndApiStop_StartsAndStopsLoop()
    {
        $loop = $this->createLoop();

        $loop->onAfterTick(function() use($loop) {
            $this->assertTrue($loop->isRunning());
            $loop->stop();
            $this->assertFalse($loop->isRunning());
        });

        $loop->start();

        unset($loop);
    }

    /**
     *
     */
    public function testApiGetFlowController_ReturnsFlowController()
    {
        $loop = $this->createLoop();

        $this->assertInstanceOf(FlowController::class, $loop->getFlowController());

        unset($loop);
    }

    /**
     *
     */
    public function testApiSetFlowController_SetsFlowController()
    {
        $loop = $this->createLoop();
        $controller = new FlowController();

        $loop->setFlowController($controller);
        $this->assertSame($controller, $loop->getFlowController());

        unset($controller);
        unset($loop);
    }

    /**
     *
     */
    public function testApiFlush_FlushesOnlyTickHandlers_WhenAllFlagSetToFalse()
    {
        $loop = $this->createLoop();
        $stream = $this->createStream();

        $loop->onAfterTick($this->expectCallableNever());
        $loop->onBeforeTick($this->expectCallableNever());
        $loop->addWriteStream($stream, $this->expectCallableOnce());
        $loop->addReadStream($stream, $this->expectCallableOnce());

        $loop->erase();
        $loop->tick();

        unset($stream);
        unset($loop);
    }

    /**
     *
     */
    public function testApiFlush_FlushesAllHandlers_WhenAllFlagSetToTrue()
    {
        $loop = $this->createLoop();
        $stream = $this->createStream();

        $loop->onAfterTick($this->expectCallableNever());
        $loop->onBeforeTick($this->expectCallableNever());
        $loop->addWriteStream($stream, $this->expectCallableNever());
        $loop->addReadStream($stream, $this->expectCallableNever());

        $loop->erase(true);
        $loop->tick();

        unset($stream);
        unset($loop);
    }

    /**
     *
     */
    public function testApiExport_ExportsOnlyTickHandlers_WhenAllFlagSetToFalse()
    {
        $this->markTestIncomplete('test should be reimplemented.');
    }

    /**
     *
     */
    public function testApiExport_ExportsAllHandlers_WhenAllFlagSetToTrue()
    {
        $this->markTestIncomplete('test should be reimplemented.');
    }

    /**
     *
     */
    public function testApiImport_ImportsOnlyTickHandlers_WhenAllFlagSetToFalse()
    {
        $this->markTestIncomplete('test should be reimplemented.');
    }

    /**
     *
     */
    public function testApiImport_ImportsAllHandlers_WhenAllFlagSetToTrue()
    {
        $this->markTestIncomplete('test should be reimplemented.');
    }

    /**
     *
     */
    public function testApiSwap_SwapsOnlyTickHandlers_WhenAllFlagSetToFalse()
    {
        $this->markTestIncomplete('test should be reimplemented.');
    }

    /**
     *
     */
    public function testApiSwap_SwapsAllHandlers_WhenAllFlagSetToTrue()
    {
        $this->markTestIncomplete('test should be reimplemented.');
    }

    /**
     * @return LoopExtendedInterface|LoopModelInterface
     */
    public function createLoop()
    {
        return new SelectLoop();
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
        return fopen('php://temp', 'r+');
    }
}
