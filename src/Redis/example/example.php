<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Kraken\Loop\Loop;
use Kraken\Redis\Client;
use Kraken\Loop\Model\SelectLoop;
use Kraken\Promise\Promise;
#examples

$loop = new Loop(new SelectLoop());

$client = new Client('192.168.99.100:32769', $loop);

$ret = [];

$client->on('connect', function (Client $client) {

    $client->flushDb()->then(function ($value) {
        global $ret;
        $ret[] = $value;
    });

    $client->set('test1','test1');

    $client->set('test2','test2');

    $client->set('test','Hello Kraken Redis!')->then(function ($value) {
        global $ret;
        $ret[] = $value;
    });

    $client->get('test')->then(function ($value) {
        global $ret;
        $ret[] = $value;
    });

    $client->append('test', 'Make PHP Awesome')->then(function ($value) {
        global $ret;
        $ret[] = $value;
    });

    $client->get('test')->then(function ($value) {
        global $ret;
        $ret[] = $value;
    });

    $client->ping()->then(function ($value) {
        global $ret;
        $ret[] = $value;
    });

    $client->exists('test','test1','test2')->then(function ($value) {
        global $ret;
        $ret[] = $value;
    });

    $client->bitPos('test',5,10,20)->then(function ($value) {
        //todo : fix
        global $ret;
        $ret[] = $value;
    });

    $client->expire('test',1)->then(function ($value) {
        global $ret;
        $ret[] = $value;
    });

    $client->info(['cpu'])->then(function ($value) {
        global $ret;
        $ret[] = $value;
    });

    $client->dump('test')->then(function ($value) use ($client) {
        global $ret;
        $ret[] = $value;
        $client->restore('test',0,$value)->then(function ($value) {
            global $ret;
            $ret[] = $value;
        });
    });

    $client->touch('f','f1','f2','f3')->then(function ($value) {
        global $ret;
        $ret[] = $value;
    });

    $client->rename('test','new_test')->then(function ($value) use ($client) {
       if ($value == 'OK') {
           global $ret;
           $ret[] = 'RENAME OK';
       }
    });

    $client->ttl('new_test')->then(function ($value) {
        global $ret;
        $ret[] = $value;
    });

    $client->del('test','test1','test2')->then(function ($value) {
        global $ret;
        $ret[] = $value;
    });

    $client->randomKey()->then(function ($value) {
        global $ret;
        $ret[] = $value;
    });

    $client->type('test1')->then(function ($value) {
        global $ret;
        $ret[] = $value;
    });

    $client->unLink('new_test')->then(function ($value) {
        global $ret;
        $ret[] = $value;
    });

    $client->end();
});

$client->connect();
$loop->start();

var_export($ret);