<?php

namespace Kraken\_Module\Network\Http;

use Kraken\_Module\Network\_Mock\ComponentMock;
use Kraken\Ipc\Socket\Socket;
use Kraken\Ipc\Socket\SocketListener;
use Kraken\Loop\LoopInterface;
use Kraken\Test\Simulation\Simulation;
use Kraken\Network\Http\Component\Router\HttpRouter;
use Kraken\Network\Http\HttpRequest;
use Kraken\Network\Http\HttpRequestInterface;
use Kraken\Network\Http\HttpServer;
use Kraken\Network\Socket\SocketServer;
use Kraken\Network\NetworkConnectionInterface;
use Kraken\Test\Simulation\SimulationInterface;
use Kraken\Test\TModule;

class HttpRouterTest extends TModule
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
     * @var SocketServer
     */
    private $server = null;

    /**
     * @var HttpServer
     */
    private $http = null;

    /**
     * @var HttpRouter
     */
    private $router = null;

    /**
     *
     */
    public function tearDown()
    {
        $this->listener = null;
        $this->server   = null;
        $this->http     = null;
        $this->router   = null;

        parent::tearDown();
    }

    /**
     *
     */
    public function testHttpRouter_HandlesIncomingHTTPMessages()
    {
        $this
            ->simulate(function(SimulationInterface $sim) {
                $compA  = $this->createComponent();
                $compB  = $this->createComponent();
                $loop   = $sim->getLoop();
                $router = $this->createServer($loop);
                $server = $this->server;

                $router->addRoute('/A', $compA);
                $router->addRoute('/B', $compB);

                $sim->delayOnce('pass', 2, function() use($sim) {
                    $sim->done();
                });

                $compA->on('connect', function(NetworkConnectionInterface $conn) use($sim) {
                    $sim->expect('connect A');
                });
                $compA->on('message', function(NetworkConnectionInterface $conn, HttpRequestInterface $message) use($sim) {

                    $sim->assertSame('GET', $message->getMethod());
                    $sim->assertSame('/A',  $message->getTarget());
                    $sim->assertSame([
                        'Host'              => [ 'localhost:10080'  ],
                        'Connection'        => [ 'keep-alive' ],
                        'Accept-Encoding'   => [ 'gzip, deflate' ],
                        'Accept-Language'   => [ 'en-US, en' ],
                        'Accept'            => [ 'text/html, application/xhtml+xml, application/xml' ],
                    ], $message->getHeaders());
                    $sim->assertSame('Hello A', (string) $message->getBody());

                    $sim->expect('message', [ $message->read() ]);
                    $sim->emit('pass');
                });

                $compB->on('connect', function(NetworkConnectionInterface $conn) use($sim) {
                    $sim->expect('connect B');
                });
                $compB->on('message', function(NetworkConnectionInterface $conn, HttpRequestInterface $message) use($sim) {

                    $sim->assertSame('GET', $message->getMethod());
                    $sim->assertSame('/B',  $message->getTarget());
                    $sim->assertSame([
                        'Host'              => [ 'localhost:10080'  ],
                        'Connection'        => [ 'keep-alive' ],
                        'Accept-Encoding'   => [ 'gzip, deflate' ],
                        'Accept-Language'   => [ 'en-US, en' ],
                        'Accept'            => [ 'text/html, application/xhtml+xml, application/xml' ],
                    ], $message->getHeaders());
                    $sim->assertSame('Hello B', (string) $message->getBody());

                    $sim->expect('message', [ $message->read() ]);
                    $sim->emit('pass');
                });

                $sim->onStart(function() use($loop) {
                    $client = $this->createClient($loop);
                    $client->write($this->createHttpMessage(
                        'GET',
                        '/A',
                        [
                            'Host'              => 'localhost:10080',
                            'Connection'        => 'keep-alive',
                            'Accept-Encoding'   => 'gzip, deflate',
                            'Accept-Language'   => 'en-US, en',
                            'Accept'            => 'text/html, application/xhtml+xml, application/xml',
                        ],
                        "Hello A"
                    ));
                    $client->stop();

                    $client = $this->createClient($loop);
                    $client->write($this->createHttpMessage(
                        'GET',
                        '/B',
                        [
                            'Host'              => 'localhost:10080',
                            'Connection'        => 'keep-alive',
                            'Accept-Encoding'   => 'gzip, deflate',
                            'Accept-Language'   => 'en-US, en',
                            'Accept'            => 'text/html, application/xhtml+xml, application/xml',
                        ],
                        'Hello B'
                    ));
                    $client->stop();
                });
                $sim->onStop(function() use($server) {
                    $server->stop();
                });

                unset($router);
                unset($server);
                unset($component);
                unset($loop);
            })
            ->expect([
                [ 'connect A' ],
                [ 'message', [ 'Hello A' ] ],
                [ 'connect B' ],
                [ 'message', [ 'Hello B' ] ]
            ], Simulation::EVENTS_COMPARE_RANDOMLY);
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
     * @return HttpRouter
     */
    public function createServer(LoopInterface $loop)
    {
        $this->listener = new SocketListener($this->endpoint, $loop);
        $this->server   = new SocketServer($this->listener);
        $this->http     = new HttpServer($this->server);
        $this->router   = new HttpRouter($this->http);

        return $this->router;
    }
}
