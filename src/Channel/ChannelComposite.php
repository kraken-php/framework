<?php

namespace Kraken\Channel;

use Kraken\Channel\Request\Request;
use Kraken\Event\BaseEventEmitter;
use Kraken\Event\EventHandler;
use Kraken\Loop\LoopAwareTrait;
use Kraken\Loop\LoopInterface;
use Kraken\Throwable\Exception\Logic\Resource\ResourceDefinedException;
use Kraken\Throwable\Exception\Logic\Resource\ResourceUndefinedException;
use Kraken\Support\GeneratorSupport;
use Kraken\Support\TimeSupport;

class ChannelComposite extends BaseEventEmitter implements ChannelCompositeInterface
{
    use LoopAwareTrait;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var ChannelBaseInterface[]|ChannelCompositeInterface[]
     */
    protected $buses;

    /**
     * @var ChannelRouterCompositeInterface
     */
    protected $router;

    /**
     * @var EventHandler[][]
     */
    protected $events;

    /**
     * @var string
     */
    protected $seed;

    /**
     * @var int
     */
    protected $counter;

    /**
     * @param string $name
     * @param ChannelBaseInterface[]|ChannelCompositeInterface[] $buses
     * @param ChannelRouterCompositeInterface $router
     * @param LoopInterface $loop
     */
    public function __construct($name, $buses = [], ChannelRouterCompositeInterface $router, LoopInterface $loop)
    {
        $this->name = $name;
        $this->buses = [];
        $this->router = $router;
        $this->loop = $loop;
        $this->events = [];
        $this->seed = GeneratorSupport::genId($this->name);
        $this->counter = 1e9;

        foreach ($buses as $name=>$channel)
        {
            $this->setBus($name, $channel);
        }
    }

    /**
     *
     */
    public function __destruct()
    {
        foreach ($this->buses as $name=>$channel)
        {
            $this->removeBus($name);
        }

        unset($this->name);
        unset($this->buses);
        unset($this->router);
        unset($this->events);
        unset($this->seed);
        unset($this->counter);
        unset($this->loop);
    }

    /*
     * @param string $name
     * @return bool
     */
    public function existsBus($name)
    {
        return isset($this->buses[$name]);
    }

    /**
     * @param string $name
     * @return ChannelBaseInterface|ChannelCompositeInterface
     * @throws ResourceUndefinedException
     */
    public function bus($name)
    {
        if (!isset($this->buses[$name]))
        {
            throw new ResourceUndefinedException(sprintf("Channel [%s] has no registered bus [$name].", $this->name()));
        }

        return $this->buses[$name];
    }

    /**
     * @param string $name
     * @param ChannelBaseInterface|ChannelCompositeInterface $channel
     * @return ChannelCompositeInterface
     * @throws ResourceDefinedException
     */
    public function setBus($name, $channel)
    {
        if (isset($this->buses[$name]))
        {
            throw new ResourceDefinedException(sprintf("Channel [%s] has already registered bus [$name].", $this->name()));
        }

        $this->buses[$name] = $channel;
        $this->events[$name] = $channel->copyEvents($this, [ 'connect', 'disconnect' ]);
        $this->events[$name][] = $channel->on('input', [ $this, 'handleReceive' ]);

        return $this;
    }

    /**
     * @param string $name
     * @return ChannelCompositeInterface
     */
    public function removeBus($name)
    {
        if (isset($this->buses[$name]))
        {
            foreach ($this->events[$name] as $handler)
            {
                $handler->cancel();
            }

            unset($this->buses[$name]);
            unset($this->events[$name]);
        }

        return $this;
    }

    /**
     * @return ChannelBaseInterface[]|ChannelCompositeInterface[]
     */
    public function getBuses()
    {
        return $this->buses;
    }

    /**
     * @return string
     */
    public function name()
    {
        return $this->name;
    }

    /**
     * @return ChannelModelInterface|null
     */
    public function model()
    {
        return null;
    }

    /**
     * @return ChannelRouterCompositeInterface
     */
    public function router()
    {
        return $this->router;
    }

    /**
     * @return ChannelRouterBaseInterface|ChannelRouterCompositeInterface
     * @throws ResourceUndefinedException
     */
    public function input()
    {
        return $this->router->bus('input');
    }

    /**
     * @return ChannelRouterBaseInterface|ChannelRouterCompositeInterface
     * @throws ResourceUndefinedException
     */
    public function output()
    {
        return $this->router->bus('output');
    }

    /**
     * @param string|string[]|null $message
     * @return ChannelProtocolInterface
     */
    public function createProtocol($message = null)
    {
        if ($message === null)
        {
            $message = '';
        }
        else if (!is_array($message))
        {
            $message = (string) $message;
        }

        return new ChannelProtocol('', $this->genID(), '', '', $message);
    }

    /**
     * @handle start
     * @param callable $handler
     * @return EventHandler
     */
    public function onStart(callable $handler)
    {
        return $this->on('start', $handler);
    }

    /**
     * @handle stop
     * @param callable $handler
     * @return EventHandler
     */
    public function onStop(callable $handler)
    {
        return $this->on('stop', $handler);
    }

    /**
     * @handle connect
     * @param callable $handler
     * @return EventHandler
     */
    public function onConnect(callable $handler)
    {
        return $this->on('connect', $handler);
    }

    /**
     * @handle disconnect
     * @param callable $handler
     * @return EventHandler
     */
    public function onDisconnect(callable $handler)
    {
        return $this->on('disconnect', $handler);
    }

    /**
     * @handle receive
     * @param callable $handler
     * @return EventHandler
     */
    public function onInput(callable $handler)
    {
        return $this->on('input', $handler);
    }

    /**
     * @handle send
     * @param callable $handler
     * @return EventHandler
     */
    public function onOutput(callable $handler)
    {
        return $this->on('output', $handler);
    }

    /**
     *
     */
    public function start()
    {
        foreach ($this->buses as $channel)
        {
            $channel->start();
        }

        $this->emit('start');
    }

    /**
     *
     */
    public function stop()
    {
        foreach ($this->buses as $channel)
        {
            $channel->stop();
        }

        $this->emit('stop');
    }

    /**
     * @param string|string[] $name
     * @param string|string[]|ChannelProtocolInterface $message
     * @param int $flags
     * @param callable|null $success
     * @param callable|null $failure
     * @param callable|null $cancel
     * @param float $timeout
     * @return mixed|mixed[]
     */
    public function send($name, $message, $flags = Channel::MODE_DEFAULT, callable $success = null, callable $failure = null, callable $cancel = null, $timeout = 0.0)
    {
        if ($success !== null || $failure !== null || $cancel !== null)
        {
            return $this->sendRequest($name, $message, $flags, $success, $failure, $cancel, $timeout);
        }

        return $this->sendAsync($name, $message, $flags);
    }

    /**
     * @param string|string[] $name
     * @param string|string[]|ChannelProtocolInterface $message
     * @param int $flags
     * @param callable|null $success
     * @param callable|null $failure
     * @param callable|null $cancel
     * @param float $timeout
     * @return Request|Request[]|null|null[]|bool|bool[]
     */
    public function push($name, $message, $flags = Channel::MODE_DEFAULT, callable $success = null, callable $failure = null, callable $cancel = null, $timeout = 0.0)
    {
        if ($success !== null || $failure !== null || $cancel !== null)
        {
            return $this->pushRequest($name, $message, $flags, $success, $failure, $cancel, $timeout);
        }

        return $this->pushAsync($name, $message, $flags);
    }

    /**
     * @param string|string[] $name
     * @param string|string[]|ChannelProtocolInterface $message
     * @param int $flags
     * @return bool|bool[]
     */
    public function sendAsync($name, $message, $flags = Channel::MODE_DEFAULT)
    {
        $message = $this->createMessageProtocol($message);

        $names = (array) $name;
        $status = [];
        foreach ($names as $name)
        {
            $status[] = $this->handleSendAsync($name, $message, $flags);
        }

        return !isset($status[0]) || isset($status[1]) ? $status : $status[0];
    }

    /**
     * @param string|string[] $name
     * @param string|string[]|ChannelProtocolInterface $message
     * @param int $flags
     * @return bool|bool[]
     */
    public function pushAsync($name, $message, $flags = Channel::MODE_DEFAULT)
    {
        $message = $this->createMessageProtocol($message);

        $names = (array) $name;
        $status = [];
        foreach ($names as $name)
        {
            $status[] = $this->handlePushAsync($name, $message, $flags);
        }

        return !isset($status[0]) || isset($status[1]) ? $status : $status[0];
    }

    /**
     * @param string|string[] $name
     * @param string|string[]|ChannelProtocolInterface $message
     * @param int $flags
     * @param callable|null $success
     * @param callable|null $failure
     * @param callable|null $cancel
     * @param float $timeout
     * @return bool|bool[]
     */
    public function sendRequest($name, $message, $flags = Channel::MODE_DEFAULT, callable $success = null, callable $failure = null, callable $cancel = null, $timeout = 0.0)
    {
        $message = $this->createMessageProtocol($message);

        $names = (array) $name;
        $status = [];
        foreach ($names as $name)
        {
            $status[] = $this->handleSendRequest($name, $message, $flags, $success, $failure, $cancel, $timeout);
        }

        return !isset($status[0]) || isset($status[1]) ? $status : $status[0];
    }

    /**
     * @param string|string[] $name
     * @param string|string[]|ChannelProtocolInterface $message
     * @param int $flags
     * @param callable|null $success
     * @param callable|null $failure
     * @param callable|null $cancel
     * @param float $timeout
     * @return Request|Request[]|null|null[]
     */
    public function pushRequest($name, $message, $flags = Channel::MODE_DEFAULT, callable $success = null, callable $failure = null, callable $cancel = null, $timeout = 0.0)
    {
        $message = $this->createMessageProtocol($message);

        $names = (array) $name;
        $status = [];
        foreach ($names as $name)
        {
            $status[] = $this->handlePushRequest($name, $message, $flags, $success, $failure, $cancel, $timeout);
        }

        return !isset($status[0]) || isset($status[1]) ? $status : $status[0];
    }

    /**
     * @param string $sender
     * @param ChannelProtocolInterface $protocol
     */
    public function receive($sender, ChannelProtocolInterface $protocol)
    {
        if ($this->input()->handle($sender, $protocol))
        {
            $this->emit('input', [ $sender, $protocol ]);
        }
    }

    /**
     * @param string $sender
     * @param ChannelProtocolInterface $protocol
     */
    public function pull($sender, ChannelProtocolInterface $protocol)
    {
        $this->emit('input', [ $sender, $protocol ]);
    }

    /**
     * @param string|string[]|null $name
     * @return bool|bool[]
     */
    public function isConnected($name = null)
    {
        $status = null;

        foreach ($this->buses as $channel)
        {
            $status = $this->combine(
                $status,
                $channel->isConnected($name),
                function($in, $out) {
                    return $in || $out;
                }
            );
        }

        return $status === null ? false : $status;
    }

    /**
     * @return string[]
     */
    public function getConnected()
    {
        $conns = [];

        foreach ($this->buses as $channel)
        {
            $conns = array_merge($conns, $channel->getConnected());
        }

        return array_values(array_unique($conns));
    }

    /**
     * @param string|string[] $name
     * @return string[]
     */
    public function matchConnected($name)
    {
        $conns = [];

        foreach ($this->buses as $channel)
        {
            $conns = array_merge($conns, $channel->matchConnected($name));
        }

        return array_values(array_unique($conns));
    }

    /**
     * @param string $name
     * @param ChannelProtocolInterface $message
     * @param int $flags
     * @return bool
     */
    protected function handleSendAsync($name, $message, $flags = Channel::MODE_DEFAULT)
    {
        if ($message->getType() === '')
        {
            $message->setType(Channel::TYPE_SND);
        }
        if ($message->getDestination() === '')
        {
            $message->setDestination($name);
        }

        return $this->output()->handle($name, $message, $flags);
    }

    /**
     * @param string $name
     * @param ChannelProtocolInterface $message
     * @param int $flags
     * @return bool
     */
    protected function handlePushAsync($name, $message, $flags = Channel::MODE_DEFAULT)
    {
        $statusArray = null;

        foreach ($this->buses as $channel)
        {
            $statusArray = $this->combine(
                $statusArray,
                $channel->push($name, $message, $flags),
                function($in, $out) {
                    return $in || $out;
                }
            );
        }

        $statusArray = (array) $statusArray;

        $cnt = 0;
        $len = count($statusArray);

        foreach ($statusArray as $statusElement)
        {
            if ($statusElement === true)
            {
                $cnt++;
            }
        }

        $status = ($cnt === $len);

        if ($status)
        {
            $this->emit('output', [ $name, $message ]);
        }

        return $status;
    }

    /**
     * @param string $name
     * @param string|ChannelProtocolInterface $message
     * @param int $flags
     * @param callable|null $success
     * @param callable|null $failure
     * @param callable|null $cancel
     * @param float $timeout
     * @return bool[]
     */
    protected function handleSendRequest($name, $message, $flags = Channel::MODE_DEFAULT, callable $success = null, callable $failure = null, callable $cancel = null, $timeout = 0.0)
    {
        return $this->output()->handle($name, $message, $flags, $success, $failure, $cancel, $timeout);
    }

    /**
     * @param string $name
     * @param string|ChannelProtocolInterface $message
     * @param int $flags
     * @param callable|null $success
     * @param callable|null $failure
     * @param callable|null $cancel
     * @param float $timeout
     * @return bool
     */
    protected function handlePushRequest($name, $message, $flags = Channel::MODE_DEFAULT, callable $success = null, callable $failure = null, callable $cancel = null, $timeout = 0.0)
    {
        $statusArray = null;

        foreach ($this->buses as $channel)
        {
            $statusArray = $this->combine(
                $statusArray,
                $channel->push($name, $message, $flags, $success, $failure, $cancel, $timeout),
                function($in, $out) {
                    return $in || $out;
                }
            );
        }

        $statusArray = (array) $statusArray;

        $cnt = 0;
        $len = count($statusArray);

        foreach ($statusArray as $statusElement)
        {
            if ($statusElement === true)
            {
                $cnt++;
            }
        }

        $status = ($cnt === $len);

        if ($status)
        {
            $this->emit('output', [ $name, $message ]);
        }

        return $status;
    }

    /**
     * @internal
     * @param string $sender
     * @param ChannelProtocolInterface $protocol
     */
    public function handleReceive($sender, ChannelProtocolInterface $protocol)
    {
        $this->input()->handle($sender, $protocol);
    }

    /**
     * @return string
     */
    protected function genID()
    {
        return $this->seed . $this->getNextSuffix();
    }

    /**
     * @return float
     */
    protected function getTime()
    {
        return TimeSupport::now();
    }

    /**
     * @return int
     */
    protected function getNextSuffix()
    {
        if ($this->counter > 2e9)
        {
            $this->counter = 1e9;
            $this->seed = GeneratorSupport::genId($this->name);
        }

        return (string) $this->counter++;
    }

    /**
     * @param ChannelProtocolInterface|null|string|string[] $message
     * @return ChannelProtocolInterface
     */
    protected function createMessageProtocol($message)
    {
        if (!($message instanceof ChannelProtocolInterface))
        {
            $message = $this->createProtocol($message);
        }

        if ($message->getPid() === '')
        {
            $message->setPid($this->genID());
        }
        if ($message->getTimestamp() == 0)
        {
            $message->setTimestamp($this->getTime());
        }

        return $message;
    }

    /**
     * @param mixed|mixed[]|null $in
     * @param mixed|mixed[]|null $out
     * @param callable $combinator
     * @return mixed|mixed[]
     */
    private function combine($in, $out, callable $combinator)
    {
        if ($in === null)
        {
            return $out;
        }

        if ($out === null)
        {
            return $in;
        }

        if (!is_array($in))
        {
            return $combinator($in, $out);
        }

        $result = [];

        foreach ($in as $index=>$status)
        {
            $result[$index] = $combinator($in[$index], $out[$index]);
        }

        return $result;
    }
}
