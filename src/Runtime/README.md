# Kraken Runtime Component

[![Build Status](https://travis-ci.org/kraken-php/framework.svg)](https://travis-ci.org/kraken-php/framework)
[![Total Downloads](https://poser.pugx.org/kraken-php/runtime/downloads)](https://packagist.org/packages/kraken-php/runtime) 
[![Latest Stable Version](https://poser.pugx.org/kraken-php/runtime/v/stable)](https://packagist.org/packages/kraken-php/runtime) 
[![Latest Unstable Version](https://poser.pugx.org/kraken-php/runtime/v/unstable)](https://packagist.org/packages/kraken-php/runtime) 
[![License](https://poser.pugx.org/kraken-php/framework/license)](https://packagist.org/packages/kraken-php/framework)
[![Kraken Compatible](https://img.shields.io/badge/kraken-compatible-6b02af.svg)](https://github.com/kraken-php/framework)

> **Note:** This repository is a part of [Kraken Framework][3], but **can be used freely as standalone library**. If you 
are interested in more asynchronous components for PHP, check out the rest of [Kraken repository][5] or see our 
[asynchronous application skeleton][4] example.

## Description

Runtime is component that provides container-based abstraction for Threads and Processes and means of managing
and supervising children containers from its ancestor level.

## Feature Highlights

Runtime features:

* Container-based abstraction for Threads and Processes,
* Separation between standard and emergent flow of business logic inside single container,
* Command-based controls to pass orders between containers,
* Built-in Process local and remote managers,
* Built-in Thread local and remote managers,
* Built-in Runtime managers abstracting managment of processes and threads,
* Supervision mechanisms with separation of local and remote errors,
* Supervision problem solvers,
* Kraken Framework compatibility,
* ...and more.

## Examples

See more examples in [official documentation][2].

## Requirements

* PHP-5.5, PHP-5.6 or PHP-7.0+,
* UNIX or ~~Windows~~ OS.

## Installation

```
composer require kraken-php/runtime
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
[2]: http://kraken-php.com/docs/api-runtime
[3]: https://github.com/kraken-php/framework
[4]: https://github.com/kraken-php/kraken
[5]: https://github.com/kraken-php
