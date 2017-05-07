# Kraken Network Component

[![Build Status](https://travis-ci.org/kraken-php/framework.svg)](https://travis-ci.org/kraken-php/framework)
[![Total Downloads](https://poser.pugx.org/kraken-php/network/downloads)](https://packagist.org/packages/kraken-php/network) 
[![Latest Stable Version](https://poser.pugx.org/kraken-php/network/v/stable)](https://packagist.org/packages/kraken-php/network) 
[![Latest Unstable Version](https://poser.pugx.org/kraken-php/network/v/unstable)](https://packagist.org/packages/kraken-php/network) 
[![License](https://poser.pugx.org/kraken-php/framework/license)](https://packagist.org/packages/kraken-php/framework)
[![Kraken Compatible](https://img.shields.io/badge/kraken-compatible-6b02af.svg)](https://github.com/kraken-php/framework)

> **Note:** This repository is a part of [Kraken Framework][3], but **can be used freely as standalone library**. If you 
are interested in more asynchronous components for PHP, check out the rest of [Kraken repository][5] or see our 
[asynchronous application skeleton][4] example.

## Description

Network is a component that provides possibility to create standalone, asynchronous servers supporting various network
protocols, including TCP, HTTP and WebSockets.

## Feature Highlights

Network features:

* Asynchronous TCP server,
* Asynchronous HTTP server,
* Asynchronous WebSocket server,
* Support for HTTP/1.0 protocol,
* Support for HTTP/1.1 protocol,
* Support for WebSocket RFC6455 protocol,
* Support for WebSocket HyBi10 protocol,
* Connections firewall,
* HTTP request and response abstraction,
* HTTP routing,
* HTTP session provider,
* Kraken Framework compatibility,
* ...and more.

## Examples

See more examples in [official documentation][2].

## Requirements

* PHP-5.5, PHP-5.6 or PHP-7.0+,
* UNIX or ~~Windows~~ OS.

## Installation

```
composer require kraken-php/network
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
[2]: http://kraken-php.com/docs/api-network
[3]: https://github.com/kraken-php/framework
[4]: https://github.com/kraken-php/kraken
[5]: https://github.com/kraken-php
