<?php

namespace Kraken\Runtime;

use Kraken\Core\CoreInterface;
use Kraken\Event\EventEmitter;
use Kraken\Event\EventHandler;
use Kraken\Loop\LoopInterface;
use Kraken\Promise\PromiseInterface;
use Kraken\Runtime\Container\Provider\Runtime\RuntimeAutowireProvider;
use Kraken\Runtime\Container\Provider\Runtime\RuntimeProvider;
use Error;
use Exception;

abstract class RuntimeContainer extends EventEmitter implements RuntimeInterface
{
    /**
     * @var RuntimeModelInterface
     */
    protected $model;

    /**
     * @param string $parent
     * @param string $alias
     * @param string $name
     */
    public function __construct($parent, $alias, $name)
    {
        parent::__construct();

        $this->model = new RuntimeModel($parent, $alias, $name);
        $this->model->setEventEmitter($this);
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->model);

        parent::__destruct();
    }

    /**
     * @return RuntimeModelInterface
     */
    public function model()
    {
        return $this->model;
    }

    /**
     * @return string
     */
    public function type()
    {
        return $this->core()->unit();
    }

    /**
     * @return string|null
     */
    public function parent()
    {
        return $this->model->parent();
    }

    /**
     * @return string
     */
    public function alias()
    {
        return $this->model->alias();
    }

    /**
     * @return string
     */
    public function name()
    {
        return $this->model->name();
    }

    /**
     * @return CoreInterface
     */
    public function getCore()
    {
        return $this->model->getCore();
    }

    /**
     * @param CoreInterface $core
     */
    public function setCore(CoreInterface $core)
    {
        $this->model->setCore($core);
    }

    /**
     * @return CoreInterface
     */
    public function core()
    {
        return $this->getCore();
    }

    /**
     * @return RuntimeManagerInterface
     */
    public function manager()
    {
        return $this->model->runtimeManager();
    }

    /**
     * @return LoopInterface
     */
    public function getLoop()
    {
        return $this->model->getLoop();
    }

    /**
     * @return LoopInterface
     */
    public function loop()
    {
        return $this->getLoop();
    }

    /**
     * @return int
     */
    public function state()
    {
        return $this->model->getState();
    }

    /**
     * @param callable $callback
     * @return EventHandler
     */
    public function onBeforeCreate(callable $callback)
    {
        return $this->on('beforeCreate', $callback);
    }

    /**
     * @param callable $callback
     * @return EventHandler
     */
    public function onCreate(callable $callback)
    {
        return $this->on('create', $callback);
    }

    /**
     * @param callable $callback
     * @return EventHandler
     */
    public function onAfterCreate(callable $callback)
    {
        return $this->on('afterCreate', $callback);
    }

    /**
     * @param callable $callback
     * @return EventHandler
     */
    public function onBeforeDestroy(callable $callback)
    {
        return $this->on('beforeDestroy', $callback);
    }

    /**
     * @param callable $callback
     * @return EventHandler
     */
    public function onDestroy(callable $callback)
    {
        return $this->on('destroy', $callback);
    }

    /**
     * @param callable $callback
     * @return EventHandler
     */
    public function onAfterDestroy(callable $callback)
    {
        return $this->on('afterDestroy', $callback);
    }

    /**
     * @param callable $callback
     * @return EventHandler
     */
    public function onBeforeStart(callable $callback)
    {
        return $this->on('beforeStart', $callback);
    }

    /**
     * @param callable $callback
     * @return EventHandler
     */
    public function onStart(callable $callback)
    {
        return $this->on('start', $callback);
    }

    /**
     * @param callable $callback
     * @return EventHandler
     */
    public function onAfterStart(callable $callback)
    {
        return $this->on('afterStart', $callback);
    }

    /**
     * @param callable $callback
     * @return EventHandler
     */
    public function onBeforeStop(callable $callback)
    {
        return $this->on('beforeStop', $callback);
    }

    /**
     * @param callable $callback
     * @return EventHandler
     */
    public function onStop(callable $callback)
    {
        return $this->on('stop', $callback);
    }

    /**
     * @param callable $callback
     * @return EventHandler
     */
    public function onAfterStop(callable $callback)
    {
        return $this->on('afterStop', $callback);
    }

    /**
     * @return bool
     */
    public function isCreated()
    {
        return $this->model->isCreated();
    }

    /**
     * @return bool
     */
    public function isDestroyed()
    {
        return $this->model->isDestroyed();
    }

    /**
     * @return bool
     */
    public function isStarted()
    {
        return $this->model->isStarted();
    }

    /**
     * @return bool
     */
    public function isStopped()
    {
        return $this->model->isStopped();
    }

    /**
     * @return PromiseInterface
     */
    public function create()
    {
        return $this->model->create();
    }

    /**
     * @return PromiseInterface
     */
    public function destroy()
    {
        return $this->model->destroy();
    }

    /**
     * @return PromiseInterface
     */
    public function start()
    {
        return $this->model->start();
    }

    /**
     * @return PromiseInterface
     */
    public function stop()
    {
        return $this->model->stop();
    }

    /**
     * @param Error|Exception $ex
     * @param mixed[] $params
     * @throws Exception
     */
    public function fail($ex, $params = [])
    {
        $this->model->fail($ex, $params);
    }

    /**
     *
     */
    public function succeed()
    {
        $this->model->succeed();
    }

    /**
     * @param CoreInterface $core
     * @return array
     */
    public function internalConfig(CoreInterface $core)
    {
        return $this->config($core);
    }

    /**
     * @param CoreInterface $core
     * @return RuntimeInterface
     */
    public function internalBoot(CoreInterface $core)
    {
        $core->registerProvider(new RuntimeProvider($this));
        $core->registerProvider(new RuntimeAutowireProvider());

        return $this->boot($core);
    }

    /**
     * @param CoreInterface $core
     * @return RuntimeInterface
     */
    public function internalConstruct(CoreInterface $core)
    {
        return $this->construct($core);
    }

    /**
     * @param CoreInterface $core
     * @return array
     */
    protected function config(CoreInterface $core)
    {
        return [];
    }

    /**
     * @param CoreInterface $core
     * @return RuntimeInterface
     */
    protected function boot(CoreInterface $core)
    {
        return $this;
    }

    /**
     * @param CoreInterface $core
     * @return RuntimeInterface
     */
    protected function construct(CoreInterface $core)
    {
        return $this;
    }
}
