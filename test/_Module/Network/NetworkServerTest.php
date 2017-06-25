<?php

namespace Kraken\_Module\Network\Http;

use Kraken\_Module\Network\_Mock\ComponentMock;
use Kraken\Ipc\Socket\Socket;
use Kraken\Ipc\Socket\SocketListener;
use Dazzle\Loop\LoopInterface;
use Kraken\Network\Http\HttpRequest;
use Kraken\Network\Http\HttpRequestInterface;
use Kraken\Network\NetworkConnectionInterface;
use Kraken\Network\NetworkServer;
use Kraken\Test\Simulation\SimulationInterface;
use Kraken\Test\TModule;

class NetworkServerTest extends TModule
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
     * @var NetworkServer
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

                $component->on('connect', function(NetworkConnectionInterface $conn) use($sim) {
                    $sim->expect('connect');
                });
                $component->on('disconnect', function(NetworkConnectionInterface $conn) use($sim) {
                    $sim->expect('disconnect');
                    $sim->emit('pass');
                });
                $component->on('message', function(NetworkConnectionInterface $conn, HttpRequestInterface $message) use($sim) {

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
     * @return NetworkServer
     */
    public function createServer(LoopInterface $loop)
    {
        $this->listener = new SocketListener($this->endpoint, $loop);
        $this->listener->start();
        $this->server = new NetworkServer($this->listener);

        return $this->server;
    }
}
