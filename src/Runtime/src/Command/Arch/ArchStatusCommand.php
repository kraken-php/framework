<?php

namespace Kraken\Runtime\Command\Arch;

use Kraken\Channel\ChannelInterface;
use Kraken\Channel\Extra\Request;
use Kraken\Runtime\Command\CommandInterface;
use Dazzle\Promise\Promise;
use Kraken\Runtime\Command\Command;
use Kraken\Runtime\RuntimeCommand;

class ArchStatusCommand extends Command implements CommandInterface
{
    /**
     * ChannelInterface
     */
    protected $channel;

    /**
     * @override
     * @inheritDoc
     */
    protected function construct()
    {
        $this->channel = $this->runtime->getCore()->make('Kraken\Runtime\Service\ChannelInternal');
    }

    /**
     * @override
     * @inheritDoc
     */
    protected function destruct()
    {
        unset($this->channel);
    }

    /**
     * @override
     * @inheritDoc
     */
    protected function command($params = [])
    {
        $runtime = $this->runtime;
        $channel = $this->channel;
        $promise = Promise::doResolve();

        return $promise
            ->then(
                function() use($runtime) {
                    return $runtime->getManager()->getRuntimes();
                }
            )
            ->then(
                function($children) use($channel) {
                    $promises = [];

                    foreach ($children as $childAlias)
                    {
                        $req = $this->createRequest(
                            $channel,
                            $childAlias,
                            new RuntimeCommand('arch:status')
                        );

                        $promises[] = $req->call();
                    }

                    return Promise::all($promises);
                }
            )
            ->then(
                function($childrenData) use($runtime) {
                    return [
                        'parent'   => $runtime->getParent(),
                        'alias'    => $runtime->getAlias(),
                        'name'     => $runtime->getName(),
                        'state'    => $runtime->getState(),
                        'children' => $childrenData
                    ];
                }
            )
        ;
    }

    /**
     * Create Request.
     *
     * @param ChannelInterface $channel
     * @param string $receiver
     * @param string $command
     * @return Request
     */
    protected function createRequest(ChannelInterface $channel, $receiver, $command)
    {
        return new Request($channel, $receiver, $command);
    }
}
