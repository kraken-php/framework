<?php

namespace Kraken\Console\Client\Command;

use Kraken\Channel\Extra\Request;
use Kraken\Channel\ChannelBaseInterface;
use Kraken\Promise\PromiseInterface;
use Kraken\Runtime\RuntimeCommand;

class CommandHandler implements CommandHandlerInterface
{
    /**
     * @var ChannelBaseInterface
     */
    protected $channel;

    /**
     * @var string
     */
    protected $receiver;

    /**
     * @param ChannelBaseInterface $channel
     * @param string $receiver
     */
    public function __construct(ChannelBaseInterface $channel, $receiver)
    {
        $this->channel  = $channel;
        $this->receiver = $receiver;
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->channel);
        unset($this->receiver);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function handle($commandParent, $commandName, $commandParams = [])
    {
        $protocol = $this->channel->createProtocol(
            new RuntimeCommand($commandName, $commandParams)
        );

        if ($commandParent !== null)
        {
            $protocol->setDestination($commandParent);
        }

        $req = new Request(
            $this->channel,
            $this->receiver,
            $protocol,
            [
                'timeout'         =>  2,
                'retriesLimit'    => 10,
                'retriesInterval' =>  1
            ]
        );

        return $req->call();
    }
}
