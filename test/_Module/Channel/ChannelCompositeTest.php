<?php

namespace Kraken\_Module\Channel;

use Kraken\Channel\Encoder\Encoder;
use Kraken\Channel\Model\Zmq\ZmqDealer;
use Kraken\Channel\Router\Router;
use Kraken\Channel\Router\RouterComposite;
use Kraken\Channel\Router\RuleHandle\RuleHandler;
use Kraken\Channel\Channel;
use Kraken\Channel\ChannelInterface;
use Kraken\Channel\ChannelComposite;
use Dazzle\Loop\LoopInterface;
use Kraken\Test\Simulation\Simulation;
use Dazzle\Util\Parser\Json\JsonParser;
use Kraken\Test\Simulation\SimulationInterface;
use Kraken\Test\TModule;
use ReflectionClass;

class ChannelCompositeTest extends TModule
{
    const ALIAS_A = 'A';
    const ALIAS_B = 'B';
    const ALIAS_C = 'C';

    const MSG_1 = 'Test Message';
    const MSG_2 = 'Secret Message';
    const MSG_3 = '%#%#Slightly   More complicated message$%#@$';
    const MSG_4 = 'Extra';

    /**
     * @dataProvider modelProvider
     */
    public function testChannelBox_PushesAndReceivesData_InPairWithBuffer($data)
    {
        $this
            ->simulate(function(SimulationInterface $sim) use($data) {
                $loop = $sim->getLoop();
                $sim->delayOnce('pass', 3, function() use($sim) {
                    $sim->done();
                });

                $master = $this->createComposite($data['master'], $loop);
                $slaver = $this->createComposite($data['slave1'], $loop);

                $master->on('start', function() use($sim, $master, $slaver) {
                    $master->push(self::ALIAS_B, self::MSG_2, Channel::MODE_BUFFER);
                    $master->push(self::ALIAS_B, [ self::MSG_3, self::MSG_4 ], Channel::MODE_BUFFER);
                    $slaver->start();
                });

                $master->on('input', function($alias, $message) use($sim, $master, $slaver) {
                    $sim->expect('input', [ $alias, $message->getMessage() ]);
                    $sim->emit('pass');
                });
                $slaver->on('input', function($alias, $message) use($sim) {
                    $sim->expect('input', [ $alias, $message->getMessage() ]);
                    $sim->emit('pass');
                });

                $sim->onStart(function() use($slaver, $master) {
                    $master->start();
                });
                $sim->onStop(function() use($slaver, $master) {
                    $master->stop();
                    $slaver->stop();
                    usleep(200e3);
                });
                $slaver->push(self::ALIAS_A, self::MSG_1, Channel::MODE_BUFFER);
            })
            ->expect([
                [ 'input', [ self::ALIAS_B, self::MSG_1 ] ],
                [ 'input', [ self::ALIAS_A, self::MSG_2 ] ],
                [ 'input', [ self::ALIAS_A, [ self::MSG_3, self::MSG_4 ] ] ]
            ], Simulation::EVENTS_COMPARE_RANDOMLY);
    }

    /**
     * @dataProvider modelProvider
     */
    public function testChannelBox_PushesAndReceivesData_InPairWithOnlineBuffer($data)
    {
        $this
            ->simulate(function(SimulationInterface $sim) use($data) {
                $loop = $sim->getLoop();
                $sim->delayOnce('pass', 3, function() use($sim) {
                    $sim->done();
                });

                $master = $this->createComposite($data['master'], $loop);
                $slaver = $this->createComposite($data['slave1'], $loop);

                $master->on('start', function() use($master, $slaver) {
                    $master->push(self::ALIAS_B, self::MSG_1, Channel::MODE_BUFFER_ONLINE);
                    $master->push(self::ALIAS_B, self::MSG_2, Channel::MODE_BUFFER_ONLINE);
                    $master->push(self::ALIAS_B, [ self::MSG_3, self::MSG_4 ], Channel::MODE_BUFFER_ONLINE);
                    $slaver->start();
                });

                $slaver->on('input', function($alias, $message) use($sim) {
                    $sim->expect('input', [ $alias, $message->getMessage() ]);
                    $sim->emit('pass');
                });

                $sim->onStart(function() use($slaver, $master) {
                    $master->start();
                });
                $sim->onStop(function() use($slaver, $master) {
                    $master->stop();
                    $slaver->stop();
                    usleep(200e3);
                });
            })
            ->expect([
                [ 'input', [ self::ALIAS_A, self::MSG_1 ] ],
                [ 'input', [ self::ALIAS_A, self::MSG_2 ] ],
                [ 'input', [ self::ALIAS_A, [ self::MSG_3, self::MSG_4 ] ] ]
            ], Simulation::EVENTS_COMPARE_RANDOMLY);
    }

    /**
     * @dataProvider modelProvider
     */
    public function testChannelBox_PushesAndReceivesData_InPairWithOfflineBuffer($data)
    {
        $this
            ->simulate(function(SimulationInterface $sim) use($data) {
                $loop = $sim->getLoop();
                $sim->delayOnce('pass', 3, function() use($sim) {
                    $sim->done();
                });

                $master = $this->createComposite($data['master'], $loop);
                $slaver = $this->createComposite($data['slave1'], $loop);

                $slaver->on('input', function($alias, $message) use($sim) {
                    $sim->expect('input', [ $alias, $message->getMessage() ]);
                    $sim->emit('pass');
                });

                $sim->onStart(function() use($slaver, $master) {
                    $master->start();
                    $slaver->start();
                });
                $sim->onStop(function() use($slaver, $master) {
                    $master->stop();
                    $slaver->stop();
                    usleep(200e3);
                });

                $master->push(self::ALIAS_B, self::MSG_1, Channel::MODE_BUFFER_OFFLINE);
                $master->push(self::ALIAS_B, self::MSG_2, Channel::MODE_BUFFER_OFFLINE);
                $master->push(self::ALIAS_B, [ self::MSG_3, self::MSG_4 ], Channel::MODE_BUFFER_OFFLINE);
            })
            ->expect([
                [ 'input', [ self::ALIAS_A, self::MSG_1 ] ],
                [ 'input', [ self::ALIAS_A, self::MSG_2 ] ],
                [ 'input', [ self::ALIAS_A, [ self::MSG_3, self::MSG_4 ] ] ]
            ], Simulation::EVENTS_COMPARE_RANDOMLY);
    }

    /**
     * @dataProvider modelProvider
     */
    public function testChannelBox_PushesAndReceivesData_InPairWithoutBuffer($data)
    {
        $this
            ->simulate(function(SimulationInterface $sim) use($data) {
                $loop = $sim->getLoop();
                $sim->delayOnce('pass', 3, function() use($sim) {
                    $sim->done();
                });

                $master = $this->createComposite($data['master'], $loop);
                $slaver = $this->createComposite($data['slave1'], $loop);

                $master->on('start', function() use($master, $slaver) {
                    $master->push(self::ALIAS_B, self::MSG_2, Channel::MODE_STANDARD);
                    $slaver->start();
                });
                $slaver->on('start', function() use($master, $loop) {
                    $loop->addTimer(0.25, function() use($master) {
                        $master->push(self::ALIAS_B, self::MSG_3, Channel::MODE_STANDARD);
                    });
                });

                $slaver->on('input', function($alias, $message) use($sim) {
                    $sim->expect('input', [ $alias, $message->getMessage() ]);
                    $sim->done();
                });

                $sim->onStart(function() use($slaver, $master) {
                    $master->start();
                    $slaver->start();
                });
                $sim->onStop(function() use($slaver, $master) {
                    $master->stop();
                    $slaver->stop();
                    usleep(200e3);
                });

                $master->push(self::ALIAS_B, self::MSG_1, Channel::MODE_STANDARD);
            })
            ->expect([
                [ 'input', [ self::ALIAS_A, self::MSG_3 ] ]
            ]);
    }

    /**
     * @dataProvider modelProvider
     */
    public function testChannelBox_SendsAndReceivesData_InPairWithBuffer($data)
    {
        $this
            ->simulate(function(SimulationInterface $sim) use($data) {
                $loop = $sim->getLoop();
                $sim->delayOnce('pass', 3, function() use($sim) {
                    $sim->done();
                });

                $master = $this->createComposite($data['master'], $loop);
                $slaver = $this->createComposite($data['slave1'], $loop);

                $master->on('start', function() use($sim, $master, $slaver) {
                    $master->send(self::ALIAS_B, self::MSG_2, Channel::MODE_BUFFER);
                    $master->send(self::ALIAS_B, [ self::MSG_3, self::MSG_4 ], Channel::MODE_BUFFER);
                    $slaver->start();
                });

                $master->on('input', function($alias, $message) use($sim, $master, $slaver) {
                    $sim->expect('input', [ $alias, $message->getMessage() ]);
                    $sim->emit('pass');
                });
                $slaver->on('input', function($alias, $message) use($sim) {
                    $sim->expect('input', [ $alias, $message->getMessage() ]);
                    $sim->emit('pass');
                });

                $sim->onStart(function() use($slaver, $master) {
                    $master->start();
                });
                $sim->onStop(function() use($slaver, $master) {
                    $master->stop();
                    $slaver->stop();
                    usleep(200e3);
                });

                $slaver->send(self::ALIAS_A, self::MSG_1, Channel::MODE_BUFFER);
            })
            ->expect([
                [ 'input', [ self::ALIAS_B, self::MSG_1 ] ],
                [ 'input', [ self::ALIAS_A, self::MSG_2 ] ],
                [ 'input', [ self::ALIAS_A, [ self::MSG_3, self::MSG_4 ] ] ]
            ], Simulation::EVENTS_COMPARE_RANDOMLY);
    }

    /**
     * @dataProvider modelProvider
     */
    public function testChannelBox_SendsAndReceivesData_InPairWithOnlineBuffer($data)
    {
        $this
            ->simulate(function(SimulationInterface $sim) use($data) {
                $loop = $sim->getLoop();
                $sim->delayOnce('pass', 3, function() use($sim) {
                    $sim->done();
                });

                $master = $this->createComposite($data['master'], $loop);
                $slaver = $this->createComposite($data['slave1'], $loop);

                $master->on('start', function() use($master, $slaver) {
                    $master->send(self::ALIAS_B, self::MSG_1, Channel::MODE_BUFFER_ONLINE);
                    $master->send(self::ALIAS_B, self::MSG_2, Channel::MODE_BUFFER_ONLINE);
                    $master->send(self::ALIAS_B, [ self::MSG_3, self::MSG_4 ], Channel::MODE_BUFFER_ONLINE);
                    $slaver->start();
                });

                $slaver->on('input', function($alias, $message) use($sim) {
                    $sim->expect('input', [ $alias, $message->getMessage() ]);
                    $sim->emit('pass');
                });

                $sim->onStart(function() use($slaver, $master) {
                    $master->start();
                });
                $sim->onStop(function() use($slaver, $master) {
                    $master->stop();
                    $slaver->stop();
                    usleep(200e3);
                });
            })
            ->expect([
                [ 'input', [ self::ALIAS_A, self::MSG_1 ] ],
                [ 'input', [ self::ALIAS_A, self::MSG_2 ] ],
                [ 'input', [ self::ALIAS_A, [ self::MSG_3, self::MSG_4 ] ] ]
            ], Simulation::EVENTS_COMPARE_RANDOMLY);
    }

    /**
     * @dataProvider modelProvider
     */
    public function testChannelBox_SendsAndReceivesData_InPairWithOfflineBuffer($data)
    {
        $this
            ->simulate(function(SimulationInterface $sim) use($data) {
                $loop = $sim->getLoop();
                $sim->delayOnce('pass', 3, function() use($sim) {
                    $sim->done();
                });

                $master = $this->createComposite($data['master'], $loop);
                $slaver = $this->createComposite($data['slave1'], $loop);

                $slaver->on('input', function($alias, $message) use($sim) {
                    $sim->expect('input', [ $alias, $message->getMessage() ]);
                    $sim->emit('pass');
                });

                $sim->onStart(function() use($slaver, $master) {
                    $master->start();
                    $slaver->start();
                });
                $sim->onStop(function() use($slaver, $master) {
                    $master->stop();
                    $slaver->stop();
                    usleep(200e3);
                });

                $master->send(self::ALIAS_B, self::MSG_1, Channel::MODE_BUFFER_OFFLINE);
                $master->send(self::ALIAS_B, self::MSG_2, Channel::MODE_BUFFER_OFFLINE);
                $master->send(self::ALIAS_B, [ self::MSG_3, self::MSG_4 ], Channel::MODE_BUFFER_OFFLINE);
            })
            ->expect([
                [ 'input', [ self::ALIAS_A, self::MSG_1 ] ],
                [ 'input', [ self::ALIAS_A, self::MSG_2 ] ],
                [ 'input', [ self::ALIAS_A, [ self::MSG_3, self::MSG_4 ] ] ]
            ], Simulation::EVENTS_COMPARE_RANDOMLY);
    }

    /**
     * @dataProvider modelProvider
     */
    public function testChannelBox_SendsAndReceivesData_InPairWithoutBuffer($data)
    {
        $this
            ->simulate(function(SimulationInterface $sim) use($data) {
                $loop = $sim->getLoop();
                $sim->delayOnce('pass', 3, function() use($sim) {
                    $sim->done();
                });

                $master = $this->createComposite($data['master'], $loop);
                $slaver = $this->createComposite($data['slave1'], $loop);

                $master->on('start', function() use($master, $slaver) {
                    $master->send(self::ALIAS_B, self::MSG_2, Channel::MODE_STANDARD);
                    $slaver->start();
                });
                $slaver->on('start', function() use($master, $loop) {
                    $loop->addTimer(0.25, function() use($master) {
                        $master->send(self::ALIAS_B, self::MSG_3, Channel::MODE_STANDARD);
                    });
                });

                $slaver->on('input', function($alias, $message) use($sim) {
                    $sim->expect('input', [ $alias, $message->getMessage() ]);
                    $sim->done();
                });

                $sim->onStart(function() use($slaver, $master) {
                    $master->start();
                    $slaver->start();
                });
                $sim->onStop(function() use($slaver, $master) {
                    $master->stop();
                    $slaver->stop();
                    usleep(200e3);
                });

                $master->send(self::ALIAS_B, self::MSG_1, Channel::MODE_STANDARD);
            })
            ->expect([
                [ 'input', [ self::ALIAS_A, self::MSG_3 ] ]
            ]);
    }


    /**
     * @dataProvider modelProvider
     */
    public function testChannelBox_EmitsInputAndOutputEvents_InPair($data)
    {
        $this
            ->simulate(function(SimulationInterface $sim) use($data) {
                $loop = $sim->getLoop();
                $sim->delayOnce('pass', 2, function() use($sim) {
                    $sim->done();
                });

                $master = $this->createComposite($data['master'], $loop);
                $slaver = $this->createComposite($data['slave1'], $loop);

                $master->on('input', function($alias, $message) use($sim, $master, $slaver) {
                    $sim->expect('input', [ $alias, $message->getMessage() ]);
                    $sim->emit('pass');
                });
                $slaver->on('output', function($alias, $message) use($sim) {
                    $sim->expect('output', [ $alias, $message->getMessage() ]);
                    $sim->emit('pass');
                });

                $sim->onStart(function() use($slaver, $master) {
                    $master->start();
                    $slaver->start();
                });
                $sim->onStop(function() use($slaver, $master) {
                    $master->stop();
                    $slaver->stop();
                    usleep(200e3);
                });

                $slaver->push(self::ALIAS_A, self::MSG_1, Channel::MODE_BUFFER);
            })
            ->expect([
                [ 'output', [ self::ALIAS_A, self::MSG_1 ] ],
                [ 'input' , [ self::ALIAS_B, self::MSG_1 ] ],
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
                    'name'  => self::ALIAS_A,
                    'buses' => [
                        'bus1' => [
                            'class'  => '\Kraken\Channel\Model\Socket\Socket',
                            'config' => [
                                'id' => self::ALIAS_A,
                                'host' => [ self::ALIAS_A ],
                                'type' => ZmqDealer::BINDER,
                                'endpoint' => 'tcp://127.0.0.1:2080'
                            ]
                        ],
                        'bus2' => [
                            'class'  => '\Kraken\Channel\Model\Socket\Socket',
                            'config' => [
                                'id' => self::ALIAS_A,
                                'host' => [ self::ALIAS_A ],
                                'type' => ZmqDealer::BINDER,
                                'endpoint' => 'tcp://127.0.0.1:2081'
                            ]
                        ]
                    ]
                ],
                'slave1'  => [
                    'name'  => self::ALIAS_B,
                    'buses' => [
                        'bus1' => [
                            'class'  => '\Kraken\Channel\Model\Socket\Socket',
                            'config' => [
                                'id' => self::ALIAS_B,
                                'host' => [ self::ALIAS_A ],
                                'type' => ZmqDealer::CONNECTOR,
                                'endpoint' => 'tcp://127.0.0.1:2080'
                            ]
                        ]
                    ]
                ],
                'slave2'  => [
                    'name'  => self::ALIAS_C,
                    'buses' => [
                        'bus1' => [
                            'class'  => '\Kraken\Channel\Model\Socket\Socket',
                            'config' => [
                                'id' => self::ALIAS_C,
                                'host' => [ self::ALIAS_A ],
                                'type' => ZmqDealer::CONNECTOR,
                                'endpoint' => 'tcp://127.0.0.1:2081'
                            ]
                        ]
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
                    'name'  => self::ALIAS_A,
                    'buses' => [
                        'bus1' => [
                            'class'  => '\Kraken\Channel\Model\Zmq\ZmqDealer',
                            'config' => [
                                'id' => self::ALIAS_A,
                                'host' => [ self::ALIAS_A ],
                                'type' => ZmqDealer::BINDER,
                                'endpoint' => 'tcp://127.0.0.1:2080'
                            ]
                        ],
                        'bus2' => [
                            'class'  => '\Kraken\Channel\Model\Zmq\ZmqDealer',
                            'config' => [
                                'id' => self::ALIAS_A,
                                'host' => [ self::ALIAS_A ],
                                'type' => ZmqDealer::BINDER,
                                'endpoint' => 'tcp://127.0.0.1:2081'
                            ]
                        ]
                    ]
                ],
                'slave1'  => [
                    'name'  => self::ALIAS_B,
                    'buses' => [
                        'bus1' => [
                            'class'  => '\Kraken\Channel\Model\Zmq\ZmqDealer',
                            'config' => [
                                'id' => self::ALIAS_B,
                                'host' => [ self::ALIAS_A ],
                                'type' => ZmqDealer::CONNECTOR,
                                'endpoint' => 'tcp://127.0.0.1:2080'
                            ]
                        ]
                    ]
                ],
                'slave2'  => [
                    'name'  => self::ALIAS_C,
                    'buses' => [
                        'bus1' => [
                            'class'  => '\Kraken\Channel\Model\Zmq\ZmqDealer',
                            'config' => [
                                'id' => self::ALIAS_C,
                                'host' => [ self::ALIAS_A ],
                                'type' => ZmqDealer::CONNECTOR,
                                'endpoint' => 'tcp://127.0.0.1:2081'
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    public function createComposite($data, LoopInterface $loop)
    {
        $name  = $data['name'];
        $buses = [];

        foreach ($data['buses'] as $busName=>$bus)
        {
            $buses[$busName] = $this->createChannel($bus, $loop);
        }

        $router  = new RouterComposite([
            'input'     => $input  = new Router(),
            'output'    => $output = new Router()
        ]);

        $channel = new ChannelComposite($name, $buses, $router, $loop);

        $router = $channel->getInput();
        $router->addDefault(
            new RuleHandler(function($params) use($channel) {
                $channel->pull(
                    $params['alias'],
                    $params['protocol']
                );
            })
        );

        $router = $channel->getOutput();
        $router->addDefault(
            new RuleHandler(function($params) use($channel) {
                $channel->push(
                    $params['alias'],
                    $params['protocol'],
                    $params['flags'],
                    $params['success'],
                    $params['failure'],
                    $params['cancel'],
                    $params['timeout']
                );
            })
        );

        return $channel;
    }

    /**
     * @param mixed $data
     * @param LoopInterface $loop
     * @return ChannelInterface
     */
    public function createChannel($data, LoopInterface $loop)
    {
        $name    = $data['config']['id'];
        $model   = (new ReflectionClass($data['class']))->newInstance($loop, $data['config']);
        $router  = new RouterComposite([
            'input'     => $input  = new Router(),
            'output'    => $output = new Router()
        ]);
        $encoder = new Encoder(new JsonParser);

        $channel = new Channel($name, $model, $router, $encoder, $loop);

        $router = $channel->getInput();
        $router->addDefault(
            new RuleHandler(function($params) use($channel) {
                $channel->pull(
                    $params['alias'],
                    $params['protocol']
                );
            })
        );

        $router = $channel->getOutput();
        $router->addDefault(
            new RuleHandler(function($params) use($channel) {
                $channel->push(
                    $params['alias'],
                    $params['protocol'],
                    $params['flags'],
                    $params['success'],
                    $params['failure'],
                    $params['cancel'],
                    $params['timeout']
                );
            })
        );

        return $channel;
    }
}
