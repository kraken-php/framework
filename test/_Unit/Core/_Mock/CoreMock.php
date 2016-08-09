<?php

namespace Kraken\_Unit\Core\_Mock;

use Kraken\Core\Core;
use Kraken\Throwable\Exception\Runtime\ExecutionException;
use Kraken\Throwable\Exception\Runtime\Io\IoWriteException;

class CoreMock extends Core
{
    /**
     * @throws ExecutionException
     */
    protected function registerDefaultProviders()
    {
        throw new ExecutionException('Error');
    }

    /**
     * @throws IoWriteException
     */
    protected function registerDefaultAliases()
    {
        throw new IoWriteException('Error');
    }
}
