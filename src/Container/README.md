# Kraken Framework - Container Component

[![Build Status](https://travis-ci.org/kraken-php/framework.svg)](https://travis-ci.org/kraken-php/framework)
[![Total Downloads](https://poser.pugx.org/kraken-php/container/downloads)](https://packagist.org/packages/kraken-php/container) 
[![Latest Stable Version](https://poser.pugx.org/kraken-php/container/v/stable)](https://packagist.org/packages/kraken-php/container) 
[![Latest Unstable Version](https://poser.pugx.org/kraken-php/container/v/unstable)](https://packagist.org/packages/kraken-php/container) 
[![License](https://poser.pugx.org/kraken-php/framework/license)](https://packagist.org/packages/kraken-php/framework)
[![Kraken Compatible](https://img.shields.io/badge/kraken-compatible-8002af.svg)](https://github.com/kraken-php/framework)

> **Note:** This repository is part of [Kraken Framework][3]. It can be used as standalone library, but for the best 
efficiency we suggest you to also check out the rest of [Kraken Repository][5].

<br>
<p align="center">
<img src="https://avatars2.githubusercontent.com/u/15938282?v=3&s=150" />
</p>

## Description

Kraken/Container is both powerful dependency injection container and service container.

## Feature Highlights

Kraken/Container features:

* Support for binding objects, classes, params and factory methods to container,
* Support for modifying container and removal of its definitions on fly,
* Autoresolve for not defined classes or servies,
* Autowiring for simple and nested dependencies,
* Service providers with configurable requirements and providees,
* Service registers,
* Sorting algorithm to ensure right execution order of providers, based on their dependencies,
* Kraken Framework compatibility,
* ...and more.

## Interface

See more in [official documentation][2].

## Requirements

* PHP-5.5, PHP-5.6 or PHP-7.0+,
* UNIX or ~~Windows~~ OS.

## Installation

```
composer require kraken-php/container
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
[2]: http://kraken-php.com/docs/api-container
[3]: https://github.com/kraken-php/framework
[4]: https://github.com/kraken-php/kraken
[5]: https://github.com/kraken-php
