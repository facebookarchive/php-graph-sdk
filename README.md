# Facebook SDK for PHP (v6)

## NOTICE: This branch is under active development. For the stable release please use the [5.x branch](https://github.com/facebook/php-graph-sdk/tree/5.x).

[![Build Status](https://img.shields.io/travis/facebook/php-graph-sdk/master.svg)](https://travis-ci.org/facebook/php-graph-sdk)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/facebook/php-graph-sdk/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/facebook/php-graph-sdk/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/facebook/php-graph-sdk/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/facebook/php-graph-sdk/?branch=master)
[![Development Version](http://img.shields.io/badge/Development%20Version-v6.0-orange.svg)](https://packagist.org/packages/facebook/graph-sdk)


This repository contains the open source PHP SDK that allows you to access the Facebook Platform from your PHP app.


## Installation

The Facebook PHP SDK can be installed with [Composer](https://getcomposer.org/). Run this command:

```sh
composer require facebook/graph-sdk php-http/curl-client guzzlehttp/psr7
```

Why the extra packages? We give you the flexibility to choose what HTTP client (e.g. cURL or Guzzle) to use and what PSR-7 implementation you prefer. Read more about this at the [HTTPlug documentation](http://php-http.readthedocs.io/en/latest/httplug/users.html).


## Usage

> **Note:** This version of the Facebook SDK for PHP requires PHP 5.6 or greater.

Simple GET example of a user's profile.

```php
require_once __DIR__ . '/vendor/autoload.php'; // change path as needed

$fb = new \Facebook\Facebook([
  'app_id' => '{app-id}',
  'app_secret' => '{app-secret}',
  'default_graph_version' => 'v2.10',
  //'default_access_token' => '{access-token}', // optional
]);

// Use one of the helper classes to get a Facebook\Authentication\AccessToken entity.
//   $helper = $fb->getRedirectLoginHelper();
//   $helper = $fb->getJavaScriptHelper();
//   $helper = $fb->getCanvasHelper();
//   $helper = $fb->getPageTabHelper();

try {
  // Get the \Facebook\GraphNode\GraphUser object for the current user.
  // If you provided a 'default_access_token', the '{access-token}' is optional.
  $response = $fb->get('/me', '{access-token}');
} catch(\Facebook\Exception\FacebookResponseException $e) {
  // When Graph returns an error
  echo 'Graph returned an error: ' . $e->getMessage();
  exit;
} catch(\Facebook\Exception\FacebookSDKException $e) {
  // When validation fails or other local issues
  echo 'Facebook SDK returned an error: ' . $e->getMessage();
  exit;
}

$me = $response->getGraphUser();
echo 'Logged in as ' . $me->getName();
```

Complete documentation, installation instructions, and examples are available [here](docs/).


## Tests

1. [Composer](https://getcomposer.org/) is a prerequisite for running the tests. Install composer globally, then run `composer install` to install required files.
2. Create a test app on [Facebook Developers](https://developers.facebook.com), then create `tests/FacebookTestCredentials.php` from `tests/FacebookTestCredentials.php.dist` and edit it to add your credentials.
3. The tests can be executed by running this command from the root directory:

```bash
$ ./vendor/bin/phpunit
```

By default the tests will send live HTTP requests to the Graph API. If you are without an internet connection you can skip these tests by excluding the `integration` group.

```bash
$ ./vendor/bin/phpunit --exclude-group integration
```


## Contributing

For us to accept contributions you will have to first have signed the [Contributor License Agreement](https://developers.facebook.com/opensource/cla). Please see [CONTRIBUTING](https://github.com/facebook/php-graph-sdk/blob/master/CONTRIBUTING.md) for details.


## License

Please see the [license file](https://github.com/facebook/php-graph-sdk/blob/master/LICENSE) for more information.


## Security Vulnerabilities

If you have found a security issue, please contact the maintainers directly at [me@sammyk.me](mailto:me@sammyk.me).
