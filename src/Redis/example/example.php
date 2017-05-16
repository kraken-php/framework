<?php
require_once __DIR__ . '/../../autoload.php';

use Kraken\Loop\Loop;
use Kraken\Redis\Client;
use Kraken\Loop\Model\SelectLoop;
#examples

$loop = new Loop(new SelectLoop());

$client = new Client('192.168.99.100:32769', $loop);

$of = function (Client $client) use ($loop) {
    $client->set('test', 'New');
    // end connection once all pending requests have been resolved
    $client->end();
};

$client->connect()->then($of);
$client->reply();