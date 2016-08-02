<?php

namespace Kraken\_Module\Loop;

use Kraken\Loop\Flow\FlowController;
use Kraken\Loop\Model\SelectLoop;
use Kraken\Loop\Loop;
use Kraken\Loop\LoopExtendedInterface;
use Kraken\Loop\LoopModelInterface;
use Kraken\Test\TModule;

class LoopTest extends TModule
{
    /**
     * @var resource
     */
    private $fp;

    /**
     * @dataProvider loopsProvider
     * @param LoopExtendedInterface|LoopModelInterface|mixed $loop
     */
    public function testApiAddReadStream_CallsReadHandler_OnTick($loop)
    {
        $stream = $this->createStream();

        $loop->addReadStream($stream, $this->expectCallableExactly(2));

        $this->writeToStream($stream, "foo\n");
        $loop->tick();

        $this->writeToStream($stream, "bar\n");
        $loop->tick();
    }

    /**
     * @dataProvider loopsProvider
     * @param LoopExtendedInterface|LoopModelInterface|mixed $loop
     */
    public function testApiAddWriteStream_CallsWriteHandler_OnTick($loop)
    {
        $stream = $this->createStream();

        $loop->addWriteStream($stream, $this->expectCallableExactly(2));
        $loop->tick();
        $loop->tick();
    }

    /**
     * @dataProvider loopsProvider
     * @param LoopExtendedInterface|LoopModelInterface|mixed $loop
     */
    public function testApiRemoveReadStream_RemovesReadHandler_Instantly($loop)
    {
        $stream = $this->createStream();

        $loop->addReadStream($stream, $this->expectCallableNever());
        $loop->removeReadStream($stream);

        $this->writeToStream($stream, "bar\n");
        $loop->tick();
    }

    /**
     * @dataProvider loopsProvider
     * @param LoopExtendedInterface|LoopModelInterface|mixed $loop
     */
    public function testApiRemoveReadStream_RemovesReadHandler_AfterReading($loop)
    {
        $stream = $this->createStream();

        $loop->addReadStream($stream, $this->expectCallableOnce());

        $this->writeToStream($stream, "foo\n");
        $loop->tick();

        $loop->removeReadStream($stream);

        $this->writeToStream($stream, "bar\n");
        $loop->tick();
    }

    /**
     * @dataProvider loopsProvider
     * @param LoopExtendedInterface|LoopModelInterface|mixed $loop
     */
    public function testApiRemoveReadStream_ThrowsNoErrors_OnInvalidStream($loop)
    {
        $stream = $this->createStream();

        $loop->removeWriteStream($stream);
    }

    /**
     * @dataProvider loopsProvider
     * @param LoopExtendedInterface|LoopModelInterface|mixed $loop
     */
    public function testApiRemoveWriteStream_RemovesWriteHandler_Instantly($loop)
    {
        $stream = $this->createStream();

        $loop->addWriteStream($stream, $this->expectCallableNever());
        $loop->removeWriteStream($stream);
        $loop->tick();
    }

    /**
     * @dataProvider loopsProvider
     * @param LoopExtendedInterface|LoopModelInterface|mixed $loop
     */
    public function testApiRemoveWriteStream_RemovesWriteHandler_AfterWriting($loop)
    {
        $stream = $this->createStream();

        $loop->addWriteStream($stream, $this->expectCallableOnce());
        $loop->tick();

        $loop->removeWriteStream($stream);
        $loop->tick();
    }

    /**
     * @dataProvider loopsProvider
     * @param LoopExtendedInterface|LoopModelInterface|mixed $loop
     */
    public function testApiRemoveWriteStream_ThrowsNoErrors_OnInvalidStream($loop)
    {
        $stream = $this->createStream();

        $loop->removeWriteStream($stream);
    }

    /**
     * @dataProvider loopsProvider
     * @param LoopExtendedInterface|LoopModelInterface|mixed $loop
     */
    public function testApiRemoveStream_RemovesWriteReadHandlers_Instantly($loop)
    {
        $stream = $this->createStream();

        $loop->addReadStream($stream, $this->expectCallableNever());
        $loop->addWriteStream($stream, $this->expectCallableNever());
        $loop->removeStream($stream);

        $this->writeToStream($stream, "bar\n");
        $loop->tick();
    }

    /**
     * @dataProvider loopsProvider
     * @param LoopExtendedInterface|LoopModelInterface|mixed $loop
     */
    public function testApiRemoveStream_RemovesWriteReadHandlers_AfterHandling($loop)
    {
        $stream = $this->createStream();

        $loop->addReadStream($stream, $this->expectCallableOnce());
        $loop->addWriteStream($stream, $this->expectCallableOnce());

        $this->writeToStream($stream, "bar\n");
        $loop->tick();

        $loop->removeStream($stream);

        $this->writeToStream($stream, "bar\n");
        $loop->tick();
    }

    /**
     * @dataProvider loopsProvider
     * @param LoopExtendedInterface|LoopModelInterface|mixed $loop
     */
    public function testApiRemoveStream_ThrowsNoErrors_OnInvalidStream($loop)
    {
        $stream = $this->createStream();

        $loop->removeStream($stream);
    }

    /**
     * @dataProvider loopsProvider
     * @param LoopExtendedInterface|LoopModelInterface|mixed $loop
     */
    public function testApiAddTimer_AddsTimer($loop)
    {
        $expectedData = 'next-tick';
        $receivedData = null;

        $loop->addTimer(1e-3, function() use($loop, &$receivedData) {
            $loop->stop();
            $receivedData = 'next-tick';
        });

        $loop->start();

        $this->assertEquals($expectedData, $receivedData);
    }

    /**
     * @dataProvider loopsProvider
     * @param LoopExtendedInterface|LoopModelInterface|mixed $loop
     */
    public function testApiAddPeriodicTimer_AddsPeriodicTimer($loop)
    {
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

    /**
     * @dataProvider loopsProvider
     * @param LoopExtendedInterface|LoopModelInterface|mixed $loop
     */
    public function testApiCancelTimer_CancelsTimer($loop)
    {
        $timer = $loop->addTimer(1e-3, $this->expectCallableNever());
        $loop->cancelTimer($timer);

        $loop->tick();
    }

    /**
     * @dataProvider loopsProvider
     * @param LoopExtendedInterface|LoopModelInterface|mixed $loop
     */
    public function testApiIsTimerActive_ReturnsTrue_ForActiveTimer($loop)
    {
        $timer = $loop->addTimer(1e-3, $this->expectCallableNever());

        $this->assertTrue($loop->isTimerActive($timer));
    }

    /**
     * @dataProvider loopsProvider
     * @param LoopExtendedInterface|LoopModelInterface|mixed $loop
     */
    public function testApiIsTimerActive_ReturnsFalse_ForInActiveTimer($loop)
    {
        $timer = $loop->addTimer(1e-3, $this->expectCallableNever());
        $loop->cancelTimer($timer);

        $this->assertFalse($loop->isTimerActive($timer));
    }

    /**
     * @dataProvider loopsProvider
     * @param LoopExtendedInterface|LoopModelInterface|mixed $loop
     */
    public function testApiStartTick_AddsActiveHandler_OnStart($loop)
    {
        $loop->afterTick(function() use($loop) {
            $loop->stop();
        });
        $loop->startTick($this->expectCallableOnce());

        $loop->start();
    }

    /**
     * @dataProvider loopsProvider
     * @param LoopExtendedInterface|LoopModelInterface|mixed $loop
     */
    public function testApiStopTick_AddsActiveHandler_OnStop($loop)
    {
        $loop->afterTick(function() use($loop) {
            $loop->stop();
        });
        $loop->stopTick($this->expectCallableOnce());

        $loop->start();
    }

    /**
     * @dataProvider loopsProvider
     * @param LoopExtendedInterface|LoopModelInterface|mixed $loop
     */
    public function testApiBeforeTick_AddsActiveHandler_BeforeTick($loop)
    {
        $loop->beforeTick($this->expectCallableOnce());

        $loop->tick();
    }

    /**
     * @dataProvider loopsProvider
     * @param LoopExtendedInterface|LoopModelInterface|mixed $loop
     */
    public function testApiAfterTick_AddsActiveHandler_AfterTick($loop)
    {
        $loop->afterTick($this->expectCallableOnce());

        $loop->tick();
    }

    /**
     * @dataProvider loopsProvider
     * @param LoopExtendedInterface|LoopModelInterface|mixed $loop
     */
    public function testApiTick_TicksLoop($loop)
    {
        $loop->beforeTick($this->expectCallableOnce());
        $loop->afterTick($this->expectCallableOnce());

        $loop->tick();
    }

    /**
     * @dataProvider loopsProvider
     * @param LoopExtendedInterface|LoopModelInterface|mixed $loop
     */
    public function testApiStartAndApiStop_StartsAndStopsLoop($loop)
    {
        $loop->afterTick(function() use($loop) {
            $this->assertTrue($loop->isRunning());
            $loop->stop();
            $this->assertFalse($loop->isRunning());
        });

        $loop->start();
    }

    /**
     * @dataProvider loopsProvider
     * @param LoopExtendedInterface|LoopModelInterface|mixed $loop
     */
    public function testApiGetFlowController_ReturnsFlowController($loop)
    {
        $this->assertInstanceOf(FlowController::class, $loop->getFlowController());
    }

    /**
     * @dataProvider loopsProvider
     * @param LoopExtendedInterface|LoopModelInterface|mixed $loop
     */
    public function testApiSetFlowController_SetsFlowController($loop)
    {
        $controller = new FlowController();

        $loop->setFlowController($controller);
        $this->assertSame($controller, $loop->getFlowController());
    }

    /**
     * @dataProvider loopsProvider
     * @param LoopExtendedInterface|LoopModelInterface|mixed $loop
     */
    public function testApiFlush_FlushesOnlyTickHandlers_WhenAllFlagSetToFalse($loop)
    {
        $stream = $this->createStream();

        $loop->afterTick($this->expectCallableNever());
        $loop->beforeTick($this->expectCallableNever());
        $loop->addWriteStream($stream, $this->expectCallableOnce());
        $loop->addReadStream($stream, $this->expectCallableOnce());

        $loop->flush();
        $loop->tick();
    }

    /**
     * @dataProvider loopsProvider
     * @param LoopExtendedInterface|LoopModelInterface|mixed $loop
     */
    public function testApiFlush_FlushesAllHandlers_WhenAllFlagSetToTrue($loop)
    {
        $stream = $this->createStream();

        $loop->afterTick($this->expectCallableNever());
        $loop->beforeTick($this->expectCallableNever());
        $loop->addWriteStream($stream, $this->expectCallableNever());
        $loop->addReadStream($stream, $this->expectCallableNever());

        $loop->flush(true);
        $loop->tick();
    }

    /**
     * @dataProvider loopsProvider
     * @param LoopExtendedInterface|LoopModelInterface|mixed $loop
     */
    public function testApiExport_ExportsOnlyTickHandlers_WhenAllFlagSetToFalse($loop)
    {
        $this->markTestIncomplete('test should be reimplemented.');
    }

    /**
     * @dataProvider loopsProvider
     * @param LoopExtendedInterface|LoopModelInterface|mixed $loop
     */
    public function testApiExport_ExportsAllHandlers_WhenAllFlagSetToTrue($loop)
    {
        $this->markTestIncomplete('test should be reimplemented.');
    }

    /**
     * @dataProvider loopsProvider
     * @param LoopExtendedInterface|LoopModelInterface|mixed $loop
     */
    public function testApiImport_ImportsOnlyTickHandlers_WhenAllFlagSetToFalse($loop)
    {
        $this->markTestIncomplete('test should be reimplemented.');
    }

    /**
     * @dataProvider loopsProvider
     * @param LoopExtendedInterface|LoopModelInterface|mixed $loop
     */
    public function testApiImport_ImportsAllHandlers_WhenAllFlagSetToTrue($loop)
    {
        $this->markTestIncomplete('test should be reimplemented.');
    }

    /**
     * @dataProvider loopsProvider
     * @param LoopExtendedInterface|LoopModelInterface|mixed $loop
     */
    public function testApiSwap_SwapsOnlyTickHandlers_WhenAllFlagSetToFalse($loop)
    {
        $this->markTestIncomplete('test should be reimplemented.');
    }

    /**
     * @dataProvider loopsProvider
     * @param LoopExtendedInterface|LoopModelInterface|mixed $loop
     */
    public function testApiSwap_SwapsAllHandlers_WhenAllFlagSetToTrue($loop)
    {
        $this->markTestIncomplete('test should be reimplemented.');
    }

    /**
     * @return Loop[][]
     */
    public function createLoops()
    {
        return [
            [ new Loop(new SelectLoop()) ]
        ];
    }

    /**
     * @return LoopModelInterface[][]
     */
    public function createLoopModels()
    {
        return [
            [ new SelectLoop() ]
        ];
    }

    /**
     * @return LoopExtendedInterface[][]|LoopModelInterface[][]|mixed[][]
     */
    public function loopsProvider()
    {
        return array_merge(
            $this->createLoops(),
            $this->createLoopModels()
        );
    }

    /**
     *
     */
    public function tearDown()
    {
        $this->destroyStream();

        parent::tearDown();
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
        unset($this->fp);
    }

//    /**
//     * @dataProvider loopsProvider
//     * @param LoopExtendedInterface|LoopModelInterface|mixed $loop
//     */
//    public function testApiIsRunning_ReturnsTrue_WhileLoopIsRunning($loop)
//    {
//        $loop->startTick(function() use($loop) {
//            $this->assertTrue($loop->isRunning());
//            $loop->stop();
//        });
//        $loop->start();
//    }
//
//    /**
//     * @dataProvider loopsProvider
//     * @param LoopExtendedInterface|LoopModelInterface|mixed $loop
//     */
//    public function testApiIsRunning_ReturnsFalse_WhileLoopIsNotRunning($loop)
//    {
//        $this->assertFalse($loop->isRunning());
//    }
//
//    /**
//     * @dataProvider loopsProvider
//     * @param LoopExtendedInterface|LoopModelInterface|mixed $loop
//     */
//    public function testApiIsRunning_ReturnsFalse_WhileLoopIsNotRunning($loop)
//    {
//        $this->assertFalse($loop->isRunning());
//    }
}
