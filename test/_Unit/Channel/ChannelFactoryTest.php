<?php

namespace Kraken\_Unit\Channel;

use Kraken\Channel\Channel;
use Kraken\Channel\ChannelComposite;
use Kraken\Channel\ChannelEncoder;
use Kraken\Channel\ChannelRouterComposite;
use Kraken\Channel\ChannelFactory;
use Kraken\Channel\ChannelModelFactory;
use Kraken\Loop\Loop;
use Kraken\Test\TUnit;

class ChannelFactoryTest extends TUnit
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var ChannelModelFactory
     */
    private $model;

    /**
     * @var Loop
     */
    private $loop;

    /**
     *
     */
    public function testCaseChannelFactory_HasProperParams()
    {
        $factory = $this->createChannelFactory();

        $this->assertSame($this->name, $factory->getParam('name'));
        $this->assertInstanceOf(ChannelEncoder::class, $factory->getParam('encoder'));
        $this->assertInstanceOf(ChannelRouterComposite::class, $factory->getParam('router'));
        $this->assertSame($this->loop, $factory->getParam('loop'));
    }

    /**
     *
     */
    public function testCaseChannelFactory_HasProperDefinitions()
    {
        $factory = $this->createChannelFactory();
        $classes = [
            Channel::class,
            ChannelComposite::class
        ];

        foreach ($classes as $class)
        {
            $this->assertTrue($factory->hasDefinition($class));
        }
    }

    /**
     * @return ChannelFactory
     */
    public function createChannelFactory()
    {
        $this->name  = 'name';
        $this->model = $this->getMock(ChannelModelFactory::class, [], [], '', false);
        $this->loop  = $this->getMock(Loop::class, [], [], '', false);

        return new ChannelFactory($this->name, $this->model, $this->loop);
    }
}
