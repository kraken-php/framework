<?php

namespace Kraken\_Unit\Runtime;

use Kraken\_Unit\Runtime\_Case\RuntimeCase;
use Kraken\Runtime\RuntimeContainer;

class RuntimeContainerTest extends RuntimeCase
{
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

        return $this->getMock(RuntimeContainer::class, $methods, $params);
    }
}