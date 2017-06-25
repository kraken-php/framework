<?php

namespace Kraken\_Unit\Console\Server\Command\Project;

use Kraken\_Unit\Console\Server\_T\TCommand;
use Kraken\Config\ConfigInterface;
use Kraken\Console\Server\Command\Project\ProjectCreateCommand;
use Kraken\Promise\PromiseFulfilled;
use Dazzle\Throwable\Exception\Runtime\RejectionException;

class ProjectCreateCommandTest extends TCommand
{
    /**
     * @var string
     */
    protected $class = ProjectCreateCommand::class;

    /**
     *
     */
    public function testApiCommand_InvokesProperAction()
    {
        $flags = 0;

        $command = $this->createCommand();
        $project = $this->createProjectManager();

        $project
            ->expects($this->once())
            ->method('createProject')
            ->with($flags)
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
}
