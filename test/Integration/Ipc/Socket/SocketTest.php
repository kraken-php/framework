<?php

namespace Kraken\Test\Integration\Ipc\Socket;

use Kraken\Ipc\Socket\Socket;
use Kraken\Ipc\Socket\SocketInterface;
use Kraken\Ipc\Socket\SocketListener;
use Kraken\Ipc\Socket\SocketListenerInterface;
use Kraken\Test\Integration\Stub\Simulation;
use Kraken\Test\Integration\TestCase;

class SocketTest extends TestCase
{
    /**
     * @dataProvider endpointProvider
     */
    public function testSocketWritesAndReadsDataCorrectly($endpoint)
    {
        $this
            ->simulate(function(Simulation $sim) use($endpoint) {
                $loop = $sim->getLoop();

                $server = new SocketListener($endpoint, $loop);
                $server->on('connect', function(SocketListenerInterface $server, SocketInterface $conn) use($sim) {
                    $conn->on('data', function(SocketInterface $conn, $data) use($server, $sim) {
                        $sim->expectEvent('data', $data);
                        $conn->write('secret answer!');
                        $server->close();
                    });
                });
                $server->on('error', $this->expectCallableNever());
                $server->on('close', function() use($sim) {
                    $sim->expectEvent('close');
                });

                $client = new Socket($endpoint, $loop);
                $client->on('data', function(SocketInterface $conn, $data) use($loop, $sim) {
                    $sim->expectEvent('data', $data);
                    $conn->close();
                    $sim->done();
                });
                $client->on('error', $this->expectCallableNever());
                $client->on('close', function() use($sim) {
                    $sim->expectEvent('close');
                });

                $client->write('secret question!');
            })
            ->expect([
                [ 'data', 'secret question!' ],
                [ 'close' ],
                [ 'data', 'secret answer!' ],
                [ 'close' ]
            ])
        ;
    }

    /**
     * @return string[][]
     */
    public function endpointProvider()
    {
        return [
            [ 'tcp://127.0.0.1:2080' ],
            [ 'tcp://[::1]:2080' ],
            [ 'unix://mysocket.sock' ]
        ];
    }
}
