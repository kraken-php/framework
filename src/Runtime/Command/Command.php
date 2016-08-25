<?php

namespace Kraken\Runtime\Command;

use Kraken\Command\CommandInterface;
use Kraken\Runtime\RuntimeInterface;
use Kraken\Throwable\Exception\Logic\InstantiationException;

class Command extends \Kraken\Command\Command implements CommandInterface
{
    /**
     * @var RuntimeInterface
     */
    protected $runtime;

    /**
     * @param mixed[] $context
     * @throws InstantiationException
     */
    public function __construct($context = [])
    {
        if (!isset($context['runtime']) || !$context['runtime'] instanceof RuntimeInterface)
        {
            throw new InstantiationException('Command did not get expected RuntimeInterface.');
        }

        $this->runtime = $context['runtime'];

        parent::__construct($context);
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->runtime);

        parent::__destruct();
    }
}
