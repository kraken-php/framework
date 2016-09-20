# Kraken Framework - Runtime Component

[![Build Status](https://travis-ci.org/kraken-php/framework.svg)](https://travis-ci.org/kraken-php/framework)
[![Total Downloads](https://poser.pugx.org/kraken-php/runtime/downloads)](https://packagist.org/packages/kraken-php/runtime) 
[![Latest Stable Version](https://poser.pugx.org/kraken-php/runtime/v/stable)](https://packagist.org/packages/kraken-php/runtime) 
[![Latest Unstable Version](https://poser.pugx.org/kraken-php/runtime/v/unstable)](https://packagist.org/packages/kraken-php/runtime) 
[![License](https://poser.pugx.org/kraken-php/framework/license)](https://packagist.org/packages/kraken-php/framework)
[![Kraken Compatible](https://img.shields.io/badge/kraken-compatible-8002af.svg)](https://github.com/kraken-php/framework)

> **Note:** This repository is part of [Kraken Framework][3]. It can be used as standalone library, but for the best 
efficiency we suggest you to also check out the rest of [Kraken Repository][5].

<br>
<p align="center">
<img src="https://avatars2.githubusercontent.com/u/15938282?v=3&s=150" />
</p>

## Description

Kraken/Runtime is component that provides container-based abstraction for Threads and Processes and means of managing
and supervising children containers from its ancestor level.

## Feature Highlights

Kraken/Runtime features:

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

## Interface

See more in [official documentation][2].

## Requirements

* PHP-5.5, PHP-5.6 or PHP-7.0+,
* UNIX or ~~Windows~~ OS.

## Installation

```
composer require kraken-php/runtime
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
[2]: http://kraken-php.com/docs/0.3/runtime
[3]: https://github.com/kraken-php/framework
[4]: https://github.com/kraken-php/kraken
[5]: https://github.com/kraken-php
