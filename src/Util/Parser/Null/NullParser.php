<?php

namespace Kraken\Util\Parser\Null;

use Kraken\Util\Parser\ParserInterface;

class NullParser implements ParserInterface
{
    /**
     * @override
     * @inheritDoc
     */
    public function encode($mixed)
    {
        return '';
    }

    /**
     * @override
     * @inheritDoc
     */
    public function decode($str)
    {
        return null;
    }
}
