<?php

namespace Kraken\Channel;

use Kraken\Channel\Encoder\Encoder;
use Kraken\Channel\Encoder\EncoderInterface;
use Kraken\Channel\Extra\Request;
use Kraken\Channel\Extra\Response;
use Kraken\Channel\Protocol\Protocol;
use Kraken\Channel\Protocol\ProtocolInterface;
use Kraken\Channel\Record\RequestRecordStorage;
use Kraken\Channel\Record\ResponseRecordStorage;
use Kraken\Channel\Router\RouterCompositeInterface;
use Kraken\Event\EventEmitter;
use Kraken\Event\EventListener;
use Kraken\Loop\Timer\TimerInterface;
use Kraken\Loop\LoopAwareTrait;
use Kraken\Loop\LoopInterface;
use Dazzle\Util\Support\GeneratorSupport;
use Dazzle\Util\Support\StringSupport;
use Dazzle\Util\Support\TimeSupport;
use Dazzle\Throwable\Exception\System\TaskIncompleteException;
use Dazzle\Throwable\Exception\Logic\InstantiationException;
use Dazzle\Throwable\Exception\LogicException;
use Dazzle\Throwable\Exception;
use Dazzle\Throwable\ThrowableProxy;

class Channel extends EventEmitter implements ChannelInterface
{
    use LoopAwareTrait;
    use RequestRecordStorage;
    use ResponseRecordStorage;

    /**
     * @var string
     */
    const TYPE_SND = 'SND';

    /**
     * @var string
     */
    const TYPE_REQ = 'REQ';

    /**
     * @var int
     */
    const BINDER = 1;

    /**
     * @var int
     */
    const CONNECTOR = 2;

    /**
     * @var int
     */
    const MODE_DEFAULT = 0;

    /**
     * @var int
     */
    const MODE_STANDARD = 0;

    /**
     * @var int
     */
    const MODE_BUFFER_ONLINE = 1;

    /**
     * @var int
     */
    const MODE_BUFFER_OFFLINE = 2;

    /**
     * @var int
     */
    const MODE_BUFFER = 3;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var ChannelModelInterface
     */
    protected $model;

    /**
     * @var RouterCompositeInterface
     */
    protected $router;

    /**
     * @var EncoderInterface
     */
    protected $encoder;

    /**
     * @var EventListener[]
     */
    protected $handlers;

    /**
     * @var string
     */
    protected $seed;

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
     * @param RouterCompositeInterface $router
     * @param EncoderInterface $encoder
     * @param LoopInterface $loop
     * @throws InstantiationException
     */
    public function __construct($name, ChannelModelInterface $model, RouterCompositeInterface $router, EncoderInterface $encoder, LoopInterface $loop)
    {
        parent::__construct($loop);

        try
        {
            $router->getBus('input');
            $router->getBus('output');
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
        $this->seed = GeneratorSupport::genId($this->name);
        $this->counter = 1e9;
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
        unset($this->handlers);
        unset($this->seed);
        unset($this->counter);
        unset($this->reqsHelperTimer);
        unset($this->repsHelperTimer);
        unset($this->reqs);
        unset($this->reps);
        unset($this->handledReps);
        unset($this->handledRepsTimeout);
        unset($this->loop);
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
        return $this->model;
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

        return new Protocol('', $this->genID(), '', $this->name, $message, '', $this->getTime());
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
        $this->model->start();
    }

    /**
     * @override
     * @inheritDoc
     */
    public function stop()
    {
        $this->model->stop();
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
     * @override
     * @inheritDoc
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
     * @override
     * @inheritDoc
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
     * @override
     * @inheritDoc
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
     * @override
     * @inheritDoc
     */
    public function receive($sender, ProtocolInterface $protocol)
    {
        if ($this->handleReceiveRequest($protocol))
        {
            return;
        }

        if ($this->handleReceiveResponse($protocol) || $this->getInput()->handle($sender, $protocol))
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
        return $this->model->isStarted();
    }

    /**
     * @override
     * @inheritDoc
     */
    public function isStopped()
    {
        return $this->model->isStopped();
    }

    /**
     * @override
     * @inheritDoc
     */
    public function isConnected($name)
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
     * @override
     * @inheritDoc
     */
    public function getConnected()
    {
        return $this->model->getConnected();
    }

    /**
     * @override
     * @inheritDoc
     */
    public function filterConnected($pattern)
    {
        return StringSupport::find($pattern, $this->getConnected());
    }

    /**
     * @override
     * @inheritDoc
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
     * @override
     * @inheritDoc
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
     * @override
     * @inheritDoc
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

        return $this->getOutput()->handle($name, $message, $flags, $success, $failure, $cancel, $timeout);
    }

    /**
     * @override
     * @inheritDoc
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
     * @override
     * @inheritDoc
     */
    public function handleReceive($sender, $multipartMessage)
    {
        $protocol = $this->encoder
            ->with(new Protocol())
            ->decode(implode('', $multipartMessage));

        if ($this->handleReceiveRequest($protocol))
        {
            return;
        }

        if ($this->handleReceiveResponse($protocol) || $this->getInput()->handle($sender, $protocol))
        {
            return;
        }
    }

    /**
     * @param ProtocolInterface
     * @return bool
     */
    protected function handleReceiveRequest(ProtocolInterface $protocol)
    {
        if ($protocol->getType() === Channel::TYPE_REQ && $protocol->getDestination() === $this->name)
        {
            $pid = $protocol->getPid();
            $timestamp = $protocol->getTimestamp();
            $now = $this->getTime();

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
     * @param ProtocolInterface $protocol
     * @return bool
     */
    protected function handleReceiveResponse(ProtocolInterface $protocol)
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
        else if ($exception === TaskIncompleteException::class)
        {
            $this->cancelRequest($pid, new ThrowableProxy([ $exception, $message ]));
        }
        else
        {
            $this->rejectRequest($pid, new ThrowableProxy([ $exception, $message ]));
        }

        return true;
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
     * @return string
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
     * @param string|string[] $message
     * @return ProtocolInterface
     */
    protected function createMessageProtocol($message)
    {
        if (!($message instanceof ProtocolInterface))
        {
            $message = $this->createProtocol($message);
        }
        else
        {
            if ($message->getPid() === '')
            {
                $message->setPid($this->genID());
            }
            if ($message->getOrigin() === '')
            {
                $message->setOrigin($this->name);
            }
            if ($message->getTimestamp() == 0)
            {
                $message->setTimestamp($this->getTime());
            }
        }

        return $message;
    }

    /**
     *
     */
    private function registerPeriodicTimers()
    {
        $this->reqsHelperTimer = $this->getLoop()->addPeriodicTimer(0.1, function() {
            $this->expireRequests();
        });
        $this->repsHelperTimer = $this->getLoop()->addPeriodicTimer(0.1, function() {
            $this->expireResponses();
            $unfinished = $this->unfinishedResponses();

            foreach ($unfinished as $response)
            {
                $protocol = new Protocol('', $response->getPid(), '', $response->getAlias(), '', '', $this->getTime());
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
        if ($this->reqsHelperTimer !== null)
        {
            $this->reqsHelperTimer->cancel();
        }

        if ($this->repsHelperTimer !== null)
        {
            $this->repsHelperTimer->cancel();
        }
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
