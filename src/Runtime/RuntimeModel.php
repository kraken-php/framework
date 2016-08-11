<?php

namespace Kraken\Runtime;

use Kraken\Event\EventEmitterInterface;
use Kraken\Throwable\Exception\Runtime\Execution\RejectionException;
use Kraken\Promise\Promise;
use Kraken\Promise\PromiseInterface;
use Kraken\Core\CoreInterface;
use Kraken\Supervisor\SupervisorInterface;
use Kraken\Throwable\Exception\LogicException;
use Kraken\Loop\Loop;
use Kraken\Loop\LoopExtendedInterface;
use Error;
use Exception;
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
     * @param string $parent
     * @param string $alias
     * @param string $name
     */
    public function __construct($parent, $alias, $name)
    {
        $this->parent = ($parent === Runtime::PARENT_UNDEFINED || $parent === Runtime::RESERVED_CONSOLE_SERVER) ? null : $parent;
        $this->alias = $alias;
        $this->name = $name;

        $this->state = Runtime::STATE_DESTROYED;
        $this->core = null;
        $this->manager = null;
        $this->loop = null;
        $this->loopBackup = null;
        $this->loopState = self::LOOP_STATE_STOPPED;
        $this->loopNextState = self::LOOP_STATE_STOPPED;
        $this->supervisor = null;
        $this->eventEmitter = null;
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->parent);
        unset($this->alias);
        unset($this->name);

        unset($this->state);
        unset($this->core);
        unset($this->manager);
        unset($this->loop);
        unset($this->loopBackup);
        unset($this->loopState);
        unset($this->loopNextState);
        unset($this->supervisor);
        unset($this->eventEmitter);
    }

    /**
     * @return string
     */
    public function type()
    {
        return Runtime::UNIT_UNDEFINED;
    }

    /**
     * @return string|null
     */
    public function parent()
    {
        return $this->parent;
    }

    /**
     * @return string
     */
    public function alias()
    {
        return $this->alias;
    }

    /**
     * @return string
     */
    public function name()
    {
        return $this->name;
    }

    /**
     * @param CoreInterface|null $core
     */
    public function setCore(CoreInterface $core = null)
    {
        $this->core = $core;
    }

    /**
     * @return CoreInterface|null
     */
    public function getCore()
    {
        return $this->core;
    }

    /**
     * @return CoreInterface
     */
    public function core()
    {
        return $this->core;
    }

    /**
     * @param RuntimeManagerInterface|null $manager
     */
    public function setRuntimeManager(RuntimeManagerInterface $manager = null)
    {
        $this->manager = $manager;
    }

    /**
     * @return RuntimeManagerInterface|null
     */
    public function getRuntimeManager()
    {
        return $this->manager;
    }

    /**
     * @param LoopExtendedInterface|null $loop
     */
    public function setLoop(LoopExtendedInterface $loop = null)
    {
        $this->loop = $loop;
        $this->loopBackup = $loop !== null ? new Loop($this->reflect($this->loop->getModel())) : $loop;
    }

    /**
     * @return LoopExtendedInterface|null
     */
    public function getLoop()
    {
        return $this->loop;
    }

    /**
     * @return LoopExtendedInterface
     */
    public function loop()
    {
        return $this->loop;
    }

    /**
     * @param SupervisorInterface|null $supervisor
     */
    public function setSupervisor(SupervisorInterface $supervisor = null)
    {
        $this->supervisor = $supervisor;
    }

    /**
     * @return SupervisorInterface|null
     */
    public function getSupervisor()
    {
        return $this->supervisor;
    }

    /**
     * @return SupervisorInterface
     */
    public function supervisor()
    {
        return $this->supervisor;
    }

    /**
     * @param EventEmitterInterface|null $emitter
     */
    public function setEventEmitter(EventEmitterInterface $emitter = null)
    {
        $this->eventEmitter = $emitter;
    }

    /**
     * @return EventEmitterInterface|null
     */
    public function getEventEmitter()
    {
        return $this->eventEmitter;
    }

    /**
     * @return EventEmitterInterface
     */
    public function eventEmitter()
    {
        return $this->eventEmitter;
    }

    /**
     * @param int $state
     */
    public function setState($state)
    {
        $this->state = $state;
    }

    /**
     * @return int
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @return int
     */
    public function state()
    {
        return $this->state;
    }

    /**
     * @param int $state
     * @return bool
     */
    public function isState($state)
    {
        return $this->state === $state;
    }

    /**
     * @return bool
     */
    public function isCreated()
    {
        return $this->isState(Runtime::STATE_CREATED);
    }

    /**
     * @return bool
     */
    public function isDestroyed()
    {
        return $this->isState(Runtime::STATE_DESTROYED);
    }

    /**
     * @return bool
     */
    public function isStarted()
    {
        return $this->isState(Runtime::STATE_STARTED);
    }

    /**
     * @return bool
     */
    public function isStopped()
    {
        return $this->isState(Runtime::STATE_STOPPED);
    }

    /**
     * @return PromiseInterface
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
        $this->getLoop()->afterTick(function() use($promise) {
            $promise->resolve(
                $this
                    ->start()
                    ->then(function() {
                        return 'Runtime has been created.';
                    })
            );
        });

        $this->setState(Runtime::STATE_CREATED);

        $emitter = $this->getEventEmitter();
        $emitter->emit('beforeCreate');
        $emitter->emit('create');
        $emitter->emit('afterCreate');

        $this->setLoopState(self::LOOP_STATE_STARTED);
        $this->startLoop();

        return $promise;
    }

    /**
     * @return PromiseInterface
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
                                return $this->getRuntimeManager()->destroyProcesses($runtimes, Runtime::DESTROY_FORCE);
                            }
                        );
                }
            )
            ->then(
                function() use($controller) {
                    $controller->getLoop()->afterTick(function() use($controller) {
                        $controller->setState(Runtime::STATE_DESTROYED);

                        $emitter = $controller->getEventEmitter();
                        $emitter->emit('beforeDestroy');
                        $emitter->emit('destroy');
                        $emitter->emit('afterDestroy');

                        $controller->setLoopState(self::LOOP_STATE_STOPPED);
                        $controller->stopLoop();
                    });

                    return 'Runtime has been destroyed.';
                }
            );
    }

    /**
     * @return PromiseInterface
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
     * @return PromiseInterface
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
     * @param Error|Exception $ex
     * @param mixed[] $params
     */
    public function fail($ex, $params = [])
    {
        $super = $this->getSupervisor();

        $this->setLoopState(self::LOOP_STATE_FAILED);
        $this->getLoop()->afterTick(function() use($super, $ex, $params) {
            try
            {
                $super
                    ->handle($ex, $params)
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
                    ->handle($ex);
            }
            catch (Exception $ex)
            {
                $super
                    ->handle($ex);
            }
        });
    }

    /**
     *
     */
    public function succeed()
    {
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
                    $this->getLoop()->export($this->loopBackup)->flush();
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
