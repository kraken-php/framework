<?php

namespace Kraken\Channel;

use Kraken\Channel\Model\Null\NullModel;
use Kraken\Channel\Model\Socket\Socket;
use Kraken\Channel\Model\Zmq\ZmqDealer;
use Kraken\Loop\LoopInterface;
use Kraken\Util\Factory\Factory;

class ChannelModelFactory extends Factory implements ChannelModelFactoryInterface
{
    /**
     * @param string $name
     * @param LoopInterface $loop
     */
    public function __construct($name, LoopInterface $loop)
    {
        parent::__construct();

        $factory = $this;
        $factory
            ->bindParam('name', $name)
            ->bindParam('loop', $loop)
        ;
        $factory
            ->define(NullModel::class, function($config = []) {
                return new NullModel();
            })
            ->define(Socket::class, function($config = []) use($factory) {
                return new Socket(
                    isset($config['loop']) ? $config['loop'] : $factory->getParam('loop'),
                    array_merge(
                        [
                            'id'        => isset($config['name']) ? $config['name'] : $factory->getParam('name'),
                            'endpoint'  => '',
                            'type'      => Channel::BINDER,
                            'host'      => isset($config['name']) ? $config['name'] : $factory->getParam('name')
                        ],
                        $config
                    )
                );
            })
            ->define(ZmqDealer::class, function($config = []) use($factory) {
                return new ZmqDealer(
                    isset($config['loop']) ? $config['loop'] : $factory->getParam('loop'),
                    array_merge(
                        [
                            'id'        => isset($config['name']) ? $config['name'] : $factory->getParam('name'),
                            'endpoint'  => '',
                            'type'      => Channel::BINDER,
                            'host '     => isset($config['name']) ? $config['name'] : $factory->getParam('name')
                        ],
                        $config
                    )
                );
            })
        ;
    }
}
