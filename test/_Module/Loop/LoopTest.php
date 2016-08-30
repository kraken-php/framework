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
     *
     */
    public function tearDown()
    {
        $this->destroyStream();

        parent::tearDown();
    }

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

        unset($stream);
        unset($loop);
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

        unset($stream);
        unset($loop);
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

        unset($stream);
        unset($loop);
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

        unset($stream);
        unset($loop);
    }

    /**
     * @dataProvider loopsProvider
     * @param LoopExtendedInterface|LoopModelInterface|mixed $loop
     */
    public function testApiRemoveReadStream_ThrowsNoErrors_OnInvalidStream($loop)
    {
        $stream = $this->createStream();

        $loop->removeWriteStream($stream);

        unset($stream);
        unset($loop);
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

        unset($stream);
        unset($loop);
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

        unset($stream);
        unset($loop);
    }

    /**
     * @dataProvider loopsProvider
     * @param LoopExtendedInterface|LoopModelInterface|mixed $loop
     */
    public function testApiRemoveWriteStream_ThrowsNoErrors_OnInvalidStream($loop)
    {
        $stream = $this->createStream();

        $loop->removeWriteStream($stream);

        unset($stream);
        unset($loop);
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

        unset($stream);
        unset($loop);
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

        unset($stream);
        unset($loop);
    }

    /**
     * @dataProvider loopsProvider
     * @param LoopExtendedInterface|LoopModelInterface|mixed $loop
     */
    public function testApiRemoveStream_ThrowsNoErrors_OnInvalidStream($loop)
    {
        $stream = $this->createStream();

        $loop->removeStream($stream);

        unset($stream);
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

        unset($loop);
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

        $loop->start();

        $this->assertEquals(5, $cnt);

        unset($loop);
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

        unset($timer);
        unset($loop);
    }

    /**
     * @dataProvider loopsProvider
     * @param LoopExtendedInterface|LoopModelInterface|mixed $loop
     */
    public function testApiIsTimerActive_ReturnsTrue_ForActiveTimer($loop)
    {
        $timer = $loop->addTimer(1e-3, $this->expectCallableNever());

        $this->assertTrue($loop->isTimerActive($timer));

        unset($timer);
        unset($loop);
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

        unset($timer);
        unset($loop);
    }

    /**
     * @dataProvider loopsProvider
     * @param LoopExtendedInterface|LoopModelInterface|mixed $loop
     */
    public function testApiOnStart_AddsActiveHandler_OnStart($loop)
    {
        $loop->onAfterTick(function() use($loop) {
            $loop->stop();
        });
        $loop->onStart($this->expectCallableOnce());

        $loop->start();

        unset($loop);
    }

    /**
     * @dataProvider loopsProvider
     * @param LoopExtendedInterface|LoopModelInterface|mixed $loop
     */
    public function testApiOnStop_AddsActiveHandler_OnStop($loop)
    {
        $loop->onAfterTick(function() use($loop) {
            $loop->stop();
        });
        $loop->onStop($this->expectCallableOnce());

        $loop->start();

        unset($loop);
    }

    /**
     * @dataProvider loopsProvider
     * @param LoopExtendedInterface|LoopModelInterface|mixed $loop
     */
    public function testApiOnBeforeTick_AddsActiveHandler_BeforeTick($loop)
    {
        $loop->onBeforeTick($this->expectCallableOnce());

        $loop->tick();

        unset($loop);
    }

    /**
     * @dataProvider loopsProvider
     * @param LoopExtendedInterface|LoopModelInterface|mixed $loop
     */
    public function testApiOnAfterTick_AddsActiveHandler_AfterTick($loop)
    {
        $loop->onAfterTick($this->expectCallableOnce());

        $loop->tick();

        unset($loop);
    }

    /**
     * @dataProvider loopsProvider
     * @param LoopExtendedInterface|LoopModelInterface|mixed $loop
     */
    public function testApiTick_TicksLoop($loop)
    {
        $loop->onBeforeTick($this->expectCallableOnce());
        $loop->onAfterTick($this->expectCallableOnce());

        $loop->tick();

        unset($loop);
    }

    /**
     * @dataProvider loopsProvider
     * @param LoopExtendedInterface|LoopModelInterface|mixed $loop
     */
    public function testApiStartAndApiStop_StartsAndStopsLoop($loop)
    {
        $loop->onAfterTick(function() use($loop) {
            $this->assertTrue($loop->isRunning());
            $loop->stop();
            $this->assertFalse($loop->isRunning());
        });

        $loop->start();

        unset($loop);
    }

    /**
     * @dataProvider loopsProvider
     * @param LoopExtendedInterface|LoopModelInterface|mixed $loop
     */
    public function testApiGetFlowController_ReturnsFlowController($loop)
    {
        $this->assertInstanceOf(FlowController::class, $loop->getFlowController());

        unset($loop);
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

        unset($controller);
        unset($loop);
    }

    /**
     * @dataProvider loopsProvider
     * @param LoopExtendedInterface|LoopModelInterface|mixed $loop
     */
    public function testApiFlush_FlushesOnlyTickHandlers_WhenAllFlagSetToFalse($loop)
    {
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
     * @dataProvider loopsProvider
     * @param LoopExtendedInterface|LoopModelInterface|mixed $loop
     */
    public function testApiFlush_FlushesAllHandlers_WhenAllFlagSetToTrue($loop)
    {
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
}
