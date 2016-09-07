<?php

namespace Kraken\_Integration\Boot\_Mock;

use Kraken\Runtime\Container\ProcessContainer;

class MockedProcessContainer extends ProcessContainer
{
    /**
     * @return string
     */
    public function genEndpoint()
    {
        return '';
    }
}
