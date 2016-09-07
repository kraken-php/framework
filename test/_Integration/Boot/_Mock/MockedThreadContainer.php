<?php

namespace Kraken\_Integration\Boot\_Mock;

use Kraken\Runtime\Container\ThreadContainer;

class MockedThreadContainer extends ThreadContainer
{
    /**
     * @return string
     */
    public function genEndpoint()
    {
        return '';
    }
}
