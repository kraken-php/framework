<?php

namespace Kraken\_Unit\Channel;

use Kraken\Channel\Model\Null\NullModel;
use Kraken\Channel\Model\Zmq\ZmqDealer;
use Kraken\Channel\ChannelModelFactory;
use Kraken\Loop\Loop;
use Kraken\Test\TUnit;

class ChannelModelFactoryTest extends TUnit
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var Loop
     */
    private $loop;

    /**
     *
     */
    public function testCaseChannelModelFactory_HasProperParams()
    {
        $factory = $this->createChannelModelFactory();

        $this->assertSame($this->name, $factory->getParam('name'));
        $this->assertSame($this->loop, $factory->getParam('loop'));
    }

    /**
     *
     */
    public function testCaseChannelModelFactory_HasProperDefinitions()
    {
        $factory = $this->createChannelModelFactory();
        $classes = [
            NullModel::class,
            ZmqDealer::class
        ];

        foreach ($classes as $class)
        {
            $this->assertTrue($factory->hasDefinition($class));
        }
    }

    /**
     * @return ChannelModelFactory
     */
    public function createChannelModelFactory()
    {
        $this->name = 'name';
        $this->loop = $this->getMock(Loop::class, [], [], '', false);

        return new ChannelModelFactory($this->name, $this->loop);
    }
}
