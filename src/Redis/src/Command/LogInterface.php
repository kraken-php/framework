<?php

namespace Kraken\Redis\Command;

interface LogInterface
{
    public function pFAdd($key,...$elements);
    public function pFCount(...$keys);
    public function pFMerge(array $dsKeyMap);
}