<?php

namespace Kraken\_Module\Stream;

use Kraken\Stream\AsyncStreamReaderInterface;
use Kraken\Stream\AsyncStreamWriterInterface;
use Kraken\_Module\Stub\Simulation;
use Kraken\_Module\TestCase;
use ReflectionClass;

class AsyncStreamTest extends TestCase
{
    public function tearDown()
    {
        $local = $this->basePath();
        unlink("$local/temp");
    }

    /**
     * @dataProvider asyncStreamPairProvider
     * @param string $readerClass
     * @param string $writerClass
     */
    public function testAsyncStream_WritesAndReadsDataCorrectly($readerClass, $writerClass)
    {
        $this
            ->simulate(function(Simulation $sim) use($readerClass, $writerClass) {
                $loop = $sim->getLoop();
                $local = $this->basePath();
                $cnt = 0;

                $reader = (new ReflectionClass($readerClass))->newInstance(
                    fopen("file://$local/temp", 'w+'),
                    $loop
                );
                $reader->on('data', function(AsyncStreamReaderInterface $conn, $data) use($sim) {
                    $sim->expectEvent('data', $data);
                    $conn->close();
                });
                $reader->on('drain', $this->expectCallableNever());
                $reader->on('error', $this->expectCallableNever());
                $reader->on('close', function() use($sim, &$cnt) {
                    $sim->expectEvent('close');
                    if (++$cnt === 2)
                    {
                        $sim->done();
                    }
                });

                $writer = (new ReflectionClass($writerClass))->newInstance(
                    fopen("file://$local/temp", 'r+'),
                    $loop
                );
                $writer->on('data', $this->expectCallableNever());
                $writer->on('drain', function(AsyncStreamWriterInterface $writer) use($sim) {
                    $sim->expectEvent('drain');
                    $writer->close();
                });
                $writer->on('error', $this->expectCallableNever());
                $writer->on('close', function() use($sim, &$cnt) {
                    $sim->expectEvent('close');
                    if (++$cnt === 2)
                    {
                        $sim->done();
                    }
                });

                $writer->write('message!');
            })
            ->expect([
                [ 'drain' ],
                [ 'close' ],
                [ 'data', 'message!' ],
                [ 'close' ]
            ])
        ;
    }

    /**
     * Provider classes of AsyncStream.
     *
     * @return string[][]
     */
    public function asyncStreamPairProvider()
    {
        return [
            [
                'Kraken\Stream\AsyncStream',
                'Kraken\Stream\AsyncStream'
            ],
            [
                'Kraken\Stream\AsyncStreamReader',
                'Kraken\Stream\AsyncStreamWriter'
            ]
        ];
    }
}
