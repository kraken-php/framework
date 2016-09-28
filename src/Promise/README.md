# Kraken Promise Component

[![Build Status](https://travis-ci.org/kraken-php/framework.svg)](https://travis-ci.org/kraken-php/framework)
[![Total Downloads](https://poser.pugx.org/kraken-php/promise/downloads)](https://packagist.org/packages/kraken-php/promise) 
[![Latest Stable Version](https://poser.pugx.org/kraken-php/promise/v/stable)](https://packagist.org/packages/kraken-php/promise) 
[![Latest Unstable Version](https://poser.pugx.org/kraken-php/promise/v/unstable)](https://packagist.org/packages/kraken-php/promise) 
[![License](https://poser.pugx.org/kraken-php/framework/license)](https://packagist.org/packages/kraken-php/framework)
[![Kraken Compatible](https://img.shields.io/badge/kraken-compatible-6b02af.svg)](https://github.com/kraken-php/framework)

> **Note:** This repository is a part of [Kraken Framework][3], but **can be used freely as standalone library**. If you 
are interested in more asynchronous components for PHP, check out the rest of [Kraken repository][5] or see our 
[asynchronous application skeleton][4] example.

## Description

Promise is a component that provides PHP implementation of promises using Promise/A+ specification and additional 
promise-related concepts such as joining, mapping, reducing and cancelling promises.

## Feature Highlights

Promise features:

* Implementation of promises using Promise/A+ specification,
* Methods to resolve, reject or cancel promises,
* Cancellation of promises using forget semantics,
* Methods to join, map, race, reduce and do other things with set of promises,
* Kraken Framework compatibility,
* ...and more.

## Examples

See more examples in [official documentation][2].

## Requirements

* PHP-5.5, PHP-5.6 or PHP-7.0+,
* UNIX or ~~Windows~~ OS.

## Installation

```
composer require kraken-php/promise
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
[2]: http://kraken-php.com/docs/api-promise
[3]: https://github.com/kraken-php/framework
[4]: https://github.com/kraken-php/kraken
[5]: https://github.com/kraken-php
