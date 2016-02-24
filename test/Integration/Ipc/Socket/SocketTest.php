<?php

namespace Kraken\Test\Integration\Ipc\Socket;

use Kraken\Ipc\Socket\Socket;
use Kraken\Ipc\Socket\SocketInterface;
use Kraken\Ipc\Socket\SocketListener;
use Kraken\Ipc\Socket\SocketListenerInterface;
use Kraken\Test\Integration\TestCase;
use Kraken\Test\Unit\Stub\EventCollection;

class SocketTest extends TestCase
{
    /**
     * @dataProvider endpointProvider
     */
    public function testSocketWritesAndReadsDataCorrectly($endpoint)
    {
        $this
            ->simulate(function() use($endpoint) {
                $loop = $this->loop();
                $events = $this->createEventCollection();

                $server = new SocketListener($endpoint, $loop);
                $server->on('connect', function(SocketListenerInterface $server, SocketInterface $conn) use($events) {
                    $conn->on('data', function(SocketInterface $conn, $data) use($server, $events) {
                        $events->enqueue($this->createEvent('data', $data));
                        $conn->write('secret answer!');
                        $server->close();
                    });
                });
                $server->on('error', $this->expectCallableNever());
                $server->on('close', function() use($events) {
                    $events->enqueue($this->createEvent('close'));
                });

                $client = new Socket($endpoint, $loop);
                $client->on('data', function(SocketInterface $conn, $data) use($loop, $events) {
                    $events->enqueue($this->createEvent('data', $data));
                    $conn->close();
                    $loop->stop();
                });
                $client->on('error', $this->expectCallableNever());
                $client->on('close', function() use($events) {
                    $events->enqueue($this->createEvent('close'));
                });

                $client->write('secret question!');

                return $events;
            })
            ->done(function(EventCollection $events) {
                $this->assertEvents($events, [
                    $this->createEvent('data', 'secret question!'),
                    $this->createEvent('close'),
                    $this->createEvent('data', 'secret answer!'),
                    $this->createEvent('close')
                ]);
            });
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
