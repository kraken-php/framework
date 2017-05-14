<?php

namespace Kraken\Redis\Protocol;

use Kraken\Redis\Protocol\Data\Request;

interface RespProtocol
{
    public function commands(Request $request);

    public function replies($data);
}