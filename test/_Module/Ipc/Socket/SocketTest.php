<?php

namespace Kraken\_Module\Ipc\Socket;

use Kraken\Ipc\Socket\Socket;
use Kraken\Ipc\Socket\SocketInterface;
use Kraken\Ipc\Socket\SocketListener;
use Kraken\Ipc\Socket\SocketListenerInterface;
use Kraken\Test\Simulation\SimulationInterface;
use Kraken\Test\TModule;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class SocketTest extends TModule
{
    /**
     * @dataProvider endpointProvider
     */
    public function testSocketWritesAndReadsDataCorrectly($endpoint)
    {
        $sim = $this->simulate(function(SimulationInterface $sim) use($endpoint) {
            $loop = $sim->getLoop();

            $server = new SocketListener($endpoint, $loop);
            $server->start();
            $server->on('connect', function(SocketListenerInterface $server, SocketInterface $conn) use($sim) {
                $conn->on('data', function(SocketInterface $conn, $data) use($server, $sim) {
                    $sim->expect('data', $data);
                    $conn->write('secret answer!');
                    $server->close();
                });
            });
            $server->on('error', $this->expectCallableNever());
            $server->on('close', function() use($sim) {
                $sim->expect('close');
            });
            $server->start();

            $client = new Socket($endpoint, $loop);
            $client->on('data', function(SocketInterface $conn, $data) use($loop, $sim) {
                $sim->expect('data', $data);
                $conn->close();
                $sim->done();
            });
            $client->on('error', $this->expectCallableNever());
            $client->on('close', function() use($sim) {
                $sim->expect('close');
            });

            $client->write('secret question!');
        });
        $sim = $sim->expect([
            [ 'data', 'secret question!' ],
            [ 'close' ],
            [ 'data', 'secret answer!' ],
            [ 'close' ]
        ]);
    }

    /**
     * @dataProvider endpointSSLProvider
     */
    public function testSocketWritesAndReadsDataCorrectly_WithSSLConfiguration($endpoint)
    {
        $sim = $this->simulate(function(SimulationInterface $sim) use($endpoint) {
            $loop = $sim->getLoop();

            $serverConfig = [
                'ssl' => true,
                'ssl_cert' => __DIR__ . '/_Data/_ssl_cert.pem',
                'ssl_key' => __DIR__ . '/_Data/_ssl_key.pem',
                'ssl_secret' => 'secret',
            ];

            $server = new SocketListener($endpoint, $loop, $serverConfig);
            $server->on('connect', function(SocketListenerInterface $server, SocketInterface $conn) use($sim) {
                $conn->on('data', function(SocketInterface $conn, $data) use($server, $sim) {
                    $sim->expect('data', $data);
                    $conn->write('secret answer!');
                    $server->close();
                });
            });
            $server->on('error', $this->expectCallableNever());
            $server->on('close', function() use($sim) {
                $sim->expect('close');
            });
            $server->start();

            $clientConfig = [
                'ssl' => true,
            ];
            $client = new Socket($endpoint, $loop, $clientConfig);
            $client->on('data', function(SocketInterface $conn, $data) use($loop, $sim) {
                $sim->expect('data', $data);
                $conn->close();
                $sim->done();
            });
            $client->on('error', $this->expectCallableNever());
            $client->on('close', function() use($sim) {
                $sim->expect('close');
            });

            $client->write('secret question!');
        });
        $sim = $sim->expect([
            [ 'data', 'secret question!' ],
            [ 'close' ],
            [ 'data', 'secret answer!' ],
            [ 'close' ]
        ]);
    }

    /**
     * @return string[][]
     */
    public function endpointProvider()
    {
        return [
            [ 'tcp://127.0.0.1:2080' ],
            [ 'unix://mysocket.sock' ]
        ];
    }

    /**
     * @return string[][]
     */
    public function endpointSSLProvider()
    {
        return [
            [ 'tcp://127.0.0.1:2080' ],
        ];
    }
}
