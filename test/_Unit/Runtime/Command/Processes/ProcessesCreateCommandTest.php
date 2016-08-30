<?php

namespace Kraken\_Unit\Runtime\Command\Processes;

use Kraken\_Unit\Runtime\Command\_T\TCommand;
use Kraken\Runtime\Command\Processes\ProcessesCreateCommand;
use Kraken\Throwable\Exception\Runtime\RejectionException;
use StdClass;

class ProcessesCreateCommandTest extends TCommand
{
    /**
     * @var string
     */
    protected $class = ProcessesCreateCommand::class;

    /**
     *
     */
    public function testApiCommand_InvokesProperAction()
    {
        $defs   = [ 'alias1' => 'name1', 'alias2' => 'name2' ];
        $flags  = 0;
        $result = new StdClass;

        $command = $this->createCommand();
        $manager = $this->createManager();
        $manager
            ->expects($this->once())
            ->method('createProcesses')
            ->with($defs, $flags)
            ->will($this->returnValue($result));

        $this->assertSame(
            $result,
            $this->callProtectedMethod(
                $command, 'command', [[ 'definitions' => $defs, 'flags' => $flags ]]
            )
        );
    }

    /**
     *
     */
    public function testApiCommand_ThrowsException_WhenContextParamDefinitionsDoesNotExist()
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

        $this->callProtectedMethod($command, 'command', [[ 'definitions' => [] ]]);
    }
}
