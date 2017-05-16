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
    $client->set('test','Hello Kraken Redis!');
    $client->get('test')->then(function ($ret) use ($client) {
        var_export($ret);
        $client->end();
    })->resolve();
});
$client->connect();

$loop->start();
