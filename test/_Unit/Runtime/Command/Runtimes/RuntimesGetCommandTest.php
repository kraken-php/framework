<?php

namespace Kraken\_Unit\Runtime\Command\Runtimes;

use Kraken\_Unit\Runtime\Command\_T\TCommand;
use Kraken\Runtime\Command\Runtimes\RuntimesGetCommand;
use Kraken\Throwable\Exception\Runtime\RejectionException;
use StdClass;

class RuntimesGetCommandTest extends TCommand
{
    /**
     * @var string
     */
    protected $class = RuntimesGetCommand::class;

    /**
     *
     */
    public function testApiCommand_InvokesProperAction()
    {
        $result = new StdClass;

        $command = $this->createCommand();
        $manager = $this->createManager();
        $manager
            ->expects($this->once())
            ->method('getRuntimes')
            ->will($this->returnValue($result));

        $this->assertSame(
            $result,
            $this->callProtectedMethod(
                $command, 'command', []
            )
        );
    }
}
