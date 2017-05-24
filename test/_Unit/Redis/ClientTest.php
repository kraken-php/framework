<?php

namespace Kraken\_Unit\Redis;

use Kraken\Loop\Loop;
use Kraken\Loop\Model\SelectLoop;
use Kraken\Test\TUnit;
use Kraken\Redis\Client;

class ClientTest extends TUnit
{
    /**
     * @var Client
     */
    private $case;
    public function setUp()
    {
        $this->case = $this->createClient();
    }

    public function testSet()
    {
        $this->case->connect();
        $this->case->set('test','hello kraken\redis')->then(function ($msg) {
            $this->assertEquals('hello kraken\redis', $msg);
        });
    }

    private function createClient()
    {
        $host = "192.168.99.100:32769";
        $loop = new Loop(new SelectLoop());

        return new Client($host , $loop);
    }
}