<?php

namespace Kraken\Runtime\Command\Container;

use Kraken\Promise\Promise;
use Kraken\Runtime\Command\Command;
use Kraken\Runtime\Command\CommandInterface;

class ContainerDestroyCommand extends Command implements CommandInterface
{
    /**
     * @override
     * @inheritDoc
     */
    protected function command($params = [])
    {
        $runtime = $this->runtime;
        $promise = new Promise();

        $runtime->once('destroy', function() use($promise) {
            $promise->resolve('Runtime has been destroyed');
        });

        $runtime
            ->destroy()
            ->then(
                null,
                function($ex) use($promise) {
                    $promise->reject($ex);
                },
                function($ex) use($promise) {
                    $promise->cancel($ex);
                }
            );

        return $promise;
    }
}
