<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Kraken\Loop\Loop;
use Kraken\Redis\Client;
use Kraken\Loop\Model\SelectLoop;
#examples

$loop = new Loop(new SelectLoop());

$client = new Client('192.168.99.100:32769', $loop);

$ret = null;
$client->on('connect',function (Client $client) use ($ret) {
    $ret = $client->get('test')->then(function ($msg) use ($client) {
        var_export($msg);
        $client->end();
    });
});
$client->connect();

$loop->start();
