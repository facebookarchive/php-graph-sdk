Contributing
------------

Contributions are **welcome** and will be fully **credited**.

We accept contributions via Pull Requests on [Github](https://github.com/facebook/facebook-php-sdk-v4).


## Pull Requests

- **Sign the CLA** - For us to accept contributions you will have to first have signed the
  [Contributor License Agreement](https://developers.facebook.com/opensource/cla).

- **[PSR-2 Coding Standard](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md)** - The easiest way to apply the conventions is to run [PHP Code Sniffer](#running-php-code-sniffer) as you code.

- **Add tests!** - Your patch won't be accepted if it doesn't have tests.

- **Document any change in behaviour** - Make sure the README and the [documentation](https://github.com/facebook/facebook-php-sdk-v4/tree/master/docs) are kept up-to-date.

- **Consider our release cycle** - As of version 5.0.0, we try to follow [SemVer](http://semver.org/). Randomly breaking public APIs is not an option.

- **Create topic branches** - Don't ask us to pull from your master branch.

- **One pull request per feature** - If you want to do more than one thing, send multiple pull requests.

- **Send coherent history** - Make sure each individual commit in your pull request is meaningful. If you had to make multiple intermediate commits while developing, please squash them before submitting.

- **Ensure tests pass!** - Please [run the tests](#running-tests) before submitting your pull request, and make sure they pass. We won't accept a patch until all tests pass.

- **Ensure no coding standards violations** - Please [run PHP Code Sniffer](#running-php-code-sniffer) using the PSR-2 standard before submitting your pull request. A violation will cause the build to fail, so please make sure there are no violations. We can't accept a patch if the build fails.


## Running Tests

``` bash
$ ./vendor/bin/phpunit
```


## Running PHP Code Sniffer

You can install [PHP Code Sniffer](https://github.com/squizlabs/PHP_CodeSniffer) globally with composer.

``` bash
$ composer global require squizlabs/php_codesniffer
```

Then you can `cd` into the Facebook PHP SDK folder and run Code Sniffer against the `src/` directory.

``` bash
$ ~/.composer/vendor/bin/phpcs
```

**Happy coding**!
