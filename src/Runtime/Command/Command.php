<?php

namespace Kraken\Runtime\Command;

use Kraken\Command\CommandInterface;
use Kraken\Runtime\RuntimeContainerInterface;
use Kraken\Throwable\Exception\Logic\InstantiationException;

class Command extends \Kraken\Command\Command implements CommandInterface
{
    /**
     * @var RuntimeContainerInterface
     */
    protected $runtime;

    /**
     * @param mixed[] $context
     * @throws InstantiationException
     */
    public function __construct($context = [])
    {
        if (!isset($context['runtime']) || !$context['runtime'] instanceof RuntimeContainerInterface)
        {
            throw new InstantiationException('Command did not get expected RuntimeContainerInterface.');
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
