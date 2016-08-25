<?php

namespace Kraken\_Unit\Runtime\Command\Process;

use Kraken\_Unit\Runtime\Command\_T\TCommand;
use Kraken\Runtime\Command\Process\ProcessDestroyCommand;
use Kraken\Throwable\Exception\Runtime\Execution\RejectionException;
use StdClass;

class ProcessDestroyCommandTest extends TCommand
{
    /**
     * @var string
     */
    protected $class = ProcessDestroyCommand::class;

    /**
     *
     */
    public function testApiCommand_InvokesProperAction()
    {
        $alias  = 'alias';
        $flags  = 0;
        $result = new StdClass;

        $command = $this->createCommand();
        $manager = $this->createManager();
        $manager
            ->expects($this->once())
            ->method('destroyProcess')
            ->with($alias, $flags)
            ->will($this->returnValue($result));

        $this->assertSame(
            $result,
            $this->callProtectedMethod(
                $command, 'command', [[ 'alias' => $alias, 'flags' => $flags ]]
            )
        );
    }

    /**
     *
     */
    public function testApiCommand_ThrowsException_WhenContextParamAliasDoesNotExist()
    {
        $this->setExpectedException(RejectionException::class);
        $command = $this->createCommand();

        $this->callProtectedMethod($command, 'command', [[ 'flags' => 0 ]]);
    }

    /**
     *
     */
    public function testApiCommand_ThrowsException_WhenContextParamFlagsDoesNotExist()
    {
        $this->setExpectedException(RejectionException::class);
        $command = $this->createCommand();

        $this->callProtectedMethod($command, 'command', [[ 'alias' => 'alias' ]]);
    }
}
