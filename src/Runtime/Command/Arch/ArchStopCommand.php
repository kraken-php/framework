<?php

namespace Kraken\Runtime\Command\Arch;

use Kraken\Channel\Extra\Request;
use Kraken\Runtime\Command\Command;
use Kraken\Command\CommandInterface;
use Kraken\Throwable\Exception\Runtime\Execution\RejectionException;
use Kraken\Promise\Promise;
use Kraken\Runtime\RuntimeCommand;

class ArchStopCommand extends Command implements CommandInterface
{
    /**
     * ChannelBaseInterface
     */
    protected $channel;

    /**
     *
     */
    protected function construct()
    {
        $this->channel = $this->runtime->core()->make('Kraken\Runtime\RuntimeChannelInterface');
    }

    /**
     *
     */
    protected function destruct()
    {
        unset($this->channel);
    }

    /**
     * @param mixed[] $params
     * @return mixed
     */
    protected function command($params = [])
    {
        $runtime = $this->runtime;
        $channel = $this->channel;
        $promise = $this->runtime->stop();

        return $promise
            ->then(
                function() use($runtime) {
                    return $runtime->manager()->getRuntimes();
                }
            )
            ->then(
                function($children) use($channel) {
                    $promises = [];

                    foreach ($children as $childAlias)
                    {
                        $req = new Request(
                            $channel,
                            $childAlias,
                            new RuntimeCommand('arch:stop')
                        );

                        $promises[] = $req->call();
                    }

                    return Promise::all($promises);
                }
            )
            ->then(
                function() {
                    return 'Part of architecture has been stopped.';
                },
                function() {
                    throw new RejectionException('Part of architecture could not be stopped.');
                }
            )
            ;
    }
}
