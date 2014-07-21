Facebook SDK for PHP
====================

[![Latest Stable Version](http://img.shields.io/packagist/v/facebook/php-sdk-v4.svg)](https://packagist.org/packages/facebook/php-sdk-v4)


This repository contains the open source PHP SDK that allows you to access Facebook
Platform from your PHP app.


Usage
-----

> **Note:** This version of the Facebook SDK for PHP requires PHP 5.4 or greater.

Minimal example:

```php
use Facebook\Facebook;
use Facebook\Exceptions\FacebookRequestException;
use Facebook\Exceptions\FacebookSDKException;

Facebook::setDefaultApplication('YOUR_APP_ID','YOUR_APP_SECRET');

// Use one of the helper classes to obtain an access token:
//   Facebook\Helpers\FacebookRedirectLoginHelper
//   Facebook\Helpers\FacebookCanvasLoginHelper
//   Facebook\Helpers\FacebookJavaScriptLoginHelper

// Get the Facebook\GraphNodes\GraphUser object for the current user:

try {
  $request = Facebook::newRequest('access-token');
  $me = $request->get('/me')->castAsGraphUser();
  echo $me->getName();
} catch (FacebookRequestException $e) {
  // The Graph API returned an error
} catch (FacebookSDKException $e) {
  // Some other error occurred
}
```

Complete documentation, installation instructions, and examples are available at:
[https://developers.facebook.com/docs/php](https://developers.facebook.com/docs/php)


Tests
-----

1) [Composer](https://getcomposer.org/) is a prerequisite for running the tests.

Install composer globally, then run `composer install` to install required files.

2) Create a test app on [Facebook Developers](https://developers.facebook.com), then
create `tests/FacebookTestCredentials.php` from `tests/FacebookTestCredentials.php.dist`
and edit it to add your credentials.

3) The tests can be executed by running this command from the root directory:

```bash
./vendor/bin/phpunit
```


Contributing
------------

For us to accept contributions you will have to first have signed the
[Contributor License Agreement](https://developers.facebook.com/opensource/cla).

When committing, keep all lines to less than 80 characters, and try to
follow the existing style.

Before creating a pull request, squash your commits into a single commit.

Add the comments where needed, and provide ample explanation in the
commit message.
