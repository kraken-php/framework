<?php

namespace Kraken\_Unit\Console\Server\Command\Project;

use Kraken\_Unit\Console\Server\_T\TCommand;
use Kraken\Config\ConfigInterface;
use Kraken\Console\Server\Command\Project\ProjectStopCommand;
use Dazzle\Promise\PromiseFulfilled;

class ProjectStopCommandTest extends TCommand
{
    /**
     * @var string
     */
    protected $class = ProjectStopCommand::class;

    /**
     *
     */
    public function testApiCommand_InvokesProperAction()
    {
        $command = $this->createCommand();
        $manager = $this->createManager();
        $project = $this->createProjectManager();

        $project
            ->expects($this->once())
            ->method('stopProject')
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
}
