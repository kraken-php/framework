# Kraken Asynchronous SSH

[![Build Status](https://travis-ci.org/kraken-php/framework.svg)](https://travis-ci.org/kraken-php/framework)
[![Latest Stable Version](https://poser.pugx.org/kraken-php/ssh/v/stable)](https://packagist.org/packages/kraken-php/ssh) 
[![Latest Unstable Version](https://poser.pugx.org/kraken-php/ssh/v/unstable)](https://packagist.org/packages/kraken-php/ssh) 
[![License](https://poser.pugx.org/kraken-php/framework/license)](https://packagist.org/packages/kraken-php/framework)
[![Kraken Compatible](https://img.shields.io/badge/kraken-compatible-6b02af.svg)](https://github.com/kraken-php/framework)

> **Note:** This repository is a part of [Kraken Framework][3], but **can be used freely as standalone library**. If you 
are interested in more asynchronous components for PHP, check out the rest of [Kraken Project][5] or see our 
[asynchronous application skeleton][4] example.

## Description

SSH is a component that provides consistent interface for PHP SSH2 extension and allows asynchronous writing and reading.

## Feature Highlights

SSH features:

* OOP abstraction for PHP SSH2 extension,
* Support for variety of authorization methods,
* Asynchronous SSH2 commands,
* Asynchronous operations on files via SFTP,
* Kraken Framework compatibility,
* ...and more.

## Examples

This section contains most frequently asked for examples. You can see more in **example directory** or in [official documentation][2].

### Executing commands

```php
$loop   = new Loop(new SelectLoop);
$auth   = new SSH2Password($user, $pass);
$config = new SSH2Config();
$ssh2   = new SSH2($auth, $config, $loop);

$ssh2->on('connect:shell', function(SSH2DriverInterface $shell) use($ssh2, $loop) {
    echo "# CONNECTED SHELL\n";

    $buffer = '';
    $command = $shell->open();
    $command->write('ls -la');
    $command->on('data', function(SSH2ResourceInterface $command, $data) use(&$buffer) {
        $buffer .= $data;
    });
    $command->on('end', function(SSH2ResourceInterface $command) use(&$buffer) {
        echo "# COMMAND RETURNED:\n";
        echo $buffer;
        $command->close();
    });
    $command->on('close', function(SSH2ResourceInterface $command) use($shell) {
        $shell->disconnect();
    });
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
```

### Writing files

```php
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
```

### Reading files

```php
$loop   = new Loop(new SelectLoop);
$auth   = new SSH2Password($user, $pass);
$config = new SSH2Config();
$ssh2   = new SSH2($auth, $config, $loop);

$ssh2->on('connect:sftp', function(SSH2DriverInterface $sftp) use($loop, $ssh2) {
    echo "# CONNECTED SFTP\n";

    $buffer = '';
    $file = $sftp->open(__DIR__ . '/_file_read.txt', 'r+');
    $file->read();
    $file->on('data', function(SSH2ResourceInterface $file, $data) use(&$buffer) {
        $buffer .= $data;
    });
    $file->on('end', function(SSH2ResourceInterface $file) use(&$buffer) {
        echo "# FOLLOWING LINES WERE READ FROM FILE:\n";
        echo $buffer;
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
```

See more examples in **example directory** or in [official documentation][2].

## Requirements

* PHP-5.6 or PHP-7.0+,
* UNIX or Windows OS,
* PHP SSH2 extension enabled.

## Installation

```
composer require kraken-php/ssh
```

## Tests

Tests are provided within our write-only [Framework repository][3].

## Documentation

Documentation for this module can be found in the [official documentation][2].

## Contributing

This library is read-only subtree split of Kraken Framework. To make contributions, please go to [Framework repository][3].

## License

This library licensed under the MIT license, see more information in [Kraken Framework][3] license section.

[1]: http://kraken-php.com
[2]: http://kraken-php.com/docs/api-ssh
[3]: https://github.com/kraken-php/framework
[4]: https://github.com/kraken-php/kraken
[5]: https://github.com/kraken-php
