# Kraken Roadmap

This file contains the roadmap necessary for developing Kraken Framework. The main purpose of this file is to coordinate work on bigger tasks between community. If you are interested in developing one the tasks in `To Do` section, please, contact [khelle](https://github.com/khelle) directly, inform us in [issues](https://github.com/kraken-php/framework/issues) section or simply create PR for it. 

This file does not contain ALL planned things to do - it only contains the tasks that needs to be done in nearest future.

Help us make PHP a better environment for moder web apps.
 
## Done

- ~~Implement async SSH driver~~ (by [khelle](https://github.com/khelle))
- ~~Add SSL support to sockets~~ (by [hidehalo](https://github.com/hidehalo))

## In Development

- Merge supervision component with runtime - made runtime completely standalone - now it needs tons of boilerplate if someone wants to use it outside of the framework (by [khelle](https://github.com/khelle))
- Throw away current Filesystem (rename to cloud?), implement async Filesystem (by [khelle](https://github.com/khelle))
- Implement async Logger (by [khelle](https://github.com/khelle))
- Implement async Redis driver (by [hidehalo](https://github.com/hidehalo))

## To Do

- Implement async PGSQL driver
- Implement async MySQL driver
- Implement async console driver
- Implement async DNS resolver
- Switch console architecture to connect directly with each container instead of using only root one
- Remove middleman process while creating sub-processes
- Write set of examples for each module in /examples repository
- Write documentation in README of each module
- Improve network component architecture
- Add missing HTTP/1.1 features to network
- Add missing HTTP/2.0 features to network
- Split network component into separate Http and WebSocket components
- Remove Ratchet dependency
- Add missing documentation for symfony integration in kraken-collective
- Add laravel integration in kraken-collective
- Create some bigger than chat application example to show how kraken can be used to make web-apps
- Create set of smaller examples using kraken components without framework