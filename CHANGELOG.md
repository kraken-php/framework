# Release Notes

This changelog references the relevant changes, bug and security fixes done.

## v0.3.2 (2016-10-13)

### Fixes

- Changed include method of external PHP scripts to not require allow_url_include flag ([#41](https://github.com/kraken-php/framework/pull/41)).
- Fixed the wrong behaviour of checkOrigin flag in WsRouter ([#46](https://github.com/kraken-php/framework/issues/46)).

## v0.3.1 (2016-09-30)

### Additions

- Added universal namespace for creating container definitions which do not care whether they are processes or threads ([#31](https://github.com/kraken-php/framework/issues/31)).
- Added possibility of creating universal bootstrap files.
- Added possibility of creating universal config files.
- Added `sendCommand` method to each class implementing `Kraken\Runtime\RuntimeManagerInterface` ([#34](https://github.com/kraken-php/framework/issues/34)).
- Created `FilesystemFactory` in `Kraken\Filesystem` namespace ([#29](https://github.com/kraken-php/framework/issues/29)).

### Fixes

- Fixed an issue of WsServer not working correctly in pair with NetworkServer ([#36](https://github.com/kraken-php/framework/issues/36)).
- Fixed an issue of `Kraken\Ipc\Socket\SocketListener` not being able to reuse unix ports ([#35](https://github.com/kraken-php/framework/issues/35)).
- Fixed the major issue of container being able to process wrong instructions in failed state ([#33](https://github.com/kraken-php/framework/issues/33)).
- Fixed an issue of threads not being able to create log files ([#32](https://github.com/kraken-php/framework/issues/32)).

### Miscellaneous

- Removed unused `kraken` and `kraken.server` scripts.
- Refactored process isolation mechanisms.
- Renamed environmental file a `ConfigProvider` searches for, from `data/config.env` to `data/environment`.
- Added missing replace `kraken-php/test` item in composer.json.
- Renamed `EventHandler` to `EventListener` to keep implementation consistent with theory ([#28](https://github.com/kraken-php/framework/issues/28)).
- Added missing `symfony/routing` dependency ([#26](https://github.com/kraken-php/framework/issues/26)).
- Refactored `Kraken\Promise` namespace.
- Made supervision solver names for escalating problems more meaningful ([#27](https://github.com/kraken-php/framework/issues/27)).

## v0.3.0 (2016-09-16)

- Initial release of publicly accessible version of Kraken