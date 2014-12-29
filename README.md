Facebook SDK for PHP
====================

[![Latest Stable Version](http://img.shields.io/badge/Latest%20Stable-4.0.14-blue.svg)](https://packagist.org/packages/facebook/php-sdk-v4)


This repository contains the open source PHP SDK that allows you to access Facebook
Platform from your PHP app.


Usage
-----

This version of the Facebook SDK for PHP requires PHP 5.4 or greater.

Minimal example:

```php
<?php

// Skip these two lines if you're using Composer
define('FACEBOOK_SDK_V4_SRC_DIR', '/path/to/facebook-php-sdk-v4/src/Facebook/');
require __DIR__ . '/path/to/facebook-php-sdk-v4/autoload.php';

use Facebook\FacebookSession;
use Facebook\FacebookRequest;
use Facebook\GraphUser;
use Facebook\FacebookRequestException;

FacebookSession::setDefaultApplication('YOUR_APP_ID','YOUR_APP_SECRET');

// Use one of the helper classes to get a FacebookSession object.
//   FacebookRedirectLoginHelper
//   FacebookCanvasLoginHelper
//   FacebookJavaScriptLoginHelper
// or create a FacebookSession with a valid access token:
$session = new FacebookSession('access-token-here');

// Get the GraphUser object for the current user:

try {
  $me = (new FacebookRequest(
    $session, 'GET', '/me'
  ))->execute()->getGraphObject(GraphUser::className());
  echo $me->getName();
} catch (FacebookRequestException $e) {
  // The Graph API returned an error
} catch (\Exception $e) {
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
