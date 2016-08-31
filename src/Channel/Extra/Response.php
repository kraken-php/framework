<?php

namespace Kraken\Channel\Extra;

use Kraken\Promise\Promise;
use Kraken\Promise\PromiseInterface;
use Kraken\Channel\Channel;
use Kraken\Channel\ChannelInterface;
use Kraken\Channel\ChannelProtocolInterface;
use Error;
use Exception;

class Response
{
    /**
     * @var ChannelInterface
     */
    protected $channel;

    /**
     * @var ChannelProtocolInterface
     */
    protected $protocol;

    /**
     * @var string|string[]|Error|Exception
     */
    protected $message;

    /**
     * @var mixed[]
     */
    protected $params;

    /**
     * @param ChannelInterface $channel
     * @param ChannelProtocolInterface $protocol
     * @param string|string[]|Error|Exception $message
     * @param mixed[] $params
     */
    public function __construct($channel, $protocol, $message, $params = [])
    {
        $this->channel = $channel;
        $this->protocol = $protocol;
        $this->message = $message;
        $this->params = $params;
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->channel);
        unset($this->protocol);
        unset($this->message);
        unset($this->params);
    }

    /**
     * Send the prepared response.
     *
     * @return PromiseInterface
     */
    public function __invoke()
    {
        return $this->send(new Promise());
    }

    /**
     * Send the prepared response.
     *
     * @return PromiseInterface
     * @resolves mixed
     * @rejects Error|Exception|string|null
     * @cancels Error|Exception|string|null
     */
    public function call()
    {
        return $this->send(new Promise());
    }

    /**
     * Send the request using passed Promise.
     *
     * @param PromiseInterface $promise
     * @return PromiseInterface
     * @resolves mixed
     * @rejects Error|Exception|string|null
     * @cancels Error|Exception|string|null
     */
    protected function send(PromiseInterface $promise)
    {
        $pid     = $this->protocol->getPid();
        $origin  = $this->protocol->getOrigin();
        $message = $this->message;
        $channel = $this->channel;

        if ($message instanceof Error || $message instanceof Exception)
        {
            $answer = $channel
                ->createProtocol($message->getMessage())
                ->setPid($pid, true)
                ->setException(get_class($message), true)
            ;
        }
        else
        {
            $answer = $channel
                ->createProtocol($message)
                ->setPid($pid, true)
            ;
        }

        $this->channel->send(
            $origin,
            $answer,
            Channel::MODE_BUFFER_ONLINE
        );

        return $promise->resolve();
    }
}
