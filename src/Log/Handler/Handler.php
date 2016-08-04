<?php

namespace Kraken\Log\Handler;

use Monolog\Formatter\FormatterInterface;
use Monolog\Handler\HandlerInterface as MonologHandlerInterface;

class Handler implements HandlerInterface
{
    /**
     * @var MonologHandlerInterface
     */
    protected $model;

    /**
     * @param MonologHandlerInterface $model
     */
    public function __construct(MonologHandlerInterface $model)
    {
        $this->model = $model;
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->model);
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
    public function isHandling(array $record)
    {
        return $this->model->isHandling($record);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function handle(array $record)
    {
        return $this->model->handle($record);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function handleBatch(array $records)
    {
        return $this->model->handleBatch($records);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function pushProcessor($callback)
    {
        return $this->model->pushProcessor($callback);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function popProcessor()
    {
        return $this->model->popProcessor();
    }

    /**
     * @override
     * @inheritDoc
     */
    public function setFormatter(FormatterInterface $formatter)
    {
        return $this->model->setFormatter($formatter);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getFormatter()
    {
        return $this->model->getFormatter();
    }
}
