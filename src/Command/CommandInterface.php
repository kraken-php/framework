<?php

namespace Kraken\Command;

use Kraken\Promise\PromiseInterface;

interface CommandInterface
{
    /**
     * @param mixed[] $params
     * @return PromiseInterface
     * @resolves mixed
     * @rejects Error|Exception|string|null
     * @cancels Error|Exception|string|null
     */
    public function __invoke($params = []);

    /**
     * @param mixed[] $params
     * @return PromiseInterface
     * @resolves mixed
     * @rejects Error|Exception|string|null
     * @cancels Error|Exception|string|null
     */
    public function execute($params = []);
}
