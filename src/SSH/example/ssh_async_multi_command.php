<?php

/**
 * ---------------------------------------------------------------------------------------------------------------------
 * DESCRIPTION
 * ---------------------------------------------------------------------------------------------------------------------
 * This file contains the example of executing set of multiple commands.
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

function executeCommand(SSH2DriverInterface $shell, $shellCommand) {

    $buffer = '';
    $command = $shell->open();
    $command->write($shellCommand);
    $command->on('data', function(SSH2ResourceInterface $command, $data) use(&$buffer) {
        $buffer .= $data;
    });
    $command->on('end', function(SSH2ResourceInterface $command) use(&$buffer) {
        echo "# COMMAND RETURNED:\n";
        echo $buffer;
        $command->close();
    });
}

$user = getenv('TEST_USER') ? getenv('TEST_USER') : 'kraken';
$pass = getenv('TEST_PASS') ? getenv('TEST_PASS') : 'kraken-1234';

$loop   = new Loop(new SelectLoop);
$auth   = new SSH2Password($user, $pass);
$config = new SSH2Config();
$ssh2   = new SSH2($auth, $config, $loop);

$ssh2->on('connect:shell', function(SSH2DriverInterface $shell) use($ssh2, $loop) {
    echo "# CONNECTED SHELL\n";

    $commands = [ 'ls -la', 'pwd' ];
    $commandsRemain = count($commands);

    $shell->on('resource:close', function(SSH2DriverInterface $shell) use(&$commandsRemain) {
        if (--$commandsRemain === 0) {
            $shell->disconnect();
        }
    });

    foreach ($commands as $command) {
        executeCommand($shell, $command);
    }
});

$ssh2->on('disconnect:shell', function(SSH2DriverInterface $shell) use($ssh2) {
    echo "# DISCONNECTED SHELL\n";
    $ssh2->disconnect();
});

$ssh2->on('connect', function(SSH2Interface $ssh2) {
    echo "# CONNECTED\n";
    $ssh2->createDriver(SSH2::DRIVER_SHELL)
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
