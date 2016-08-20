<?php

namespace Kraken\_Module\Channel;

use Kraken\Channel\Channel;
use Kraken\Channel\ChannelModelInterface;
use Kraken\Channel\Model\Zmq\ZmqDealer;
use Kraken\Loop\LoopInterface;
use Kraken\Test\Simulation\Simulation;
use Kraken\Test\Simulation\SimulationInterface;
use Kraken\Test\TModule;
use ReflectionClass;

/**
 * @runTestsInSeparateProcesses
 */
class ChannelModelTest extends TModule
{
    const ALIAS_A = 'A';
    const ALIAS_B = 'B';
    const ALIAS_C = 'C';

    const MSG_1 = 'Test Message';
    const MSG_2 = 'Secret Message';
    const MSG_3 = '%#%#Slightly   More complicated message$%#@$';
    const MSG_4 = 'Extra';

    public function setUp()
    {
        parent::setUp();

        usleep(100e3);
    }

    /**
     * @dataProvider modelProvider
     */
    public function testChannelModel_DoesNotAllowStartingMultipleTimesInRow($data)
    {
        $this
            ->simulate(function(SimulationInterface $sim) use($data) {
                $loop = $sim->getLoop();

                $master = $this->createModel($data['master'], $loop);

                $master->on('start', function() use($sim) {
                    $sim->expect('start');
                });
                $master->on('stop', function() use($sim) {
                    $sim->expect('stop');
                });

                $master->delayOnce('start', 2, function() use($sim) {
                    $sim->fail('Start have been called twice, but it should not be possible!');
                });

                $sim->onStart(function() use($sim, $master) {
                    $master->start();
                    $master->start();
                    $sim->done();
                });
                $sim->onStop(function() use($master) {
                    $master->stop();
                });
            })
            ->expect([
                [ 'start' ],
                [ 'stop' ]
            ]);
    }

    /**
     * @dataProvider modelProvider
     */
    public function testChannelModel_DoesNotAllowStoppingMultipleTimesInRow($data)
    {
        $this
            ->simulate(function(SimulationInterface $sim) use($data) {
                $loop = $sim->getLoop();

                $master = $this->createModel($data['master'], $loop);

                $master->on('start', function() use($sim) {
                    $sim->expect('start');
                });
                $master->on('stop', function() use($sim) {
                    $sim->expect('stop');
                });

                $master->delayOnce('stop', 2, function() use($sim) {
                    $sim->fail('Stop have been called twice, but it should not be possible!');
                });

                $sim->onStart(function() use($sim, $master) {
                    $master->start();
                    $sim->done();
                });
                $sim->onStop(function() use($master) {
                    $master->stop();
                    $master->stop();
                });
            })
            ->expect([
                [ 'start' ],
                [ 'stop' ]
            ]);
    }

    /**
     * @dataProvider modelProvider
     */
    public function testChannelModel_ConnectsEvenInWrongOrder($data)
    {
        $this
            ->simulate(function(SimulationInterface $sim) use($data) {
                $loop = $sim->getLoop();
                $sim->delayOnce('pass', 3, function() use($sim) {
                    $sim->done();
                });

                $master = $this->createModel($data['master'], $loop);
                $slaver = $this->createModel($data['slave1'], $loop);

                $slaver->on('start', function() use($master, $slaver) {
                    $master->start();
                });

                $master->on('recv', function($alias, $message) use($sim, $master, $slaver) {
                    $sim->done();
                });

                $sim->onStart(function() use($slaver, $master) {
                    $slaver->start();
                });
                $sim->onStop(function() use($slaver, $master) {
                    $master->stop();
                    $slaver->stop();
                });

                $slaver->unicast(self::ALIAS_A, self::MSG_1, Channel::MODE_BUFFER);
            });
    }

    /**
     * @dataProvider modelProvider
     */
    public function testChannelModel_SendsAndReceivesDataCorrectlyThroughUnicast_InPairWithBuffer($data)
    {
        $this
            ->simulate(function(SimulationInterface $sim) use($data) {
                $loop = $sim->getLoop();
                $sim->delayOnce('pass', 3, function() use($sim) {
                    $sim->done();
                });

                $master = $this->createModel($data['master'], $loop);
                $slaver = $this->createModel($data['slave1'], $loop);

                $master->on('start', function() use($master, $slaver) {
                    $master->unicast(self::ALIAS_B, self::MSG_2, Channel::MODE_BUFFER);
                    $master->unicast(self::ALIAS_B, [ self::MSG_3, self::MSG_4 ], Channel::MODE_BUFFER);
                    $slaver->start();
                });

                $master->on('recv', function($alias, $message) use($sim, $master, $slaver) {
                    $sim->expect('recv', [ $alias, $message ]);
                    $sim->emit('pass');
                });
                $slaver->on('recv', function($alias, $message) use($sim) {
                    $sim->expect('recv', [ $alias, $message ]);
                    $sim->emit('pass');
                });

                $sim->onStart(function() use($slaver, $master) {
                    $master->start();
                });
                $sim->onStop(function() use($slaver, $master) {
                    $master->stop();
                    $slaver->stop();
                });

                $slaver->unicast(self::ALIAS_A, self::MSG_1, Channel::MODE_BUFFER);
            })
            ->expect([
                [ 'recv', [ self::ALIAS_B, [ self::MSG_1 ] ] ],
                [ 'recv', [ self::ALIAS_A, [ self::MSG_2 ] ] ],
                [ 'recv', [ self::ALIAS_A, [ self::MSG_3, self::MSG_4 ] ] ]
            ], Simulation::EVENTS_COMPARE_RANDOMLY);
    }

    /**
     * @dataProvider modelProvider
     */
    public function testChannelModel_SendsAndReceivesDataCorrectlyThroughUnicast_InPairWithOnlineBuffer($data)
    {
        $this
            ->simulate(function(SimulationInterface $sim) use($data) {
                $loop = $sim->getLoop();
                $sim->delayOnce('pass', 3, function() use($sim) {
                    $sim->done();
                });

                $master = $this->createModel($data['master'], $loop);
                $slaver = $this->createModel($data['slave1'], $loop);

                $master->on('start', function() use($master, $slaver) {
                    $master->unicast(self::ALIAS_B, self::MSG_1, Channel::MODE_BUFFER_ONLINE);
                    $master->unicast(self::ALIAS_B, self::MSG_2, Channel::MODE_BUFFER_ONLINE);
                    $master->unicast(self::ALIAS_B, [ self::MSG_3, self::MSG_4 ], Channel::MODE_BUFFER_ONLINE);
                    $slaver->start();
                });

                $slaver->on('recv', function($alias, $message) use($sim) {
                    $sim->expect('recv', [ $alias, $message ]);
                    $sim->emit('pass');
                });

                $sim->onStart(function() use($slaver, $master) {
                    $master->start();
                });
                $sim->onStop(function() use($slaver, $master) {
                    $master->stop();
                    $slaver->stop();
                });
            })
            ->expect([
                [ 'recv', [ self::ALIAS_A, [ self::MSG_1 ] ] ],
                [ 'recv', [ self::ALIAS_A, [ self::MSG_2 ] ] ],
                [ 'recv', [ self::ALIAS_A, [ self::MSG_3, self::MSG_4 ] ] ]
            ], Simulation::EVENTS_COMPARE_RANDOMLY);
    }

    /**
     * @dataProvider modelProvider
     */
    public function testChannelModel_SendsAndReceivesDataCorrectlyThroughUnicast_InPairWithOfflineBuffer($data)
    {
        $this
            ->simulate(function(SimulationInterface $sim) use($data) {
                $loop = $sim->getLoop();
                $sim->delayOnce('pass', 3, function() use($sim) {
                    $sim->done();
                });

                $master = $this->createModel($data['master'], $loop);
                $slaver = $this->createModel($data['slave1'], $loop);

                $slaver->on('recv', function($alias, $message) use($sim) {
                    $sim->expect('recv', [ $alias, $message ]);
                    $sim->emit('pass');
                });

                $sim->onStart(function() use($slaver, $master) {
                    $master->start();
                    $slaver->start();
                });
                $sim->onStop(function() use($slaver, $master) {
                    $master->stop();
                    $slaver->stop();
                });

                $master->unicast(self::ALIAS_B, self::MSG_1, Channel::MODE_BUFFER_OFFLINE);
                $master->unicast(self::ALIAS_B, self::MSG_2, Channel::MODE_BUFFER_OFFLINE);
                $master->unicast(self::ALIAS_B, [ self::MSG_3, self::MSG_4 ], Channel::MODE_BUFFER_OFFLINE);
            })
            ->expect([
                [ 'recv', [ self::ALIAS_A, [ self::MSG_1 ] ] ],
                [ 'recv', [ self::ALIAS_A, [ self::MSG_2 ] ] ],
                [ 'recv', [ self::ALIAS_A, [ self::MSG_3, self::MSG_4 ] ] ]
            ], Simulation::EVENTS_COMPARE_RANDOMLY);
    }

    /**
     * @dataProvider modelProvider
     */
    public function testChannelModel_SendsAndReceivesDataCorrectlyThroughUnicast_InPairWithoutBuffer($data)
    {
        $this
            ->simulate(function(SimulationInterface $sim) use($data) {
                $loop = $sim->getLoop();
                $sim->delayOnce('pass', 3, function() use($sim) {
                    $sim->done();
                });

                $master = $this->createModel($data['master'], $loop);
                $slaver = $this->createModel($data['slave1'], $loop);

                $master->on('start', function() use($master, $slaver) {
                    $master->unicast(self::ALIAS_B, self::MSG_2, Channel::MODE_STANDARD);
                    $slaver->start();
                });
                $slaver->on('start', function() use($master, $loop) {
                    $loop->addTimer(0.25, function() use($master) {
                        $master->unicast(self::ALIAS_B, self::MSG_3, Channel::MODE_STANDARD);
                    });
                });

                $slaver->on('recv', function($alias, $message) use($sim) {
                    $sim->expect('recv', [ $alias, $message ]);
                    $sim->done();
                });

                $sim->onStart(function() use($master, $slaver) {
                    $master->start();
                    $slaver->start();
                });
                $sim->onStop(function() use($master, $slaver) {
                    $master->stop();
                    $slaver->stop();
                });

                $master->unicast(self::ALIAS_B, self::MSG_1, Channel::MODE_STANDARD);
            })
            ->expect([
                [ 'recv', [ self::ALIAS_A, [ self::MSG_3 ] ] ]
            ]);
    }

    /**
     * @dataProvider modelProvider
     */
    public function testChannelModel_SendsAndReceivesDataCorrectlyThroughBroadcast_InThreesomeWithoutBuffer($data)
    {
        $this
            ->simulate(function(SimulationInterface $sim) use($data) {
                $loop = $sim->getLoop();
                $sim->delayOnce('pass', 2, function() use($sim) {
                    $sim->done();
                });

                $master = $this->createModel($data['master'], $loop);
                $slave1 = $this->createModel($data['slave1'], $loop);
                $slave2 = $this->createModel($data['slave2'], $loop);

                $master->on('start', function() use($master, $slave1, $slave2) {
                    $master->broadcast([ self::MSG_2 ]);
                    $slave1->start();
                    $slave2->start();
                });
                $slave1->on('start', function() use($sim) {
                    $sim->emit('up');
                });
                $slave2->on('start', function() use($sim) {
                    $sim->emit('up');
                });

                $sim->delayOnce('up', 2, function() use($loop, $master) {
                    $loop->addTimer(0.25, function() use($master) {
                        $master->broadcast([ self::MSG_3 ]);
                    });
                });

                $slave1->on('recv', function($alias, $message) use($sim) {
                    $sim->expect('recv', [ $alias, $message ]);
                    $sim->emit('pass');
                });
                $slave2->on('recv', function($alias, $message) use($sim) {
                    $sim->expect('recv', [ $alias, $message ]);
                    $sim->emit('pass');
                });

                $sim->onStart(function() use($master) {
                    $master->start();
                });
                $sim->onStop(function() use($master, $slave1, $slave2) {
                    $master->stop();
                    $slave1->stop();
                    $slave2->stop();
                });

                $master->broadcast([ self::MSG_1 ]);
            })
            ->expect([
                [ 'recv', [ self::ALIAS_A, [ self::MSG_3 ] ] ],
                [ 'recv', [ self::ALIAS_A, [ self::MSG_3 ] ] ]
            ]);
    }

    /**
     * @dataProvider modelProvider
     */
    public function testChannelModel_EmitsConnectAndDisconnectEvents_InThreesome($data)
    {
        $this
            ->simulate(function(SimulationInterface $sim) use($data) {
                $loop = $sim->getLoop();

                $data['master']['config']['heartbeatKeepalive'] = 100;

                $master = $this->createModel($data['master'], $loop);
                $slave1 = $this->createModel($data['slave1'], $loop);
                $slave2 = $this->createModel($data['slave2'], $loop);

                $master->on('start', function() use($master, $slave1, $slave2) {
                    $master->broadcast([ self::MSG_2 ]);
                    $slave1->start();
                    $slave2->start();
                });
                $master->on('connect', function($name) use($sim) {
                    $sim->expect('connect', [ $name ]);
                });
                $master->on('disconnect', function($name) use($sim) {
                    $sim->expect('disconnect', [ $name ]);
                });

                $sim->delayOnce('down', 2, function() use($sim, $loop) {
                    $loop->addTimer(0.5, function() use($sim) {
                        $sim->done();
                    });
                });

                $slave1->on('start', function() use($sim, $slave1, $loop) {
                    $loop->addTimer(0.25, function() use($slave1) {
                        $slave1->stop();
                    });
                });
                $slave1->on('stop', function() use($sim) {
                    $sim->emit('down');
                });

                $slave2->on('start', function() use($sim, $slave2, $loop) {
                    $loop->addTimer(0.25, function() use($slave2) {
                        $slave2->stop();
                    });
                });
                $slave2->on('stop', function() use($sim) {
                    $sim->emit('down');
                });

                $sim->onStart(function() use($master) {
                    $master->start();
                });
                $sim->onStop(function() use($master, $slave1, $slave2) {
                    $master->stop();
                });
            })
            ->expect([
                [ 'connect', [ self::ALIAS_B ] ],
                [ 'connect', [ self::ALIAS_C ] ],
                [ 'disconnect', [ self::ALIAS_B ] ],
                [ 'disconnect', [ self::ALIAS_C ] ],
            ]);
    }

    /**
     * @dataProvider modelProvider
     */
    public function testChannelModel_EmitsRecvAndSendEvents_InPair($data)
    {
        $this
            ->simulate(function(SimulationInterface $sim) use($data) {
                $loop = $sim->getLoop();
                $sim->delayOnce('pass', 2, function() use($sim) {
                    $sim->done();
                });

                $master = $this->createModel($data['master'], $loop);
                $slaver = $this->createModel($data['slave1'], $loop);

                $master->on('recv', function($alias, $message) use($sim, $master, $slaver) {
                    $sim->expect('recv', [ $alias, $message ]);
                    $sim->emit('pass');
                });
                $slaver->on('send', function($alias, $message) use($sim) {
                    $sim->expect('send', [ $alias, $message ]);
                    $sim->emit('pass');
                });

                $sim->onStart(function() use($slaver, $master, $slaver) {
                    $master->start();
                    $slaver->start();
                });
                $sim->onStop(function() use($slaver, $master) {
                    $master->stop();
                    $slaver->stop();
                });

                $slaver->unicast(self::ALIAS_A, self::MSG_1, Channel::MODE_BUFFER);
            })
            ->expect([
                [ 'send', [ self::ALIAS_A, [ self::MSG_1 ] ] ],
                [ 'recv', [ self::ALIAS_B, [ self::MSG_1 ] ] ],
            ]);
    }

    /**
     * @dataProvider modelProvider
     */
    public function testChannelModel_IsAwareOfItsOwnConnection($data)
    {
        $this
            ->simulate(function(SimulationInterface $sim) use($data) {
                $loop = $sim->getLoop();

                $master = $this->createModel($data['master'], $loop);

                $master->on('start', function() use($sim, $master) {
                    if (!$master->isConnected())
                    {
                        $sim->fail('Master should be marked as connected.');
                    }
                    $master->stop();
                });
                $master->on('stop', function() use($sim, $master) {
                    if ($master->isConnected())
                    {
                        $sim->fail('Master should be marked as disconnected.');
                    }
                    $sim->done();
                });

                $sim->onStart(function() use($master) {
                    $master->start();
                });
            });
    }

    /**
     * @dataProvider modelProvider
     */
    public function testChannelModel_IsAwareOfConnectedModels_InThreesome($data)
    {
        $this
            ->simulate(function(SimulationInterface $sim) use($data) {
                $loop = $sim->getLoop();

                $data['master']['config']['heartbeatKeepalive'] = 100;

                $master = $this->createModel($data['master'], $loop);
                $slave1 = $this->createModel($data['slave1'], $loop);
                $slave2 = $this->createModel($data['slave2'], $loop);

                $master->on('start', function() use($master, $slave1, $slave2) {
                    $slave1->start();
                    $slave2->start();
                });
                $master->on('connect', function($name) use($sim, $master) {
                    $sim->expect('connect', [ $name, $master->isConnected($name) ]);
                    $sim->emit('connect');
                });
                $master->on('disconnect', function($name) use($sim, $master) {
                    $sim->expect('disconnect', [ $name, $master->isConnected($name) ]);
                    $sim->emit('disconnect');
                });

                $sim->delayOnce('connect', 2, function() use($sim, $master) {
                    $sim->expect('getConnected', [ $master->getConnected() ]);
                });
                $sim->delayOnce('disconnect', 2, function() use($sim, $master) {
                    $sim->expect('getConnected', [ $master->getConnected() ]);
                });

                $sim->delayOnce('down', 2, function() use($sim, $loop) {
                    $loop->addTimer(0.5, function() use($sim) {
                        $sim->done();
                    });
                });

                $slave1->on('start', function() use($sim, $slave1, $loop) {
                    $loop->addTimer(0.25, function() use($slave1) {
                        $slave1->stop();
                    });
                });
                $slave1->on('stop', function() use($sim) {
                    $sim->emit('down');
                });

                $slave2->on('start', function() use($sim, $slave2, $loop) {
                    $loop->addTimer(0.25, function() use($slave2) {
                        $slave2->stop();
                    });
                });
                $slave2->on('stop', function() use($sim) {
                    $sim->emit('down');
                });

                $sim->onStart(function() use($master) {
                    $master->start();
                });
                $sim->onStop(function() use($master, $slave1, $slave2) {
                    $master->stop();
                });
            })
            ->expect([
                [ 'connect', [ self::ALIAS_B, true ] ],
                [ 'connect', [ self::ALIAS_C, true ] ],
                [ 'getConnected', [ [ self::ALIAS_B, self::ALIAS_C ] ] ],
                [ 'disconnect', [ self::ALIAS_B, false ] ],
                [ 'disconnect', [ self::ALIAS_C, false ] ],
                [ 'getConnected', [ [] ] ],
            ]);
    }

    /**
     * @dataProvider modelProvider
     */
    public function testChannelModel_AllowsReconnections_InThreesome($data)
    {
        $this
            ->simulate(function(SimulationInterface $sim) use($data) {
                $loop = $sim->getLoop();

                $data['master']['config']['heartbeatKeepalive'] = 100;

                $master = $this->createModel($data['master'], $loop);
                $slave1 = $this->createModel($data['slave1'], $loop);

                $master->times('connect', 2, function($name) use($sim, $slave1) {
                    $sim->expect('connect', [ $name ]);
                    $slave1->stop();
                });

                $slave1->once('stop', function() use($sim, $slave1, $loop) {
                    $loop->addTimer(0.25, function() use($slave1) {
                        $slave1->start();
                    });
                });
                $slave1->delayOnce('stop', 2, function() use($sim) {
                    $sim->done();
                });

                $sim->onStart(function() use($master, $slave1) {
                    $master->start();
                    $slave1->start();
                });
                $sim->onStop(function() use($master, $slave1) {
                    $master->stop();
                    $slave1->stop();
                });
            })
            ->expect([
                [ 'connect', [ self::ALIAS_B ] ],
                [ 'connect', [ self::ALIAS_B ] ]
            ]);
    }

    /**
     * @return string[][]
     */
    public function modelProvider()
    {
        $channels = [];

        $channels[] = $this->getSocketData();

        if (class_exists('ZMQ'))
        {
            $channels[] = $this->getZmqData();
        }

        return $channels;
    }

    /**
     * @return mixed[][]
     */
    public function getSocketData()
    {
        return [
            [
                'master' => [
                    'class'  => '\Kraken\Channel\Model\Socket\Socket',
                    'config' => [
                        'id' => self::ALIAS_A,
                        'hosts' => self::ALIAS_A,
                        'type' => Channel::BINDER,
                        'endpoint' => 'tcp://127.0.0.1:2080'
                    ]
                ],
                'slave1' => [
                    'class'  => '\Kraken\Channel\Model\Socket\Socket',
                    'config' => [
                        'id' => self::ALIAS_B,
                        'hosts' => self::ALIAS_A,
                        'type' => Channel::CONNECTOR,
                        'endpoint' => 'tcp://127.0.0.1:2080'
                    ]
                ],
                'slave2' => [
                    'class'  => '\Kraken\Channel\Model\Socket\Socket',
                    'config' => [
                        'id' => self::ALIAS_C,
                        'hosts' => self::ALIAS_A,
                        'type' => Channel::CONNECTOR,
                        'endpoint' => 'tcp://127.0.0.1:2080'
                    ]
                ]
            ]
        ];
    }

    /**
     * @return mixed[][]
     */
    public function getZmqData()
    {
        return [
            [
                'master' => [
                    'class'  => '\Kraken\Channel\Model\Zmq\ZmqDealer',
                    'config' => [
                        'id' => self::ALIAS_A,
                        'hosts' => [ self::ALIAS_A ],
                        'type' => ZmqDealer::BINDER,
                        'endpoint' => 'tcp://127.0.0.1:2080'
                    ]
                ],
                'slave1' => [
                    'class'  => '\Kraken\Channel\Model\Zmq\ZmqDealer',
                    'config' => [
                        'id' => self::ALIAS_B,
                        'hosts' => [ self::ALIAS_A ],
                        'type' => ZmqDealer::CONNECTOR,
                        'endpoint' => 'tcp://127.0.0.1:2080'
                    ]
                ],
                'slave2' => [
                    'class'  => '\Kraken\Channel\Model\Zmq\ZmqDealer',
                    'config' => [
                        'id' => self::ALIAS_C,
                        'hosts' => [ self::ALIAS_A ],
                        'type' => ZmqDealer::CONNECTOR,
                        'endpoint' => 'tcp://127.0.0.1:2080'
                    ]
                ]
            ]
        ];
    }

    /**
     * @param mixed $data
     * @param LoopInterface $loop
     * @return ChannelModelInterface
     */
    public function createModel($data, LoopInterface $loop)
    {
        return (new ReflectionClass($data['class']))->newInstance($loop, $data['config']);
    }
}
