<?php

namespace Kraken\_Unit\Console\Server;

use Kraken\_Unit\Runtime\_Case\RuntimeCase;
use Kraken\Console\Server\Server;
use Kraken\Runtime\RuntimeContainer;
use Kraken\Test\TUnit;

class ServerTest extends TUnit
{
    use RuntimeCase;

    /**
     * @param string[] $params
     * @param string[]|null $methods
     * @return RuntimeContainer|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createRuntime($params = [], $methods = null)
    {
        $params[0] = isset($params[0]) ? $params[0] : 'parent';
        $params[1] = isset($params[1]) ? $params[1] : 'alias';
        $params[2] = isset($params[2]) ? $params[2] : 'class';

        return $this->getMock(Server::class, $methods, $params);
    }
}
