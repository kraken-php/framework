<?php

namespace Kraken\Channel;

use Kraken\Channel\Encoder\Encoder;
use Kraken\Channel\Encoder\EncoderInterface;
use Kraken\Channel\Router\Router;
use Kraken\Channel\Router\RouterComposite;
use Kraken\Loop\LoopInterface;
use Dazzle\Util\Factory\Factory;
use Dazzle\Util\Parser\Json\JsonParser;

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
            ->bindParam('encoder', new Encoder(new JsonParser))
            ->bindParam('router', function() {
                return new RouterComposite([
                    'input'  => new Router(),
                    'output' => new Router()
                ]);
            })
            ->bindParam('loop', $loop)
        ;
        $factory
            ->define(Channel::class, function($model, $config = []) use($factory, $modelFactory) {
                return new Channel(
                    isset($config['name']) ? $config['name'] : $factory->getParam('name'),
                    $modelFactory->create($model, [ $config ]),
                    $factory->getParam('router'),
                    $factory->getParam('encoder'),
                    isset($config['loop']) ? $config['loop'] : $factory->getParam('loop')
                );
            })
            ->define(ChannelComposite::class, function($channels = [], $config = []) use($factory) {
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
