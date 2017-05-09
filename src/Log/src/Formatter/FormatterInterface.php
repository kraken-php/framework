<?php

namespace Kraken\Log\Formatter;

use Monolog\Formatter\FormatterInterface as MonologFormatterInterface;

interface FormatterInterface extends MonologFormatterInterface
{
    /**
     * @return MonologFormatterInterface
     */
    public function getModel();
}
