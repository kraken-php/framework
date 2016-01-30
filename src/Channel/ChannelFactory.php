<?php

namespace Kraken\Channel;

use Kraken\Loop\LoopInterface;
use Kraken\Parser\Json\JsonParser;
use Kraken\Pattern\Factory\Factory;

class ChannelFactory extends Factory implements ChannelFactoryInterface
{
    /**
     * @param string $name
     * @param ChannelModelFactoryInterface $modelFactory
     * @param LoopInterface|null $loop
     */
    public function __construct($name, ChannelModelFactoryInterface $modelFactory, LoopInterface $loop = null)
    {
        parent::__construct();

        $factory = $this;
        $factory
            ->bindParam('name', $name)
            ->bindParam('encoder', new ChannelEncoder(new JsonParser()))
            ->bindParam('router', function() {
                return new ChannelRouterComposite([
                    'input'  => new ChannelRouterBase(),
                    'output' => new ChannelRouterBase()
                ]);
            })
            ->bindParam('loop', $loop)
            ->define('Kraken\Channel\ChannelBase', function($class, $config) use($factory, $modelFactory) {
                return new ChannelBase(
                    isset($config['name']) ? $config['name'] : $factory->getParam('name'),
                    $modelFactory->create($class, [ $config ]),
                    $factory->getParam('router'),
                    $factory->getParam('encoder'),
                    isset($config['loop']) ? $config['loop'] : $factory->getParam('loop')
                );
            })
            ->define('Kraken\Channel\ChannelComposite', function($channels = [], $config = []) use($factory) {
                return new ChannelComposite(
                    isset($config['name']) ? $config['name'] : $factory->getParam('name'),
                    $channels,
                    $factory->getParam('router'),
                    isset($config['loop']) ? $config['loop'] : $factory->getParam('loop')
                );
            })
        ;
    }
}
