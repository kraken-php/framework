<?php

namespace Kraken\Redis\Protocol;

use Clue\Redis\Protocol\Model\Request;

interface RespProtocol
{
    public function commands(Request $request);

    public function replies($data);
}