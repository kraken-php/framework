# Contributing

Contributions are **welcome** and accepted via **Pull Requests** on [Github](https://github.com/kraken-php/framework).

## Pull Requests

- **Naming convention** - all pull requests fixing a problem should match "Fix #issue : Message" pattern, the new features and non-fix changes should match "Resolve #issue : Message", the rest should match "Non-issue : Message".
- **Follow our template of code** - all contributions have to follow [PSR-2 coding standard](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md) with an exception of control structures, which have to have opening parenthesis always placed in the next line instead of the same.
- **Add tests** - the contribution won't be accepted if it doesn't have tests.
- **Document any change in behaviour** - make sure the `README.md` and [official documentation](https://github.com/kraken-php/docs) is kept up to date.
- **Create feature branches** - don't create pull requests from your master branch.
- **One pull request per feature** - for multiple things that you want to do, send also multiple pull requests.
- **Keep coherent history** - make sure each individual commit in your pull request is meaningful. If you had to make multiple commits during development cycle, please squash them before submitting.

## Running Tests

```
$> vendor/bin/phpunit
```
