<?php

namespace Kraken\Runtime;

use Kraken\Event\EventEmitterInterface;
use Kraken\Core\CoreInterface;
use Kraken\Promise\Promise;
use Kraken\Supervision\SupervisorInterface;
use Kraken\Loop\Loop;
use Kraken\Loop\LoopExtendedInterface;
use Dazzle\Throwable\Exception\LogicException;
use Dazzle\Throwable\Exception\Runtime\RejectionException;
use Error;
use Exception;
use Kraken\Util\Support\HashSupport;
use ReflectionClass;

class RuntimeModel implements RuntimeModelInterface
{
    /**
     * @var int
     */
    const LOOP_STATE_STARTED = 1;

    /**
     * @var int
     */
    const LOOP_STATE_STOPPED = 2;

    /**
     * @var int
     */
    const LOOP_STATE_FAILED = 4;

    /**
     * @var string
     */
    protected $parent;

    /**
     * @var string
     */
    protected $alias;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string[]
     */
    protected $args;

    /**
     * @var int
     */
    protected $state;

    /**
     * @var CoreInterface
     */
    protected $core;

    /**
     * @var RuntimeManagerInterface
     */
    protected $manager;

    /**
     * @var LoopExtendedInterface
     */
    protected $loop;

    /**
     * @var LoopExtendedInterface
     */
    protected $loopBackup;

    /**
     * @var int
     */
    protected $loopState;

    /**
     * @var int
     */
    protected $loopNextState;

    /**
     * @var SupervisorInterface
     */
    protected $supervisor;

    /**
     * @var EventEmitterInterface
     */
    protected $eventEmitter;

    /**
     * @var string|null
     */
    private $failureHash;

    /**
     * @param string $parent
     * @param string $alias
     * @param string $name
     * @param string[] $args
     */
    public function __construct($parent, $alias, $name, $args = [])
    {
        $this->parent = ($parent === Runtime::PARENT_UNDEFINED || $parent === Runtime::RESERVED_CONSOLE_SERVER) ? null : $parent;
        $this->alias = $alias;
        $this->name = $name;
        $this->args = $args;

        $this->state = Runtime::STATE_DESTROYED;
        $this->core = null;
        $this->manager = null;
        $this->loop = null;
        $this->loopBackup = null;
        $this->loopState = self::LOOP_STATE_STOPPED;
        $this->loopNextState = self::LOOP_STATE_STOPPED;
        $this->supervisor = null;
        $this->eventEmitter = null;
        $this->failureHash = null;
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->parent);
        unset($this->alias);
        unset($this->name);
        unset($this->args);

        unset($this->state);
        unset($this->core);
        unset($this->manager);
        unset($this->loop);
        unset($this->loopBackup);
        unset($this->loopState);
        unset($this->loopNextState);
        unset($this->supervisor);
        unset($this->eventEmitter);
        unset($this->failureHash);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getType()
    {
        return Runtime::UNIT_UNDEFINED;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getAlias()
    {
        return $this->alias;
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
    public function getArgs()
    {
        return $this->args;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function setCore(CoreInterface $core = null)
    {
        $this->core = $core;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getCore()
    {
        return $this->core;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function setRuntimeManager(RuntimeManagerInterface $manager = null)
    {
        $this->manager = $manager;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getRuntimeManager()
    {
        return $this->manager;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function setLoop(LoopExtendedInterface $loop = null)
    {
        $this->loop = $loop;
        $this->loopBackup = $loop !== null ? new Loop($this->reflect($this->loop->getModel())) : $loop;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getLoop()
    {
        return $this->loop;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function setSupervisor(SupervisorInterface $supervisor = null)
    {
        $this->supervisor = $supervisor;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getSupervisor()
    {
        return $this->supervisor;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function supervisor()
    {
        return $this->supervisor;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function setEventEmitter(EventEmitterInterface $emitter = null)
    {
        $this->eventEmitter = $emitter;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getEventEmitter()
    {
        return $this->eventEmitter;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function setState($state)
    {
        $this->state = $state;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function state()
    {
        return $this->state;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getHash()
    {
        return $this->failureHash;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function isState($state)
    {
        return ($state === Runtime::STATE_FAILED && $this->failureHash !== null) || ($this->state === $state);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function isCreated()
    {
        return $this->isState(Runtime::STATE_CREATED);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function isDestroyed()
    {
        return $this->isState(Runtime::STATE_DESTROYED);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function isStarted()
    {
        return $this->isState(Runtime::STATE_STARTED);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function isStopped()
    {
        return $this->isState(Runtime::STATE_STOPPED);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function isFailed()
    {
        return $this->isState(Runtime::STATE_FAILED);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function create()
    {
        $state = $this->getState();

        if ($state === Runtime::STATE_CREATED)
        {
            return Promise::doResolve(
                'Runtime has been already created.'
            );
        }
        else if ($state !== Runtime::STATE_DESTROYED)
        {
            return Promise::doReject(
                new RejectionException("It is not possible to create runtime from state [$state].")
            );
        }

        $promise = new Promise();
        $emitter = $this->getEventEmitter();
        $emitter->emit('beforeCreate');

        $this->getLoop()->onTick(function() use($promise, $emitter) {
            $emitter->emit('create');
            $emitter->emit('afterCreate');

            $promise->resolve(
                $this
                    ->start()
                    ->then(function() {
                        return 'Runtime has been created.';
                    })
            );
        });

        $this->setState(Runtime::STATE_CREATED);
        $this->setLoopState(self::LOOP_STATE_STARTED);
        $this->startLoop();

        return $promise;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function destroy()
    {
        $state = $this->getState();

        if ($state === Runtime::STATE_DESTROYED)
        {
            return Promise::doResolve(
                'Runtime has been already destroyed.'
            );
        }

        $controller = $this;
        return $controller
            ->stop()
            ->then(
                function() {
                    return Promise::doResolve()
                        ->then(
                            function() {
                                return $this->getRuntimeManager()->getRuntimes();
                            }
                        )
                        ->then(
                            function($runtimes) {
                                return $this->getRuntimeManager()->destroyRuntimes($runtimes, Runtime::DESTROY_FORCE);
                            }
                        );
                }
            )
            ->then(
                function() use($controller) {
                    $promise = new Promise();

                    $emitter = $controller->getEventEmitter();
                    $emitter->emit('beforeDestroy');
                    $emitter->emit('destroy');

                    $controller->getLoop()->onTick(function() use($controller, $promise) {
                        $controller->setState(Runtime::STATE_DESTROYED);
                        $controller->setLoopState(self::LOOP_STATE_STOPPED);
                        $controller->stopLoop();

                        $emitter = $controller->getEventEmitter();
                        $emitter->emit('afterDestroy');

                        $promise->resolve();
                    });

                    return $promise
                        ->then(
                            function() {
                                return 'Runtime has been destroyed.';
                            }
                        );
                }
            );
    }

    /**
     * @override
     * @inheritDoc
     */
    public function start()
    {
        $state = $this->getState();

        if ($state === Runtime::STATE_DESTROYED)
        {
            return Promise::doReject(
                new RejectionException("It is not possible to start runtime from state [$state].")
            );
        }
        else if ($state === Runtime::STATE_STARTED)
        {
            return Promise::doResolve(
                'Runtime has been already started.'
            );
        }

        $this->setState(Runtime::STATE_STARTED);

        $emitter = $this->getEventEmitter();
        $emitter->emit('beforeStart');
        $emitter->emit('start');
        $emitter->emit('afterStart');

        return Promise::doResolve('Runtime has been started.');
    }

    /**
     * @override
     * @inheritDoc
     */
    public function stop()
    {
        $state = $this->getState();

        if ($state === Runtime::STATE_CREATED || $state === Runtime::STATE_DESTROYED)
        {
            return Promise::doReject(
                new RejectionException("It is not possible to stop runtime from state [$state].")
            );
        }
        else if ($state === Runtime::STATE_STOPPED)
        {
            return Promise::doResolve(
                'Runtime has been already stopped.'
            );
        }

        $this->setState(Runtime::STATE_STOPPED);

        $emitter = $this->getEventEmitter();
        $emitter->emit('beforeStop');
        $emitter->emit('stop');
        $emitter->emit('afterStop');

        return Promise::doResolve('Runtime has been stopped.');
    }

    /**
     * @override
     * @inheritDoc
     */
    public function fail($ex, $params = [])
    {
        if ($this->failureHash !== null)
        {
            return;
        }

        $super = $this->getSupervisor();
        $loop  = $this->getLoop();

        $hash = HashSupport::hash();

        $this->failureHash = $hash;
        $params['hash'] = $hash;

        $this->setLoopState(self::LOOP_STATE_FAILED);
        $loop->onTick(function() use($super, $ex, $params) {
            try
            {
                $super
                    ->solve($ex, $params)
                    ->done(
                        null,
                        function($reason) {
                            throw $reason;
                        }
                    );
            }
            catch (Error $ex)
            {
                $super
                    ->solve($ex);
            }
            catch (Exception $ex)
            {
                $super
                    ->solve($ex);
            }
        });
    }

    /**
     * @override
     * @inheritDoc
     */
    public function succeed()
    {
        $this->failureHash = null;
        $this->setLoopState(self::LOOP_STATE_STARTED);
        $this->startLoop();
    }

    /**
     * @param int $state
     * @throws Exception
     */
    protected function setLoopState($state)
    {
        if ($state === $this->loopState)
        {
            return;
        }

        switch ($state)
        {
            case self::LOOP_STATE_STOPPED:
                $this->stopLoop();
                break;

            case self::LOOP_STATE_STARTED:
                $this->stopLoop();
                if ($this->loopState === self::LOOP_STATE_FAILED)
                {
                    $this->getLoop()->import($this->loopBackup);
                }
                break;

            case self::LOOP_STATE_FAILED:
                $this->stopLoop();
                if ($this->loopState === self::LOOP_STATE_STARTED)
                {
                    $this->getLoop()->export($this->loopBackup);
                    $this->getLoop()->erase();
                }
                break;

            default:
                throw new LogicException('Method RuntimeModel::setLoopState() tried switching to invalid state.');
        }

        $this->loopState = $this->loopNextState = $state;
    }

    /**
     * @return int
     */
    protected function getLoopState()
    {
        return $this->loopState;
    }

    /**
     * @param int $state
     * @return bool
     */
    protected function isLoopState($state)
    {
        return $this->loopState === $state;
    }

    /**
     *
     */
    protected function startLoop()
    {
        while ($this->loopNextState !== self::LOOP_STATE_STOPPED)
        {
            $this->loopNextState = self::LOOP_STATE_STOPPED;

            try
            {
                $this->getLoop()->start();
            }
            catch (Error $ex)
            {
                $this->fail($ex);
            }
            catch (Exception $ex)
            {
                $this->fail($ex);
            }
        }
    }

    /**
     *
     */
    protected function stopLoop()
    {
        $this->getLoop()->stop();
    }

    /**
     * @param mixed $object
     * @param mixed[] $params
     * @return mixed
     */
    private function reflect($object, $params = [])
    {
        return (new ReflectionClass(get_class($object)))->newInstanceArgs($params);
    }
}
