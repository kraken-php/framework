# Kraken Channel Component

[![Build Status](https://travis-ci.org/kraken-php/framework.svg)](https://travis-ci.org/kraken-php/framework)
[![Total Downloads](https://poser.pugx.org/kraken-php/channel/downloads)](https://packagist.org/packages/kraken-php/channel) 
[![Latest Stable Version](https://poser.pugx.org/kraken-php/channel/v/stable)](https://packagist.org/packages/kraken-php/channel) 
[![Latest Unstable Version](https://poser.pugx.org/kraken-php/channel/v/unstable)](https://packagist.org/packages/kraken-php/channel) 
[![License](https://poser.pugx.org/kraken-php/framework/license)](https://packagist.org/packages/kraken-php/framework)
[![Kraken Compatible](https://img.shields.io/badge/kraken-compatible-6b02af.svg)](https://github.com/kraken-php/framework)

> **Note:** This repository is a part of [Kraken Framework][3], but **can be used freely as standalone library**. If you 
are interested in more asynchronous components for PHP, check out the rest of [Kraken repository][5] or see our 
[asynchronous application skeleton][4] example.

## Description

Channel is an event-based component that allows sending and receiving messsages asynchronously. It provides 
abstraction for various IPC models and is designed to be used in multi-threaded, multi-processed systems. It
provides complex routing mechanisms, protocols, message encoders and extends behaviour of decorated IPC models by 
implementing hearbeat mechanisms, reconnect mechanisms and allowing usage of both async and request-reply messaging 
patterns.

## Feature Highlights

Channel features:

* Message-driven communication,
* IPC models abstraction,
* Support for sending asynchronous messages,
* Support for request-reply pattern,
* Built-in offline and online message buffers,
* Built-in configurable protocol-based routing mechanisms,
* Separation of input and output routers,
* Heartbeat mechanism,
* Reconnect mechanism,
* Event-based API,
* Promise-based helpers,
* Kraken Framework compatibility,
* ...and more.

## Examples

See more examples in [official documentation][2].

## Requirements

* PHP-5.5, PHP-5.6 or PHP-7.0+,
* UNIX or ~~Windows~~ OS.

## Installation

```
composer require kraken-php/channel
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
[2]: http://kraken-php.com/docs/api-channel
[3]: https://github.com/kraken-php/framework
[4]: https://github.com/kraken-php/kraken
[5]: https://github.com/kraken-php
