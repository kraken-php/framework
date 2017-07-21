# Kraken PHP Framework ~ Release the Kraken!

[![Build Status](https://travis-ci.org/kraken-php/framework.svg)](https://travis-ci.org/kraken-php/framework)
[![Latest Stable Version](https://poser.pugx.org/kraken-php/framework/v/stable)](https://packagist.org/packages/kraken-php/framework) 
[![Latest Unstable Version](https://poser.pugx.org/kraken-php/framework/v/unstable)](https://packagist.org/packages/kraken-php/framework) 
[![License](https://poser.pugx.org/kraken-php/framework/license)](https://packagist.org/packages/kraken-php/framework)
[![Gitter](https://badges.gitter.im/kraken-php/framework.svg)](https://gitter.im/kraken-php/framework?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge)
[![@kraken_php on Twitter](https://img.shields.io/badge/twitter-%40kraken__php-blue.svg)](https://twitter.com/kraken_php)

> **Note:** This repository contains the core of the Kraken Framework. If you want to start developing new application with Kraken, check out [Kraken Application Skeleton](https://github.com/kraken-php/kraken). If you want to learn more visit [official website](http://kraken-php.com).

<br>
<p align="center">
<img src="https://avatars2.githubusercontent.com/u/15938282?v=3&s=150" />
</p>

## Description

Kraken is the first and only multi-processed, multi-threaded, fault-tolerant framework for PHP. It has been written to provide easy and reliable API for creating distributed applications using PHP. Kraken aims to solve typical problems of writing such applications and to provide developers with powerful yet elegant tools for dealing with them. 

The main focus of Kraken Framework is put on: 
* __Concurrency__ : create systems that are asynchronous and concurrent by design,
* __Distribution__ : divide your application into several containers and run them on multiple threads, processors or hosts,
* __Fault tolerance__ : write systems that self-heal using remote and local supervision hierarchies,
* __Elasticity__ : modify existing architecture in realtime without need to change in code,
* __High performance__ : handle up to thousands of connections per second on each container,
* __Extensibility__ : use available options to easily extend and adapt framework features for your needs.

Start writing applications that were previously marked as impossible or hard to implement in PHP right know. Servers, service-oriented architecture, agent-based models, games, complex daemons, socket programs, schedulers and much, much more - nothing is impossible with Kraken! 

## Feature Highlights

Kraken features:

* Support for asynchronous programming using fully-featured event Loop with multiple backgrounds.
* Support for event-driven architecture.
* Easy to understand and work with Promise-based API.
* Consistent multi-processing and multi-threading.
* Process and Thread abstraction as isolated message-driven containers.
* Built-in message routing system and IPC abstraction.
* Configurable local and remote supervision hierarchies.
* Centralized deployment and management.
* Extensible Console and Server interface.
* Asynchronous TCP and UDP sockets.
* Asynchronous Stream wrappers.
* Standalone HTTP and WebSocket server.
* Variety of IPC models.
* ReactPHP-compatibility adapters.
* ...and more.

Full list of features can be found on [official website][1].

## Performance

Kraken is able to emit millions of events and thousands of messages and connections per second using single container. It is scalable for multiple processes and threads, faster than traditional PHP approach and able to handle similar amount of connections as Node.js.

<p align="center">
<img src="https://docs.google.com/uc?export=download&id=0B_FVuB10kPjVT21lY3JzVTRwT3c" width="882" height="334" />
</p>

> **Note:** Keep in mind that Kraken project does not solely focus around HTTP performance. It provides a set of distinct asynchronous libraries to use in PHP. The attached graph's main intention is to show that PHP is fast enough to compete with the leading technologies available on the market. The HTTP component has been chosen as it is the only one that can be easily compared between asynchronous and synchronous MVC frameworks. Do not treat it as an actual benchmark.

## Powered By

Kraken Framework is built on top of asynchronous library named [Dazzle Project](https://github.com/dazzle-php/dazzle). If you are looking for a solution simpler than framework, you might consider using it instead.

<br>
<p align="center">
<img src="https://github.com/dazzle-php/dazzle/blob/master/media/dazzle-x125.png" />
</p>

## Requirements

* PHP-5.6 or PHP-7.0+,
* UNIX or Windows OS,
* Additional constraints based on which components you do plan to use.

## Installation and Official Documentation

Documentation for the framework can be found in the [official documentation][2] page. To see installation instructions, 
please check out [application skeleton](https://github.com/kraken-php/kraken) or go to [installation guide][3].

## Examples

There are few examples you can try, before deciding to use Kraken:

- [Chat Application](https://github.com/kraken-php/demo-chat).
- [Symfony Integration & Websockets Bundle](https://github.com/kraken-collective/ws-symfony).
- [Symfony Integration & Websockets Bundle Example](https://github.com/kraken-collective/ws-symfony-example).

If you have written your own demo application for Kraken, and want to list it here, contact us!

## Frequently Asked Questions

#### How does the Kraken differ from other PHP async libraries?

> In comparison to already existing PHP async libraries, Kraken gives the developer not only the async tools, but also do the most of the dirty work of creating distributed applications. It gives you consistent interface for working with processes and threads, implements fault-tolerance mechanisms, allows usage of remote and local supervision hierarchies, gives IPC abstractions and implements most important messaging patterns such as routing, heartbeating and much, much more. Instead of thinking how to use async libraries in the new project and how to design the project with them, developer can simply start Kraken instance and focus on its business logic!

#### Is PHP GC able to handle daemonized, long-running application?

> In most cases yes, but developers still have to keep an eye on application memory usage. PHP 5.5+, which is required for 
> using framework, is able to successfully deal with proper memory handling and garbage collecting. It proves to be true 
> regarding Kraken modules as in development cycle special attention was paid to ensure they do not leak memory. This, 
> however, cannot be guaranteed with usage of third party vendors as some (ex. ORMs) are known to be prone to this problem. 
> The easiest way to avoid it is to simply use destructors, unset functions and create memory allocation and deallocation
> tests. If despite that you still stumble across this problem, the best way to deal with it is to isolate leaking piece 
> of code in separated container and restart it cyclically when it reaches its memory limits.

<br>

If there are any additional questions that you have about framework, please check whether the answers for them have been 
already posted in [issues][4] or ask on our [gitter room][8].


## Contributing

Thank you for considering contributing to Kraken Framework! The contribution guide can be found in the [contribution tips][5].

## License

Kraken Framework is open-sourced software licensed under the [MIT license][6]. The documentation is provided under [FDL-1.3 license][7].

[1]: http://kraken-php.com
[2]: http://kraken-php.com/docs
[3]: http://kraken-php.com/docs/installation
[4]: https://github.com/kraken-php/framework/issues
[5]: https://github.com/kraken-php/framework/blob/master/CONTRIBUTING.md
[6]: http://opensource.org/licenses/MIT
[7]: https://www.gnu.org/licenses/fdl-1.3.en.html
[8]: https://gitter.im/kraken-php/framework
