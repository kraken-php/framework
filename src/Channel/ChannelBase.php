<?php

namespace Kraken\Channel;

use Kraken\Channel\Extra\Response;
use Kraken\Channel\Request\RequestHelperTrait;
use Kraken\Channel\Response\ResponseHelperTrait;
use Kraken\Event\EventEmitter;
use Kraken\Event\EventHandler;
use Kraken\Loop\LoopInterface;
use Kraken\Loop\Timer\TimerInterface;
use Kraken\Support\GeneratorSupport;
use Kraken\Support\StringSupport;
use Kraken\Support\TimeSupport;
use Kraken\Throwable\LazyException;
use Kraken\Throwable\Exception\System\TaskIncompleteException;
use Kraken\Throwable\Exception;
use Kraken\Throwable\Exception\Logic\InstantiationException;
use Kraken\Throwable\Exception\Logic\Resource\ResourceUndefinedException;
use Kraken\Throwable\Exception\LogicException;

class ChannelBase extends EventEmitter implements ChannelBaseInterface
{
    use RequestHelperTrait;
    use ResponseHelperTrait;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var ChannelModelInterface
     */
    protected $model;

    /**
     * @var ChannelRouterCompositeInterface
     */
    protected $router;

    /**
     * @var ChannelEncoderInterface
     */
    protected $encoder;

    /**
     * @var LoopInterface
     */
    protected $loop;

    /**
     * @var EventHandler[]
     */
    protected $handlers;

    /**
     * @var string
     */
    protected $uniqid;

    /**
     * @var int
     */
    protected $counter;

    /**
     * @var TimerInterface
     */
    protected $reqsHelperTimer;

    /**
     * @var TimerInterface
     */
    protected $repsHelperTimer;

    /**
     * @param string $name
     * @param ChannelModelInterface $model
     * @param ChannelRouterCompositeInterface $router
     * @param ChannelEncoderInterface $encoder
     * @param LoopInterface $loop
     * @throws InstantiationException
     */
    public function __construct($name, ChannelModelInterface $model, ChannelRouterCompositeInterface $router, ChannelEncoderInterface $encoder, LoopInterface $loop)
    {
        parent::__construct($loop);

        try
        {
            $router->bus('input');
            $router->bus('output');
        }
        catch (Exception $ex)
        {
            throw new InstantiationException("Could not construct Kraken\\Channel\\Channel due to Router wrong configuration.");
        }

        $this->name = $name;
        $this->model = $model;
        $this->router = $router;
        $this->encoder = $encoder;
        $this->loop = $loop;
        $this->handlers = [];
        $this->uniqid = GeneratorSupport::genId($this->name);
        $this->counter = 0;
        $this->reqsHelperTimer = null;
        $this->repsHelperTimer = null;
        $this->handledRepsTimeout = 10e3;

        $this->registerEvents();
        $this->registerPeriodicTimers();
    }

    /**
     *
     */
    public function __destruct()
    {
        $this->unregisterEvents();
        $this->unregisterPeriodicTimers();

        unset($this->name);
        unset($this->model);
        unset($this->router);
        unset($this->encoder);
        unset($this->loop);
        unset($this->handlers);
        unset($this->uniqid);
        unset($this->counter);
        unset($this->reqsHelperTimer);
        unset($this->repsHelperTimer);
        unset($this->reqs);
        unset($this->reps);
        unset($this->handledReps);
        unset($this->handledRepsTimeout);

        parent::__destruct();
    }

    /**
     * @param LoopInterface $loop
     */
    public function setLoop(LoopInterface $loop)
    {
        $this->loop = $loop;
    }

    /**
     * @return LoopInterface
     */
    public function getLoop()
    {
        return $this->loop;
    }

    /**
     * @param LoopInterface|null $loop
     * @return LoopInterface
     */
    public function loop(LoopInterface $loop = null)
    {
        if ($loop !== null)
        {
            $this->loop = $loop;
        }

        return $this->loop;
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
        return $this->model;
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

        return new ChannelProtocol(
            '',
            $this->genId(),
            '',
            $this->name,
            $message,
            '',
            $this->now()
        );
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
        $this->model->start();
    }

    /**
     *
     */
    public function stop()
    {
        $this->model->stop();
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
     * @return ChannelRequest|ChannelRequest[]|null|null[]|bool|bool[]
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
        $protocol = $this->createMessageProtocol($message);
        $names = (array) $name;
        $handlers = [];

        foreach ($names as $name)
        {
            $handlers[] = $this->handleSendAsync($name, $protocol, $flags);
        }

        return !isset($handlers[0]) || isset($handlers[1]) ? $handlers : $handlers[0];
    }

    /**
     * @param string|string[] $name
     * @param string|string[]|ChannelProtocolInterface $message
     * @param int $flags
     * @return bool|bool[]
     */
    public function pushAsync($name, $message, $flags = Channel::MODE_DEFAULT)
    {
        $protocol = $this->createMessageProtocol($message);
        $names = (array) $name;
        $handlers = [];

        foreach ($names as $name)
        {
            $handlers[] = $this->handlePushAsync($name, $protocol, $flags);
        }

        return !isset($handlers[0]) || isset($handlers[1]) ? $handlers : $handlers[0];
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
        $protocol = $this->createMessageProtocol($message);
        $names = (array) $name;
        $handlers = [];

        foreach ($names as $name)
        {
            $handlers[] = $this->handleSendRequest($name, $protocol, $flags, $success, $failure, $cancel, $timeout);
        }

        return !isset($handlers[0]) || isset($handlers[1]) ? $handlers : $handlers[0];
    }

    /**
     * @param string|string[] $name
     * @param string|string[]|ChannelProtocolInterface $message
     * @param int $flags
     * @param callable|null $success
     * @param callable|null $failure
     * @param callable|null $cancel
     * @param float $timeout
     * @return ChannelRequest|ChannelRequest[]|null|null[]
     */
    public function pushRequest($name, $message, $flags = Channel::MODE_DEFAULT, callable $success = null, callable $failure = null, callable $cancel = null, $timeout = 0.0)
    {
        $protocol = $this->createMessageProtocol($message);
        $names = (array) $name;
        $handlers = [];

        foreach ($names as $name)
        {
            $handlers[] = $this->handlePushRequest($name, $protocol, $flags, $success, $failure, $cancel, $timeout);
        }

        return !isset($handlers[0]) || isset($handlers[1]) ? $handlers : $handlers[0];
    }

    /**
     * @param string $sender
     * @param ChannelProtocolInterface $protocol
     */
    public function receive($sender, ChannelProtocolInterface $protocol)
    {
        if ($this->handleReceiveRequest($protocol))
        {
            return;
        }

        if ($this->handleReceiveResponse($protocol) || $this->input()->handle($sender, $protocol))
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
        if (is_array($name))
        {
            $statuses = [];

            foreach ($name as $singleName)
            {
                $statuses[] = $this->model->isConnected($singleName);
            }

            return $statuses;
        }

        return $this->model->isConnected($name);
    }

    /**
     * @return string[]
     */
    public function getConnected()
    {
        return $this->model->getConnected();
    }

    /**
     * @param string|string[] $name
     * @return string[]
     */
    public function matchConnected($name)
    {
        return StringSupport::find($name, $this->getConnected());
    }

    /**
     * @return string
     */
    protected function genId()
    {
        return $this->uniqid . $this->nextCounter();
    }

    /**
     * @return float
     */
    protected function now()
    {
        return TimeSupport::now();
    }

    /**
     * @return string
     */
    protected function nextCounter()
    {
        if (++$this->counter > 2e9)
        {
            $this->counter = 0;
        }

        return (string) $this->counter;
    }

    /**
     * @param string|string[] $message
     * @return ChannelProtocolInterface
     */
    protected function createMessageProtocol($message)
    {
        if (!($message instanceof ChannelProtocolInterface))
        {
            $message = $this->createProtocol($message);
        }
        else
        {
            if ($message->getPid() === '')
            {
                $message->setPid($this->genId());
            }
            if ($message->getOrigin() === '')
            {
                $message->setOrigin($this->name());
            }
            if ($message->getTimestamp() == 0)
            {
                $message->setTimestamp($this->now());
            }
        }

        return $message;
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
        if ($message->getType() === '')
        {
            $message->setType(Channel::TYPE_SND);
        }
        if ($message->getDestination() === '')
        {
            $message->setDestination($name);
        }

        $status = $this->model->unicast(
            $name,
            $this->encoder->with($message)->encode(),
            $flags
        );

        if ($status)
        {
            $this->resolveOrRejectResponse($message->getPid(), $message->getException());
            $this->emit('output', [ $name, $message ]);
        }

        return $status;
    }

    /**
     * @param string|string[] $name
     * @param ChannelProtocolInterface $message
     * @param int $flags
     * @param callable|null $success
     * @param callable|null $failure
     * @param callable|null $cancel
     * @param float $timeout
     * @return bool
     */
    protected function handleSendRequest($name, $message, $flags = Channel::MODE_DEFAULT, callable $success = null, callable $failure = null, callable $cancel = null, $timeout = 0.0)
    {
        if ($message->getType() === '')
        {
            $message->setType(Channel::TYPE_REQ);
        }
        if ($message->getDestination() === '')
        {
            $message->setDestination($name);
        }

        return $this->output()->handle($name, $message, $flags, $success, $failure, $cancel, $timeout);
    }

    /**
     * @param string|string[] $name
     * @param ChannelProtocolInterface $message
     * @param int $flags
     * @param callable|null $success
     * @param callable|null $failure
     * @param callable|null $cancel
     * @param float $timeout
     * @return ChannelRequest|null
     */
    protected function handlePushRequest($name, $message, $flags = Channel::MODE_DEFAULT, callable $success = null, callable $failure = null, callable $cancel = null, $timeout = 0.0)
    {
        if ($message->getType() === '')
        {
            $message->setType(Channel::TYPE_REQ);
        }
        if ($message->getDestination() === '')
        {
            $message->setDestination($name);
        }

        $status = $this->model->unicast(
            $name,
            $this->encoder->with($message)->encode(),
            $flags
        );

        if (!$status)
        {
            if ($cancel !== null)
            {
                $cancel(new LogicException('Request could not be sent.'));
            }

            return null;
        }

        $pid = $message->getPid();
        $request = $this->createRequest($pid, $success, $failure, $cancel, $timeout);
        $this->addRequest($pid, $request);

        $this->emit('output', [ $name, $message ]);

        return $request;
    }

    /**
     * @internal
     * @param string $sender
     * @param string[] $multipartMessage
     */
    public function handleReceive($sender, $multipartMessage)
    {
        $protocol = $this->encoder
            ->with(new ChannelProtocol())
            ->decode(implode('', $multipartMessage));

        if ($this->handleReceiveRequest($protocol))
        {
            return;
        }

        if ($this->handleReceiveResponse($protocol) || $this->input()->handle($sender, $protocol))
        {
            $this->emit('input', [ $sender, $protocol ]);
        }
    }

    /**
     * @param ChannelProtocolInterface
     * @return bool
     */
    protected function handleReceiveRequest(ChannelProtocolInterface $protocol)
    {
        if ($protocol->getType() === Channel::TYPE_REQ && $protocol->getDestination() === $this->name())
        {
            $pid = $protocol->getPid();
            $timestamp = $protocol->getTimestamp();
            $now = $this->now();

            if ($timestamp <= $now || $this->existsResponse($pid))
            {
                return true;
            }

            $timestamp -= 5e3;
            $this->addResponse($pid, $this->createResponse($pid, $protocol->getOrigin(), $timestamp, $timestamp - $now));
        }

        return false;
    }

    /**
     * @param ChannelProtocolInterface $protocol
     * @return bool
     */
    protected function handleReceiveResponse(ChannelProtocolInterface $protocol)
    {
        $pid = $protocol->getPid();

        if (!$this->existsRequest($pid))
        {
            return false;
        }

        $message = $protocol->getMessage();
        $exception = $protocol->getException();

        if ($exception === '')
        {
            $this->resolveRequest($pid, $message);
        }
        else if ($exception === 'Kraken\Throwable\Exception\System\TaskIncompleteException')
        {
            $this->cancelRequest($pid, new LazyException([ $exception, $message ]));
        }
        else
        {
            $this->rejectRequest($pid, new LazyException([ $exception, $message ]));
        }

        return true;
    }

    /**
     *
     */
    private function registerPeriodicTimers()
    {
        $this->reqsHelperTimer = $this->loop->addPeriodicTimer(0.1, function() {
            $this->expireRequests();
        });
        $this->repsHelperTimer = $this->loop->addPeriodicTimer(0.1, function() {
            $this->expireResponses();
            $unfinished = $this->unfinishedResponses();

            foreach ($unfinished as $response)
            {
                $protocol = new ChannelProtocol('', $response->pid(), '', $response->alias(), '', '', $this->now());
                $response = new Response($this, $protocol, new TaskIncompleteException("Task unfinished."));
                $response->call();
            }
        });
    }

    /**
     *
     */
    private function unregisterPeriodicTimers()
    {
        $this->reqsHelperTimer->cancel();
        $this->repsHelperTimer->cancel();
    }

    /**
     *
     */
    private function registerEvents()
    {
        $this->handlers = $this->model->copyEvents($this, [ 'start', 'stop', 'connect', 'disconnect' ]);
        $this->handlers[] = $this->model->on('recv', [ $this, 'handleReceive' ]);
    }

    /**
     *
     */
    private function unregisterEvents()
    {
        foreach ($this->handlers as $handler)
        {
            $handler->cancel();
        }
    }
}
