<?php

namespace Kraken\_Unit\Runtime\Command\Runtime;

use Kraken\_Unit\Runtime\Command\_T\TCommand;
use Kraken\Runtime\Command\Runtime\RuntimeStartCommand;
use Kraken\Throwable\Exception\Runtime\Execution\RejectionException;
use StdClass;

class RuntimeStartCommandTest extends TCommand
{
    /**
     * @var string
     */
    protected $class = RuntimeStartCommand::class;

    /**
     *
     */
    public function testApiCommand_InvokesProperAction()
    {
        $alias  = 'alias';
        $result = new StdClass;

        $command = $this->createCommand();
        $manager = $this->createManager();
        $manager
            ->expects($this->once())
            ->method('startRuntime')
            ->with($alias)
            ->will($this->returnValue($result));

        $this->assertSame(
            $result,
            $this->callProtectedMethod(
                $command, 'command', [[ 'alias' => $alias ]]
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

        $this->callProtectedMethod($command, 'command', [[]]);
    }
}
