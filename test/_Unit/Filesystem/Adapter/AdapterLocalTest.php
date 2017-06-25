<?php

namespace Kraken\_Unit\Filesystem\Adapter;

use Kraken\Filesystem\Adapter\AdapterLocal;
use Kraken\Test\TUnit;
use Dazzle\Throwable\Exception\Logic\InstantiationException;

class AdapterLocalTest extends TUnit
{
    /**
     *
     */
    public function testApiConstruct_ThrowsException_WhenParentThrowsException()
    {
        $this->setExpectedException(InstantiationException::class);

        $adapter = new AdapterLocal('/ext');
    }
}
