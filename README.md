Facebook SDK for PHP
====================

[![Development Version](http://img.shields.io/badge/Development%20Version-4.1.0-orange.svg)](https://packagist.org/packages/facebook/php-sdk-v4)


This repository contains the open source PHP SDK that allows you to access Facebook
Platform from your PHP app.


Usage
-----

> **Note:** This version of the Facebook SDK for PHP requires PHP 5.4 or greater.

Simple GET example of a user's profile.

```php
use Facebook\Entities\FacebookApp;
use Facebook\Entities\FacebookRequest;
use Facebook\FacebookClient;
use Facebook\GraphNodes\GraphUser;
use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;

$facebookApp = new FacebookApp('{app-id}', '{app-secret}');

// Use one of the helper classes to get a Facebook\Entities\AccessToken entity.
//   Facebook\Helpers\FacebookRedirectLoginHelper
//   Facebook\Helpers\FacebookJavaScriptLoginHelper
//   Facebook\Helpers\FacebookCanvasLoginHelper
//   Facebook\Helpers\FacebookPageTabHelper

// Get the Facebook\GraphNodes\GraphUser object for the current user:
$facebookClient = new FacebookClient();
$request = new FacebookRequest($facebookApp, '{access-token}', 'GET', '/me');

try {
  $facebookResponse = $facebookClient->sendRequest($request);
  $me = $facebookResponse->getGraphObject(GraphUser::className());
  echo 'Logged in as ' . $me->getName();
} catch(FacebookResponseException $e) {
  // When Graph returns an error
  echo 'Graph returned an error: ' . $e->getMessage();
} catch(FacebookSDKException $e) {
  // When validation fails or other local issues
  echo 'Facebook SDK returned an error: ' . $e->getMessage();
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
