<?php

namespace Kraken\Channel;

use Kraken\Channel\Protocol\Protocol;
use Kraken\Channel\Protocol\ProtocolInterface;
use Kraken\Channel\Router\RouterCompositeInterface;
use Kraken\Event\BaseEventEmitter;
use Kraken\Event\EventListener;
use Kraken\Loop\LoopAwareTrait;
use Kraken\Loop\LoopInterface;
use Dazzle\Throwable\Exception\Logic\ResourceOccupiedException;
use Dazzle\Throwable\Exception\Logic\ResourceUndefinedException;
use Kraken\Util\Support\GeneratorSupport;
use Kraken\Util\Support\TimeSupport;

class ChannelComposite extends BaseEventEmitter implements ChannelCompositeInterface
{
    use LoopAwareTrait;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var ChannelInterface[]|ChannelCompositeInterface[]
     */
    protected $buses;

    /**
     * @var RouterCompositeInterface
     */
    protected $router;

    /**
     * @var EventListener[][]
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
     * @param ChannelInterface[]|ChannelCompositeInterface[] $buses
     * @param RouterCompositeInterface $router
     * @param LoopInterface $loop
     */
    public function __construct($name, $buses = [], RouterCompositeInterface $router, LoopInterface $loop)
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

    /**
     * @override
     * @inheritDoc
     */
    public function existsBus($name)
    {
        return isset($this->buses[$name]);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getBus($name)
    {
        if (!isset($this->buses[$name]))
        {
            throw new ResourceUndefinedException(sprintf("Channel [%s] has no registered bus [$name].", $this->getName()));
        }

        return $this->buses[$name];
    }

    /**
     * @override
     * @inheritDoc
     */
    public function setBus($name, $channel)
    {
        if (isset($this->buses[$name]))
        {
            throw new ResourceOccupiedException(sprintf("Channel [%s] has already registered bus [$name].", $this->getName()));
        }

        $this->buses[$name] = $channel;
        $this->events[$name] = $channel->copyEvents($this, [ 'connect', 'disconnect' ]);
        $this->events[$name][] = $channel->on('input', [ $this, 'handleReceive' ]);

        return $this;
    }

    /**
     * @override
     * @inheritDoc
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
     * @override
     * @inheritDoc
     */
    public function getBuses()
    {
        return $this->buses;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getModel()
    {
        return null;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getRouter()
    {
        return $this->router;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getInput()
    {
        return $this->router->getBus('input');
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getOutput()
    {
        return $this->router->getBus('output');
    }

    /**
     * @override
     * @inheritDoc
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

        return new Protocol('', $this->genID(), '', '', $message);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function onStart(callable $handler)
    {
        return $this->on('start', $handler);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function onStop(callable $handler)
    {
        return $this->on('stop', $handler);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function onConnect(callable $handler)
    {
        return $this->on('connect', $handler);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function onDisconnect(callable $handler)
    {
        return $this->on('disconnect', $handler);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function onInput(callable $handler)
    {
        return $this->on('input', $handler);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function onOutput(callable $handler)
    {
        return $this->on('output', $handler);
    }

    /**
     * @override
     * @inheritDoc
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
     * @override
     * @inheritDoc
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
     * @override
     * @inheritDoc
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
     * @override
     * @inheritDoc
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
     * @override
     * @inheritDoc
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
     * @override
     * @inheritDoc
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
     * @override
     * @inheritDoc
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
     * @override
     * @inheritDoc
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
     * @override
     * @inheritDoc
     */
    public function receive($sender, ProtocolInterface $protocol)
    {
        if ($this->getInput()->handle($sender, $protocol))
        {
            $this->emit('input', [ $sender, $protocol ]);
        }
    }

    /**
     * @override
     * @inheritDoc
     */
    public function pull($sender, ProtocolInterface $protocol)
    {
        $this->emit('input', [ $sender, $protocol ]);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function isStarted()
    {
        $statuses = [];

        foreach ($this->buses as $bus=>$channel)
        {
            $statuses[$bus] = $channel->isStarted();
        }

        return $statuses;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function isStopped()
    {
        $statuses = [];

        foreach ($this->buses as $bus=>$channel)
        {
            $statuses[$bus] = $channel->isStopped();
        }

        return $statuses;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function isConnected($name)
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
     * @override
     * @inheritDoc
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
     * @override
     * @inheritDoc
     */
    public function filterConnected($pattern)
    {
        $conns = [];

        foreach ($this->buses as $channel)
        {
            $conns = array_merge($conns, $channel->filterConnected($pattern));
        }

        return array_values(array_unique($conns));
    }

    /**
     * @param string $name
     * @param ProtocolInterface $message
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

        return $this->getOutput()->handle($name, $message, $flags);
    }

    /**
     * @param string $name
     * @param ProtocolInterface $message
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
     * @param string|ProtocolInterface $message
     * @param int $flags
     * @param callable|null $success
     * @param callable|null $failure
     * @param callable|null $cancel
     * @param float $timeout
     * @return bool[]
     */
    protected function handleSendRequest($name, $message, $flags = Channel::MODE_DEFAULT, callable $success = null, callable $failure = null, callable $cancel = null, $timeout = 0.0)
    {
        return $this->getOutput()->handle($name, $message, $flags, $success, $failure, $cancel, $timeout);
    }

    /**
     * @param string $name
     * @param string|ProtocolInterface $message
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
     * @param ProtocolInterface $protocol
     */
    public function handleReceive($sender, ProtocolInterface $protocol)
    {
        $this->getInput()->handle($sender, $protocol);
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
     * @param ProtocolInterface|null|string|string[] $message
     * @return ProtocolInterface
     */
    protected function createMessageProtocol($message)
    {
        if (!($message instanceof ProtocolInterface))
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
