# Kraken Framework - Loop Component

[![Build Status](https://travis-ci.org/kraken-php/framework.svg)](https://travis-ci.org/kraken-php/framework)
[![Total Downloads](https://poser.pugx.org/kraken-php/loop/downloads)](https://packagist.org/packages/kraken-php/loop) 
[![Latest Stable Version](https://poser.pugx.org/kraken-php/loop/v/stable)](https://packagist.org/packages/kraken-php/loop) 
[![Latest Unstable Version](https://poser.pugx.org/kraken-php/loop/v/unstable)](https://packagist.org/packages/kraken-php/loop) 
[![License](https://poser.pugx.org/kraken-php/framework/license)](https://packagist.org/packages/kraken-php/framework)

> **Note:** This repository is part of [Kraken Framework][3]. It can be used as standalone library, but for the best 
efficiency we suggest you to also check out the rest of [Kraken Repository][5].

<br>
<p align="center">
<img src="https://avatars2.githubusercontent.com/u/15938282?v=3&s=150" />
</p>

## Description

Kraken/Loop is component that provides abstraction layer for writing asynchronous code in PHP on single thread or process
with usage of single or multiple loops.

## Feature Highlights

Kraken/Loop features:

* Interface for writing asynchronous code on single Thread or Process,
* File descriptor polling,
* One-time and periodic timers,
* Deferred execution of callbacks,
* Support for StreamSelect -based loops,
* ~~Support for LibEvent -based loops~~,
* ~~Support for LibEv -based loops~~,
* ~~Support for ExtEvent -based loops~~,
* Support for using multiple loops with multiple execution flows,
* Support for switching between loops and importing/exporting its unfinished queues,
* ReactPHP compatibility,
* ReactPHP EventLoop adapters,
* Kraken Framework compatibility,
* ...and more.

## Interface

See more in [official documentation][2].

## Requirements

* PHP-5.5, PHP-5.6 or PHP-7.0+,
* UNIX or ~~Windows~~ OS.

## Installation

```
composer require kraken-php/loop
```

## Tests

Tests are provided in [Framework Repository][3].

## Documentation

Documentation for this module can be found in the [official documentation][2].

## Contributing

This library is read-only subtree split of Kraken Framework. To make contributions, please go to [Framework Repository][3].

## License

This library licensed under the same license as [Kraken Framework][3].

[1]: http://kraken-php.com
[2]: http://kraken-php.com/docs/0.3/loop
[3]: https://github.com/kraken-php/framework
[4]: https://github.com/kraken-php/kraken
[5]: https://github.com/kraken-php
