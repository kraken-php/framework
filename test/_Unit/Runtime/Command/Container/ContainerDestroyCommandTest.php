<?php

namespace Kraken\_Unit\Runtime\Command\Container;

use Kraken\_Unit\Runtime\Command\_T\TCommand;
use Kraken\Promise\PromiseCancelled;
use Kraken\Promise\PromiseFulfilled;
use Kraken\Promise\PromiseRejected;
use Kraken\Runtime\Command\Container\ContainerDestroyCommand;

class ContainerDestroyCommandTest extends TCommand
{
    /**
     * @var string
     */
    protected $class = ContainerDestroyCommand::class;

    /**
     *
     */
    public function testApiCommand_FulfillsOnDestroyEvent()
    {
        $command = $this->createCommand();
        $runtime = $this->createRuntime([ 'destroy' ]);
        $runtime
            ->expects($this->once())
            ->method('destroy')
            ->will($this->returnCallback(function() use($runtime) {
                $runtime->emit('destroy');
                return new PromiseFulfilled();
            }));

        $this
            ->callProtectedMethod($command, 'command', [])
            ->then(
                $this->expectCallableOnce()
            );
    }

    /**
     *
     */
    public function testApiCommand_RejectsPromise_WhenDestroyMethodRejects()
    {
        $command = $this->createCommand();
        $runtime = $this->createRuntime([ 'destroy' ]);
        $runtime
            ->expects($this->once())
            ->method('destroy')
            ->will($this->returnValue(new PromiseRejected()));

        $this
            ->callProtectedMethod($command, 'command', [])
            ->then(
                $this->expectCallableNever(),
                $this->expectCallableOnce()
            );
    }

    /**
     *
     */
    public function testApiCommand_CancelsPromise_WhenDestroyMethodCancels()
    {
        $command = $this->createCommand();
        $runtime = $this->createRuntime([ 'destroy' ]);
        $runtime
            ->expects($this->once())
            ->method('destroy')
            ->will($this->returnValue(new PromiseCancelled()));

        $this
            ->callProtectedMethod($command, 'command', [])
            ->then(
                $this->expectCallableNever(),
                $this->expectCallableNever(),
                $this->expectCallableOnce()
            );
    }
}
