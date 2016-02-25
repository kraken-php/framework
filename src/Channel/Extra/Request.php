<?php

namespace Kraken\Channel\Extra;

use Kraken\Channel\ChannelProtocolInterface;
use Kraken\Throwable\LazyException;
use Kraken\Throwable\Runtime\TimeoutException;
use Kraken\Throwable\System\TaskUnfinishedException;
use Kraken\Promise\Promise;
use Kraken\Promise\PromiseInterface;
use Kraken\Channel\Channel;
use Kraken\Channel\ChannelBaseInterface;
use Kraken\Support\TimeSupport;
use Error;
use Exception;

class Request
{
    /**
     * @var ChannelBaseInterface
     */
    protected $channel;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var ChannelProtocolInterface|string
     */
    protected $message;

    /**
     * @var float
     */
    protected $params;

    /**
     * @var int
     */
    protected $counter;

    /**
     * @param ChannelBaseInterface $channel
     * @param string $name
     * @param string|ChannelProtocolInterface $message
     * @param mixed[] $params
     */
    public function __construct($channel, $name, $message, $params = [])
    {
        $this->channel = $channel;
        $this->name = $name;
        $this->message = ($message instanceof ChannelProtocolInterface) ? $message : $this->channel->createProtocol($message);
        $this->params = [
            'timeout'           => isset($params['timeout']) ? $params['timeout'] : 3.0,
            'retriesLimit'      => isset($params['retriesLimit']) ? $params['retriesLimit'] : 6,
            'retriesInterval'   => isset($params['retriesInterval']) ? $params['retriesInterval'] : 2.0
        ];
        $this->counter = 1;
        $this->message->setTimestamp(
            TimeSupport::now() + ($this->params['retriesInterval'] + $this->params['timeout']) * 1e3 * $this->params['retriesLimit'],
            true
        );
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->channel);
        unset($this->name);
        unset($this->message);
        unset($this->params);
        unset($this->counter);
    }

    /**
     * @return PromiseInterface
     */
    public function __invoke()
    {
        return $this->send(new Promise());
    }

    /**
     * @return PromiseInterface
     */
    public function call()
    {
        return $this->send(new Promise());
    }

    /**
     * @param PromiseInterface $promise
     * @return PromiseInterface
     */
    protected function send(PromiseInterface $promise)
    {
        if (!$promise->isPending())
        {
            return $promise;
        }

        $this->channel->send(
            $this->name,
            $this->message,
            Channel::MODE_STANDARD,
            function($value) use($promise) {
                $promise->resolve($value);
            },
            function($ex) use($promise) {
                $promise->reject($ex);
            },
            function($ex) use($promise) {
                $this->retryOrReset($promise, $ex);
            },
            $this->params['timeout']
        );

        return $promise;
    }

    /**
     * @param PromiseInterface $promise
     */
    protected function retry(PromiseInterface $promise)
    {
        if ($this->counter >= $this->params['retriesLimit'])
        {
            $promise->reject(
                new LazyException(new TimeoutException('No response was received during specified timeout.'))
            );
        }
        else if ($this->params['retriesInterval'] > 0)
        {
            $this->counter++;
            $this->channel->getLoop()->addTimer($this->params['retriesInterval'], function() use($promise) {
                $this->send($promise);
            });
        }
        else
        {
            $this->counter++;
            $this->channel->getLoop()->afterTick(function() use($promise) {
                $this->send($promise);
            });
        }
    }

    /**
     * @param PromiseInterface $promise
     * @param Error|Exception|LazyException $ex
     */
    protected function retryOrReset(PromiseInterface $promise, $ex)
    {
        if ($ex instanceof TaskUnfinishedException)
        {
            $this->counter = 1;
            $this->channel->getLoop()->afterTick(function() use($promise) {
                $this->send($promise);
            });
            return;
        }

        $this->retry($promise);
    }
}
