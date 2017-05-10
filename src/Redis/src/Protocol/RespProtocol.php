<?php

namespace Kraken\Redis\Protocol;

interface RespProtocol
{
    public function commands($data);

    public function replies($data);
}