# Kraken Ipc Component

[![Build Status](https://travis-ci.org/kraken-php/framework.svg)](https://travis-ci.org/kraken-php/framework)
[![Total Downloads](https://poser.pugx.org/kraken-php/ipc/downloads)](https://packagist.org/packages/kraken-php/ipc) 
[![Latest Stable Version](https://poser.pugx.org/kraken-php/ipc/v/stable)](https://packagist.org/packages/kraken-php/ipc) 
[![Latest Unstable Version](https://poser.pugx.org/kraken-php/ipc/v/unstable)](https://packagist.org/packages/kraken-php/ipc) 
[![License](https://poser.pugx.org/kraken-php/framework/license)](https://packagist.org/packages/kraken-php/framework)
[![Kraken Compatible](https://img.shields.io/badge/kraken-compatible-6b02af.svg)](https://github.com/kraken-php/framework)

> **Note:** This repository is a part of [Kraken Framework][3], but **can be used freely as standalone library**. If you 
are interested in more asynchronous components for PHP, check out the rest of [Kraken repository][5] or see our 
[asynchronous application skeleton][4] example.

## Description

Ipc is a component that provides various models for implementing inter-process communication via asynchronous
sockets or external services.

## Feature Highlights

Ipc features:

* Asynchronous handling of incoming and outcoming messages,
* Support for TCP, UDP and Unix sockets,
* Support for ZeroMQ extension and ZeroMQ protocols,
* Kraken Framework compatibility,
* ...and more.

## Examples

See more examples in [official documentation][2].

## Requirements

* PHP-5.6 or PHP-7.0+,
* UNIX or Windows OS.

## Installation

```
composer require kraken-php/ipc
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
[2]: http://kraken-php.com/docs/api-ipc
[3]: https://github.com/kraken-php/framework
[4]: https://github.com/kraken-php/kraken
[5]: https://github.com/kraken-php
