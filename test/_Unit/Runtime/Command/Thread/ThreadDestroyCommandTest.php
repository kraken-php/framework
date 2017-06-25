<?php

namespace Kraken\_Unit\Runtime\Command\Thread;

use Kraken\_Unit\Runtime\Command\_T\TCommand;
use Kraken\Runtime\Command\Thread\ThreadDestroyCommand;
use Dazzle\Throwable\Exception\Runtime\RejectionException;
use StdClass;

class ThreadDestroyCommandTest extends TCommand
{
    /**
     * @var string
     */
    protected $class = ThreadDestroyCommand::class;

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
            ->method('destroyThread')
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
