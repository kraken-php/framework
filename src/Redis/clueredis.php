<?php

require_once __DIR__.'/vendor/autoload.php';

use Kraken\Redis\AsyncRedisClient;
use Kraken\Redis\Client;

$loop = new \Kraken\Loop\Loop(new \Kraken\Loop\Model\SelectLoop());
$client = AsyncRedisClient::client('tcp://192.168.99.100:32768',$loop);

$onFulfilled = function (Client $client) use ($loop) {
    $client->set('greeting', 'Hello world');
    $client->append('greeting', '!');

    $client->get('greeting')->then(function ($greeting) {
        // Hello world!
        echo $greeting . PHP_EOL;
    });

    $client->incr('invocation')->then(function ($n) {
        echo 'This is invocation #' . $n . PHP_EOL;
    });

    // end connection once all pending requests have been resolved
    $client->end();
};
$onRejected = function (Client $client) use ($loop) {
    $client->close();
    $loop->stop();
};

$onProgress = function (Client $client) use ($loop) {
    die('here');
};

$factory->createClient('tcp://192.168.99.100:3238')->then($onFulfilled, $onRejected);

$loop->run();