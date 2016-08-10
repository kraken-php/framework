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
     * @param CoreInterface $core
     */
    public function setCore(CoreInterface $core = null)
    {
        $this->core = $core;
    }

    /**
     * @return CoreInterface
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
     * @param RuntimeManagerInterface $manager
     */
    public function setRuntimeManager(RuntimeManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @return RuntimeManagerInterface
     */
    public function getRuntimeManager()
    {
        return $this->manager;
    }

    /**
     * @return RuntimeManagerInterface
     */
    public function runtimeManager()
    {
        return $this->manager;
    }

    /**
     * @param LoopExtendedInterface $loop
     */
    public function setLoop(LoopExtendedInterface $loop)
    {
        $this->loop = $loop;
        $this->loopBackup = new Loop($this->reflect($this->loop->getModel()));
    }

    /**
     * @return LoopExtendedInterface
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
     * @param SupervisorInterface $supervisor
     */
    public function setSupervisor(SupervisorInterface $supervisor)
    {
        $this->supervisor = $supervisor;
    }

    /**
     * @return SupervisorInterface
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
     * @param EventEmitterInterface $emitter
     */
    public function setEventEmitter(EventEmitterInterface $emitter)
    {
        $this->eventEmitter = $emitter;
    }

    /**
     * @return EventEmitterInterface
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

        if ($state !== Runtime::STATE_DESTROYED)
        {
            return Promise::doReject(
                new RejectionException("It is not possible to create runtime from state [$state].")
            );
        }

        $promise = new Promise();
        $this->loop()->afterTick(function() use($promise) {
            $this
                ->start()
                ->then(
                    function() use($promise) {
                        return $promise->resolve('Runtime has been created.');
                    }
                );
        });

        $this->setState(Runtime::STATE_CREATED);
        $emitter = $this->eventEmitter();
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
        $controller = $this;
        return $controller
            ->stop()
            ->then(
                function() {
                    return Promise::doResolve()
                        ->then(
                            function() {
                                return $this->runtimeManager()->getRuntimes();
                            }
                        )
                        ->then(
                            function($runtimes) {
                                return $this->runtimeManager()->destroyProcesses($runtimes, Runtime::DESTROY_FORCE);
                            }
                        );
                }
            )
            ->then(
                function() {
                    return 'Runtime has been destroyed.';
                }
            )
            ->then(
                function() use($controller) {
                    $controller->loop()->afterTick(function() use($controller) {
                        $controller->setState(Runtime::STATE_DESTROYED);
                        $emitter = $controller->eventEmitter();
                        $emitter->emit('beforeDestroy');
                        $emitter->emit('destroy');
                        $emitter->emit('afterDestroy');
                        $controller->setLoopState(self::LOOP_STATE_STOPPED);
                        $controller->stopLoop();
                    });
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

        if ($state !== Runtime::STATE_STARTED)
        {
            $this->setState(Runtime::STATE_STARTED);
            $emitter = $this->eventEmitter();
            $emitter->emit('beforeStart');
            $emitter->emit('start');
            $emitter->emit('afterStart');
        }

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

        if ($state !== Runtime::STATE_STOPPED)
        {
            $this->setState(Runtime::STATE_STOPPED);
            $emitter = $this->eventEmitter();
            $emitter->emit('beforeStop');
            $emitter->emit('stop');
            $emitter->emit('afterStop');
        }

        return Promise::doResolve('Runtime has been stopped.');
    }

    /**
     * @param Error|Exception $ex
     * @param mixed[] $params
     */
    public function fail($ex, $params = [])
    {
        $manager = $this->supervisor();
        $this->setLoopState(self::LOOP_STATE_FAILED);
        $this->loop()->afterTick(function() use($manager, $ex, $params) {
            try
            {
                $manager
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
                $manager
                    ->handle($ex);
            }
            catch (Exception $ex)
            {
                $manager
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

        try
        {
            switch ($state)
            {
                case self::LOOP_STATE_STOPPED:
                    $this->stopLoop();
                    break;

                case self::LOOP_STATE_STARTED:
                    $this->stopLoop();
                    if ($this->loopState === self::LOOP_STATE_FAILED)
                    {
                        $this->loop()->import($this->loopBackup);
                    }
                    break;

                case self::LOOP_STATE_FAILED:
                    $this->stopLoop();
                    if ($this->loopState === self::LOOP_STATE_STARTED)
                    {
                        $this->loop()->export($this->loopBackup)->flush();
                    }
                    break;

                default:
                    throw new LogicException('RuntimeModel::setState() tried switching to invalid state.');
            }
        }
        catch (Error $ex)
        {
            throw $ex;
        }
        catch (Exception $ex)
        {
            throw $ex;
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
                $this->loop()->start();
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
        $this->loop()->stop();
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
