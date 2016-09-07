<?php

namespace Kraken\_Integration\Boot\_Mock;

use Kraken\Runtime\RuntimeContainer;

class RuntimeContainerMock extends RuntimeContainer
{
    /**
     * @return string
     */
    public function genEndpoint()
    {
        return '';
    }
}
