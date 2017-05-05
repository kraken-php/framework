<?php
use Kraken\Loop\Loop;
use Kraken\Redis\Client;
use Kraken\Loop\Model\SelectLoop;
#examples

$bigLoop = new Loop(new SelectLoop());

$loop = new Loop(new SelectLoop());

$client = new Client('192.168.99.100:32778', $loop);

$of = function (Client $client) use ($loop) {

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

$client->connect()->then($of);

$bigLoop->onStart(function () use ($loop) {
$loop->start();
});

$bigLoop->onStop(function () use ($loop) {
$loop->stop();
});

$bigLoop->start();