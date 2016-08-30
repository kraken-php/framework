<?php

namespace Kraken\_Unit\Console\Server\Command\Project;

use Kraken\_Unit\Console\Server\_T\TCommand;
use Kraken\Config\ConfigInterface;
use Kraken\Console\Server\Command\Project\ProjectDestroyCommand;
use Kraken\Promise\PromiseFulfilled;
use Kraken\Throwable\Exception\Runtime\RejectionException;

class ProjectDestroyCommandTest extends TCommand
{
    /**
     * @var string
     */
    protected $class = ProjectDestroyCommand::class;

    /**
     *
     */
    public function testApiCommand_InvokesProperAction()
    {
        $flags = 0;

        $command = $this->createCommand();
        $manager = $this->createManager();
        $config  = $this->createConfig([ 'main.alias' => 'main' ]);

        $manager
            ->expects($this->once())
            ->method('destroyProcess')
            ->with('main', $flags)
            ->will($this->returnCallback(function() {
                return new PromiseFulfilled('ok');
            }));

        $result = $this->callProtectedMethod(
            $command, 'command', [[ 'flags' => $flags ]]
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
    public function testApiCommand_ThrowsException_WhenParamFlagsDoesNotExist()
    {
        $this->setExpectedException(RejectionException::class);
        $command = $this->createCommand();

        $this->callProtectedMethod($command, 'command', [[]]);
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
