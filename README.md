# Kraken PHP Framework ~ Release the Kraken!

[![Build Status](https://travis-ci.org/kraken-php/framework.svg)](https://travis-ci.org/kraken-php/framework)
[![Latest Stable Version](https://poser.pugx.org/kraken-php/framework/v/stable)](https://packagist.org/packages/kraken-php/framework) 
[![Latest Unstable Version](https://poser.pugx.org/kraken-php/framework/v/unstable)](https://packagist.org/packages/kraken-php/framework) 
[![License](https://poser.pugx.org/kraken-php/framework/license)](https://packagist.org/packages/kraken-php/framework)
[![Gitter](https://badges.gitter.im/kraken-php/framework.svg)](https://gitter.im/kraken-php/framework?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge)
[![@kraken_php on Twitter](https://img.shields.io/badge/twitter-%40kraken__php-blue.svg)](https://twitter.com/kraken_php)

> **Note:** This repository contains the core of the Kraken Framework. If you want to start developing new application with Kraken, check out [Kraken Application Skeleton](https://github.com/kraken-php/kraken). If you want to learn more visit [offical website](http://kraken-php.com).

<br>
<p align="center">
<img src="https://avatars2.githubusercontent.com/u/15938282?v=3&s=150" />
</p>

## Description

Kraken is the first and only multi-processed, multi-threaded, fault-tolerant framework for PHP. It has been written to provide easy and reliable API for creating distributed applications using PHP. Kraken aims to solve typical problems of writing such applications and to provide developers with powerful yet elegant tools for dealing with them. 

The main focus of Kraken Framework is put on: 
* __Concurrency__ : create systems that are asynchronous and concurrent by design,
* __Distribution__ : divide your application into several containers and run them on multiple threads, processors or hosts,
* __Faul tolerance__ : write systems that self-heal using remote and local supervision hierarchies,
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

## Modules

Kraken Framework is fully modular and each of its components can be used separately. If for some reason you don't want to download full application stack, you can require any of the following components:

* [__Kraken/Channel__](https://github.com/kraken-php/channel) : IPC abstractions,
* [__Kraken/Config__](https://github.com/kraken-php/config) : Default configurator,
* [__Kraken/Console__](https://github.com/kraken-php/console) : Console and Server implementation,
* [__Kraken/Container__](https://github.com/kraken-php/container) : Service container,
* [__Kraken/Core__](https://github.com/kraken-php/core) : Framework core,
* [__Kraken/Environment__](https://github.com/kraken-php/environment) : Environment controller,
* [__Kraken/Event__](https://github.com/kraken-php/event) : Support for events,
* [__Kraken/Filesystem__](https://github.com/kraken-php/filesystem) : Default filesystem,
* [__Kraken/Ipc__](https://github.com/kraken-php/ipc) : IPC models,
* [__Kraken/Log__](https://github.com/kraken-php/log) : Default logger,
* [__Kraken/Loop__](https://github.com/kraken-php/loop) : Event-loop implementation,
* [__Kraken/Network__](https://github.com/kraken-php/network) : Network protocols servers,
* [__Kraken/Promise__](https://github.com/kraken-php/promise) : Promise/A+ implementation,
* [__Kraken/Root__](https://github.com/kraken-php/root) : Default composition root,
* [__Kraken/Runtime__](https://github.com/kraken-php/runtime) : Process and Thread abstractions,
* [__Kraken/Stream__](https://github.com/kraken-php/stream) : Stream wrappers,
* [__Kraken/Supervision__](https://github.com/kraken-php/supervision) : Supervisors and problem solvers,
* [__Kraken/Test__](https://github.com/kraken-php/test) : Test helpers,
* [__Kraken/Throwable__](https://github.com/kraken-php/throwable) : Throwable hierarchy,
* [__Kraken/Util__](https://github.com/kraken-php/util) : Utility classes and methods.

## Performance

Kraken is able to emit millions of events and thousands of messages and connections per second using single container. It is scalable for multiple processes and threads, faster than traditional PHP approach and able to handle same or higher amount of connections that Node.js.

<p align="center">
<img src="https://docs.google.com/uc?export=download&id=0B_FVuB10kPjVT21lY3JzVTRwT3c" width="882" height="334" />
</p>

## Requirements

* PHP-5.5, PHP-5.6 or PHP-7.0+,
* [Pthreads](http://php.net/manual/en/book.pthreads.php) extension enabled (only if you want to use threading),
* UNIX OS.

## Installation and Official Documentation

Documentation for the framework can be found in the [official documentation][2] page. To see installation instructions, 
please check out [application skeleton](https://github.com/kraken-php/kraken) or go to [installation guide][3].

## Examples

There are few examples you can try, before deciding to use Kraken:

- [Distributed Chat Application](https://github.com/kraken-php/demo-chat).

If you have written your own demo application for Kraken, and want to list it here, contact us!

## Frequently Asked Questions

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
already posted in [faq section][4] or ask on our [gitter room][8].


## Contributing

Thank you for considering contributing to Kraken Framework! The contribution guide can be found in the [contribution tips][5].

## License

Kraken Framework is open-sourced software licensed under the [MIT license][6]. The documentation is provided under [FDL-1.3 license][7].

[1]: http://kraken-php.com
[2]: http://kraken-php.com/docs
[3]: http://kraken-php.com/docs/installation
[4]: http://kraken-php.com/faqs
[5]: https://github.com/kraken-php/framework/blob/master/CONTRIBUTING.md
[6]: http://opensource.org/licenses/MIT
[7]: https://www.gnu.org/licenses/fdl-1.3.en.html
[8]: https://gitter.im/kraken-php/framework
