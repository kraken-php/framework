<?php

namespace Kraken\_Unit\Console\Server\Command\Project;

use Kraken\_Unit\Console\Server\_T\TCommand;
use Kraken\Config\ConfigInterface;
use Kraken\Console\Server\Command\Project\ProjectStartCommand;
use Kraken\Promise\PromiseFulfilled;

class ProjectStartCommandTest extends TCommand
{
    /**
     * @var string
     */
    protected $class = ProjectStartCommand::class;

    /**
     *
     */
    public function testApiCommand_InvokesProperAction()
    {
        $command = $this->createCommand();
        $manager = $this->createManager();
        $channel = $this->createChannel();
        $config  = $this->createConfig([ 'main.alias' => 'main' ]);

        $manager
            ->expects($this->once())
            ->method('startProcess')
            ->with('main')
            ->will($this->returnCallback(function() {
                return new PromiseFulfilled('ok');
            }));

        $result = $this->callProtectedMethod(
            $command, 'command', []
        );

        $expect = $this->createCallableMock();
        $expect
            ->expects($this->once())
            ->method('__invoke');

        $result
            ->then($expect);
    }

    /**
     *
     */
    public function testProtectedApiCreateConfig_CreatesConfig()
    {
        $cmd = $this->createCommand();

        $this->assertInstanceOf(ConfigInterface::class, $this->callProtectedMethod($cmd, 'createConfig'));
    }
}
