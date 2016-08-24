<?php

namespace Kraken\_Module\Transfer\Http;

use Kraken\_Module\Transfer\_Mock\ComponentMock;
use Kraken\Ipc\Socket\Socket;
use Kraken\Ipc\Socket\SocketListener;
use Kraken\Loop\LoopInterface;
use Kraken\Transfer\Http\HttpRequest;
use Kraken\Transfer\Http\HttpRequestInterface;
use Kraken\Transfer\TransferConnectionInterface;
use Kraken\Transfer\TransferServer;
use Kraken\Test\Simulation\SimulationInterface;
use Kraken\Test\TModule;

class TransferServerTest extends TModule
{
    /**
     * @var resource
     */
    private $endpoint = 'tcp://127.0.0.1:10080';

    /**
     * @var SocketListener
     */
    private $listener = null;

    /**
     * @var TransferServer
     */
    private $server = null;

    /**
     *
     */
    public function tearDown()
    {
        $this->listener = null;
        $this->server   = null;

        parent::tearDown();
    }

    /**
     *
     */
    public function testServer_HandlesIncomingMessages()
    {
        $this
            ->simulate(function(SimulationInterface $sim) {
                $component = $this->createComponent();
                $loop      = $sim->getLoop();
                $transfer  = $this->createServer($loop);
                $server    = $this->listener;
                $client    = $this->createClient($loop);

                $transfer->addRoute('/route', $component);

                $sim->delayOnce('pass', 2, function() use($sim) {
                    $sim->done();
                });

                $component->on('connect', function(TransferConnectionInterface $conn) use($sim) {
                    $sim->expect('connect');
                });
                $component->on('disconnect', function(TransferConnectionInterface $conn) use($sim) {
                    $sim->expect('disconnect');
                    $sim->emit('pass');
                });
                $component->on('message', function(TransferConnectionInterface $conn, HttpRequestInterface $message) use($sim) {

                    $sim->assertSame('GET',    $message->getMethod());
                    $sim->assertSame('/route', $message->getTarget());
                    $sim->assertSame([
                        'Host'              => [ 'localhost:10080'  ],
                        'Connection'        => [ 'keep-alive' ],
                        'Accept-Encoding'   => [ 'gzip, deflate' ],
                        'Accept-Language'   => [ 'en-US, en' ],
                        'Accept'            => [ 'text/html, application/xhtml+xml, application/xml' ],
                    ], $message->getHeaders());
                    $sim->assertSame('Hello World', (string) $message->getBody());

                    $sim->expect('message', [ $message->read() ]);
                    $sim->emit('pass');
                });

                $sim->onStart(function() use($sim, $client) {
                    $client->write($this->createHttpMessage(
                        'GET',
                        '/route',
                        [
                            'Host'              => 'localhost:10080',
                            'Connection'        => 'keep-alive',
                            'Accept-Encoding'   => 'gzip, deflate',
                            'Accept-Language'   => 'en-US, en',
                            'Accept'            => 'text/html, application/xhtml+xml, application/xml',
                        ],
                        'Hello World'
                    ));
                    $client->stop();
                });
                $sim->onStop(function() use($client, $server) {
                    $server->stop();
                });

                unset($http);
                unset($server);
                unset($component);
                unset($loop);
            })
            ->expect([
                [ 'connect' ],
                [ 'message', [ 'Hello World' ] ],
                [ 'disconnect' ]
            ]);
    }

    /**
     * @param string $method
     * @param string $uri
     * @param string[] $headers
     * @param string $body
     * @return string
     */
    public function createHttpMessage($method, $uri, $headers, $body)
    {
        return (new HttpRequest($method, $uri, $headers, $body))->encode();
    }

    /**
     * @return ComponentMock
     */
    public function createComponent()
    {
        return new ComponentMock();
    }

    /**
     * @param LoopInterface $loop
     * @return Socket
     */
    public function createClient(LoopInterface $loop)
    {
        return new Socket($this->endpoint, $loop);
    }

    /**
     * @param LoopInterface $loop
     * @return TransferServer
     */
    public function createServer(LoopInterface $loop)
    {
        $this->listener = new SocketListener($this->endpoint, $loop);
        $this->server = new TransferServer($this->listener);

        return $this->server;
    }
}
