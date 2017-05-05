<?php

/**
 * ---------------------------------------------------------------------------------------------------------------------
 * DESCRIPTION
 * ---------------------------------------------------------------------------------------------------------------------
 * This file contains the example of writing data to files.
 * ---------------------------------------------------------------------------------------------------------------------
 */

require __DIR__ . '/../../autoload.php';

use Kraken\Loop\Model\SelectLoop;
use Kraken\Loop\Loop;
use Kraken\SSH\Auth\SSH2Password;
use Kraken\SSH\SSH2;
use Kraken\SSH\SSH2Config;
use Kraken\SSH\SSH2DriverInterface;
use Kraken\SSH\SSH2Interface;
use Kraken\SSH\SSH2ResourceInterface;

$user = getenv('TEST_USER') ? getenv('TEST_USER') : 'kraken';
$pass = getenv('TEST_PASS') ? getenv('TEST_PASS') : 'kraken-1234';

$loop   = new Loop(new SelectLoop);
$auth   = new SSH2Password($user, $pass);
$config = new SSH2Config();
$ssh2   = new SSH2($auth, $config, $loop);

$ssh2->on('connect:sftp', function(SSH2DriverInterface $sftp) use($loop, $ssh2) {
    echo "# CONNECTED SFTP\n";

    $lines = [ "KRAKEN\n", "IS\n", "AWESOME!\n" ];
    $linesPointer = 0;

    $file = $sftp->open(__DIR__ . '/_file_write.txt', 'w+');
    $file->write();
    $file->on('drain', function(SSH2ResourceInterface $file) use(&$lines, &$linesPointer) {
        echo "# PART OF THE DATA HAS BEEN WRITTEN\n";
        if ($linesPointer < count($lines)) {
            $file->write($lines[$linesPointer++]);
        }
    });
    $file->on('finish', function(SSH2ResourceInterface $file) {
        echo "# FINISHED WRITING\n";
        $file->close();
    });
    $file->on('close', function(SSH2ResourceInterface $file) use($sftp) {
        echo "# FILE HAS BEEN CLOSED\n";
        $sftp->disconnect();
    });
});

$ssh2->on('disconnect:sftp', function(SSH2DriverInterface $sftp) use($ssh2) {
    echo "# DISCONNECTED SFTP\n";
    $ssh2->disconnect();
});

$ssh2->on('connect', function(SSH2Interface $ssh2) {
    echo "# CONNECTED\n";
    $ssh2->createDriver(SSH2::DRIVER_SFTP)
         ->connect();
});

$ssh2->on('disconnect', function(SSH2Interface $ssh2) use($loop) {
    echo "# DISCONNECTED\n";
    $loop->stop();
});

$loop->onTick(function() use($ssh2) {
    $ssh2->connect();
});

$loop->start();
