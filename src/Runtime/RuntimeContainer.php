<?php

namespace Kraken\Runtime;

use Kraken\Core\CoreInterface;
use Kraken\Event\EventEmitter;
use Error;

abstract class RuntimeContainer extends EventEmitter implements RuntimeContainerInterface
{
    /**
     * @var RuntimeModelInterface
     */
    protected $model;

    /**
     * @param string $parent
     * @param string $alias
     * @param string $name
     * @param string[] $args
     */
    public function __construct($parent, $alias, $name, $args = [])
    {
        parent::__construct();

        $this->model = new RuntimeModel($parent, $alias, $name, $args);
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
    public function getType()
    {
        return $this->getCore()->getType();
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getParent()
    {
        return $this->model->getParent();
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getAlias()
    {
        return $this->model->getAlias();
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getName()
    {
        return $this->model->getName();
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getArgs()
    {
        return $this->model->getArgs();
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getCore()
    {
        return $this->model->getCore();
    }

    /**
     * @override
     * @inheritDoc
     */
    public function setCore(CoreInterface $core = null)
    {
        $this->model->setCore($core);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getManager()
    {
        return $this->model->getRuntimeManager();
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getLoop()
    {
        return $this->model->getLoop();
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getState()
    {
        return $this->model->getState();
    }

    /**
     * @override
     * @inheritDoc
     */
    public function onBeforeCreate(callable $callback)
    {
        return $this->on('beforeCreate', $callback);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function onCreate(callable $callback)
    {
        return $this->on('create', $callback);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function onAfterCreate(callable $callback)
    {
        return $this->on('afterCreate', $callback);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function onBeforeDestroy(callable $callback)
    {
        return $this->on('beforeDestroy', $callback);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function onDestroy(callable $callback)
    {
        return $this->on('destroy', $callback);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function onAfterDestroy(callable $callback)
    {
        return $this->on('afterDestroy', $callback);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function onBeforeStart(callable $callback)
    {
        return $this->on('beforeStart', $callback);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function onStart(callable $callback)
    {
        return $this->on('start', $callback);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function onAfterStart(callable $callback)
    {
        return $this->on('afterStart', $callback);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function onBeforeStop(callable $callback)
    {
        return $this->on('beforeStop', $callback);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function onStop(callable $callback)
    {
        return $this->on('stop', $callback);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function onAfterStop(callable $callback)
    {
        return $this->on('afterStop', $callback);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function isCreated()
    {
        return $this->model->isCreated();
    }

    /**
     * @override
     * @inheritDoc
     */
    public function isDestroyed()
    {
        return $this->model->isDestroyed();
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
    public function create()
    {
        return $this->model->create();
    }

    /**
     * @override
     * @inheritDoc
     */
    public function destroy()
    {
        return $this->model->destroy();
    }

    /**
     * @override
     * @inheritDoc
     */
    public function start()
    {
        return $this->model->start();
    }

    /**
     * @override
     * @inheritDoc
     */
    public function stop()
    {
        return $this->model->stop();
    }

    /**
     * @override
     * @inheritDoc
     */
    public function fail($ex, $params = [])
    {
        $this->model->fail($ex, $params);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function succeed()
    {
        $this->model->succeed();
    }

    /**
     * @internal
     * @param CoreInterface $core
     * @return array
     */
    public function internalConfig(CoreInterface $core)
    {
        return $this->config($core);
    }

    /**
     * @internal
     * @param CoreInterface $core
     * @return RuntimeContainerInterface
     */
    public function internalBoot(CoreInterface $core)
    {
        $core->instance(RuntimeContainer::class, $this);

        return $this->boot($core);
    }

    /**
     * @internal
     * @param CoreInterface $core
     * @return RuntimeContainerInterface
     */
    public function internalConstruct(CoreInterface $core)
    {
        return $this->construct($core);
    }

    /**
     * This method will be called before container boot. It should contain and return additional configurations.
     *
     * @param CoreInterface $core
     * @return array
     */
    protected function config(CoreInterface $core)
    {
        return [];
    }

    /**
     * This method will be called on container boot. It should contain tweaks to services and providers.
     *
     * This method
     *
     * @param CoreInterface $core
     * @return RuntimeContainerInterface
     */
    protected function boot(CoreInterface $core)
    {
        return $this;
    }

    /**
     * This method will be called on container construction. It should contain logic to be fired after booting up.
     *
     * @param CoreInterface $core
     * @return RuntimeContainerInterface
     */
    protected function construct(CoreInterface $core)
    {
        return $this;
    }
}
