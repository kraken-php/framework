<?php

namespace Kraken\Console\Client;

use Kraken\Promise\PromiseInterface;

interface ConsoleCommandHandlerInterface
{
    /**
     * @param string|null $commandParent
     * @param string $commandName
     * @param string[] $commandParams
     * @return PromiseInterface
     */
    public function handle($commandParent, $commandName, $commandParams = []);
}
