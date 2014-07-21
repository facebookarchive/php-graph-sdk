<?php

require __DIR__ . '/../vendor/autoload.php';

// Because PHP is usually misconfigured on this front.
date_default_timezone_set('UTC');

use Facebook\Exceptions\FacebookSDKException;

if (!file_exists(__DIR__ . '/FacebookTestCredentials.php')) {
  throw new FacebookSDKException(
    'You must create a FacebookTestCredentials.php file from FacebookTestCredentials.php.dist'
  );
}

require __DIR__ . '/FacebookTestCredentials.php';
require __DIR__ . '/FacebookTestHelper.php';

// Create a temp test user to use for testing
FacebookTestHelper::initialize();

// Delete the temp test user after all tests have fired
register_shutdown_function(function ()
{
  FacebookTestHelper::deleteTestUser();
});
