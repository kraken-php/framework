<?php

namespace Kraken\_Unit\Runtime\Command\Process;

use Kraken\_Unit\Runtime\Command\_T\TCommand;
use Kraken\Runtime\Command\Process\ProcessCreateCommand;
use Dazzle\Throwable\Exception\Runtime\RejectionException;
use StdClass;

class ProcessCreateCommandTest extends TCommand
{
    /**
     * @var string
     */
    protected $class = ProcessCreateCommand::class;

    /**
     *
     */
    public function testApiCommand_InvokesProperAction()
    {
        $name   = 'name';
        $alias  = 'alias';
        $flags  = 0;
        $result = new StdClass;

        $command = $this->createCommand();
        $manager = $this->createManager();
        $manager
            ->expects($this->once())
            ->method('createProcess')
            ->with($alias, $name, $flags)
            ->will($this->returnValue($result));

        $this->assertSame(
            $result,
            $this->callProtectedMethod(
                $command, 'command', [[ 'alias' => $alias, 'name' => $name, 'flags' => $flags ]]
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

        $this->callProtectedMethod($command, 'command', [[ 'name' => 'name', 'flags' => 0 ]]);
    }

    /**
     *
     */
    public function testApiCommand_ThrowsException_WhenContextParamNameDoesNotExist()
    {
        $this->setExpectedException(RejectionException::class);
        $command = $this->createCommand();

        $this->callProtectedMethod($command, 'command', [[ 'alias' => 'alias', 'flags' => 0 ]]);
    }

    /**
     *
     */
    public function testApiCommand_ThrowsException_WhenContextParamFlagsDoesNotExist()
    {
        $this->setExpectedException(RejectionException::class);
        $command = $this->createCommand();

        $this->callProtectedMethod($command, 'command', [[ 'alias' => 'alias', 'name' => 'name' ]]);
    }
}
