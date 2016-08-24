<?php

namespace Kraken\_Module\Transfer\Socket;

use Kraken\_Module\Transfer\_Mock\ComponentMock;
use Kraken\Ipc\Socket\Socket;
use Kraken\Ipc\Socket\SocketListener;
use Kraken\Loop\LoopInterface;
use Kraken\Test\Simulation\SimulationInterface;
use Kraken\Transfer\Socket\SocketServer;
use Kraken\Test\TModule;
use Kraken\Transfer\ServerComponentInterface;
use Kraken\Transfer\TransferConnectionInterface;
use Kraken\Transfer\TransferMessageInterface;

class SocketServerTest extends TModule
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
    public function testCaseSocketServer_HandlesIncomingMessages()
    {
        $this
            ->simulate(function(SimulationInterface $sim) {
                $component = $this->createComponent();
                $loop      = $sim->getLoop();
                $server    = $this->createServer($component, $loop);
                $client    = $this->createClient($loop);

                $component->on('connect', function(TransferConnectionInterface $conn) use($sim) {
                    $sim->expect('connect');
                });
                $component->on('disconnect', function(TransferConnectionInterface $conn) use($sim) {
                    $sim->expect('disconnect');
                });
                $component->on('message', function(TransferConnectionInterface $conn, TransferMessageInterface $message) use($sim) {
                    $sim->expect('message', [ $message->read() ]);
                    $sim->done();
                });

                $sim->onStart(function() use($client) {
                    $client->write('multipart');
                    $client->write('rawdata');
                });
                $sim->onStop(function() use($client, $server) {
                    $client->stop();
                    $server->stop();
                });

                unset($server);
                unset($component);
                unset($loop);
            })
            ->expect([
                [ 'connect' ],
                [ 'message', [ 'multipartrawdata' ] ],
                [ 'disconnect' ]
            ]);
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
     * @param ServerComponentInterface $component
     * @param LoopInterface $loop
     * @return SocketServer
     */
    public function createServer(ServerComponentInterface $component, LoopInterface $loop)
    {
        $this->listener = new SocketListener($this->endpoint, $loop);
        $this->server   = new SocketServer($this->listener, $component);

        return $this->server;
    }
}
