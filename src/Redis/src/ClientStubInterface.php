<?php

namespace Kraken\Redis;


interface ClientStubInterface extends \RequestInterface,\ResponseInterface
{
    public function buildRequst();
    public function parseResponse();
    public
}