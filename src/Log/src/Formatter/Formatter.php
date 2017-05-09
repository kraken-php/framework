<?php

namespace Kraken\Log\Formatter;

use Monolog\Formatter\FormatterInterface as MonologFormatterInterface;

class Formatter implements FormatterInterface
{
    /**
     * @var MonologFormatterInterface
     */
    protected $model;

    /**
     * @param MonologFormatterInterface $model
     */
    public function __construct(MonologFormatterInterface $model)
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
    public function format(array $record)
    {
        return $this->model->format($record);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function formatBatch(array $records)
    {
        return $this->model->formatBatch($records);
    }
}
