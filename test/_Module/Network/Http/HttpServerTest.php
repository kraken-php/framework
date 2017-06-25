<?php

namespace Kraken\_Module\Network\Http;

use Kraken\_Module\Network\_Mock\ComponentMock;
use Dazzle\Socket\Socket;
use Dazzle\Socket\SocketListener;
use Dazzle\Loop\LoopInterface;
use Kraken\Network\Http\HttpRequest;
use Kraken\Network\Http\HttpRequestInterface;
use Kraken\Network\Http\HttpServer;
use Kraken\Network\Socket\SocketServer;
use Kraken\Network\NetworkComponentInterface;
use Kraken\Network\NetworkConnectionInterface;
use Kraken\Test\Simulation\SimulationInterface;
use Kraken\Test\TModule;

class HttpServerTest extends TModule
{
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
     *
     */
    public function tearDown()
    {
        $this->listener = null;
        $this->server   = null;
        $this->http     = null;

        parent::tearDown();
    }

    /**
     *
     */
    public function testHttpServer_HandlesIncomingHTTPMessages()
    {
        $this
            ->simulate(function(SimulationInterface $sim) {
                $component = $this->createComponent();
                $loop      = $sim->getLoop();
                $http      = $this->createServer($component, $loop);
                $server    = $this->server;
                $client    = $this->createClient($loop);

                $component->on('connect', function(NetworkConnectionInterface $conn) use($sim) {
                    $sim->expect('connect');
                });
                $component->on('disconnect', function(NetworkConnectionInterface $conn) use($sim) {
                    $sim->expect('disconnect');
                });
                $component->on('message', function(NetworkConnectionInterface $conn, HttpRequestInterface $message) use($sim) {

                    $sim->assertSame('GET', $message->getMethod());
                    $sim->assertSame('/',   $message->getTarget());
                    $sim->assertSame([
                        'Host'              => [ 'localhost:10080'  ],
                        'Connection'        => [ 'keep-alive' ],
                        'Accept-Encoding'   => [ 'gzip, deflate' ],
                        'Accept-Language'   => [ 'en-US, en' ],
                        'Accept'            => [ 'text/html, application/xhtml+xml, application/xml' ],
                    ], $message->getHeaders());
                    $sim->assertSame('Hello World', (string) $message->getBody());

                    $sim->expect('message', [ $message->read() ]);
                    $sim->done();
                });

                $sim->onStart(function() use($client) {
                    $client->write($this->createHttpMessage(
                        'GET',
                        '/',
                        [
                            'Host'              => 'localhost:10080',
                            'Connection'        => 'keep-alive',
                            'Accept-Encoding'   => 'gzip, deflate',
                            'Accept-Language'   => 'en-US, en',
                            'Accept'            => 'text/html, application/xhtml+xml, application/xml',
                        ],
                        'Hello World'
                    ));
                });
                $sim->onStop(function() use($client, $server) {
                    $client->stop();
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
     * todo
     */
    public function toDotestHttpsServer_HandlesIncomingHTTPsMessages()
    {
        $this
            ->simulate(function(SimulationInterface $sim) {
                $component = $this->createComponent();
                $loop      = $sim->getLoop();
                $httpsServerConfig = [
                    'ssl' => true,
                    'local_cert'=>realpath(__DIR__.'/../../../../test/cert.pem'),
                    'passphrase'=>'secret',
                    'local_pk'=>realpath(__DIR__.'/../../../../test/key.pem'),
                ];

                $http      = $this->createServer($component, $loop, 'ssl://127.0.0.1:10080', $httpsServerConfig);
                $server    = $this->server;
                $httpsClientConfig = [
                    'ssl' => true,
                    'cafile'=>realpath(__DIR__.'/../../../../test/cert.pem'),
                ];

                $client    = $this->createClient($loop, 'ssl://127.0.0.1:10080', $httpsClientConfig);

                $component->on('connect', function(NetworkConnectionInterface $conn) use($sim) {
                    $sim->expect('connect');
                });

                $component->on('disconnect', function(NetworkConnectionInterface $conn) use($sim) {
                    $sim->expect('disconnect');
                });

                $component->on('message', function(NetworkConnectionInterface $conn, HttpRequestInterface $message) use($sim) {

                    $sim->assertSame('GET', $message->getMethod());
                    $sim->assertSame('/',   $message->getTarget());
                    $sim->assertSame([
                        'Host'              => [ 'https://localhost:10080'  ],
                        'Connection'        => [ 'keep-alive' ],
                        'Accept-Encoding'   => [ 'gzip, deflate' ],
                        'Accept-Language'   => [ 'en-US, en' ],
                        'Accept'            => [ 'text/html, application/xhtml+xml, application/xml' ],
                    ], $message->getHeaders());
                    $sim->assertSame('Hello World', (string) $message->getBody());

                    $sim->expect('message', [ $message->read() ]);
                    $sim->done();
                });

                $sim->onStart(function() use($client) {
                    $client->write($this->createHttpMessage(
                        'GET',
                        '/',
                        [
                            'Host'              => 'https://localhost:10080',
                            'Connection'        => 'keep-alive',
                            'Accept-Encoding'   => 'gzip, deflate',
                            'Accept-Language'   => 'en-US, en',
                            'Accept'            => 'text/html, application/xhtml+xml, application/xml',
                        ],
                        'Hello World'
                    ));
                });
                $sim->onStop(function() use($client, $server) {
                    $client->stop();
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
     * @param string|resource $endpoint
     * @param array $config
     * @return Socket
     */
    public function createClient(LoopInterface $loop , $endpoint = 'tcp://127.0.0.1:10080' ,$config = [])
    {
        return new Socket($endpoint, $loop ,$config);
    }

    /**
     * @param NetworkComponentInterface $component
     * @param LoopInterface $loop
     * @param string|resource $endpoint
     * @param array $config
     * @return SocketServer
     */
    public function createServer(NetworkComponentInterface $component, LoopInterface $loop , $endpoint = 'tcp://127.0.0.1:10080' ,$config = [])
    {
        $this->listener = new SocketListener($endpoint, $loop ,$config);
        $this->listener->start();
        $this->server   = new SocketServer($this->listener);
        $this->http     = new HttpServer($this->server, $component);

        return $this->http;
    }
}
