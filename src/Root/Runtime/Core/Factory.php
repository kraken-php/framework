<?php

namespace Kraken\Root\Runtime\Core;

use Kraken\Core\Core;
use Kraken\Runtime\Runtime;
use Kraken\Throwable\Exception\Logic\InstantiationException;

class Factory
{
    /**
     * @var string
     */
    private $type = null;

    /**
     * @param string $type
     */
    public function __construct($type)
    {
        $this->type = $type;
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->type);
    }

    /**
     * @param string|null $path
     * @return Core
     * @throws InstantiationException
     */
    public function create($path = null)
    {
        if ($this->type === Runtime::UNIT_PROCESS)
        {
            return new ProcessCore($path);
        }

        if ($this->type === Runtime::UNIT_THREAD)
        {
            return new ThreadCore($path);
        }

        throw new InstantiationException('Passed undefined core type to create');
    }
}
