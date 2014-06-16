<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Facebook\FacebookSDKException;

if (!file_exists(__DIR__ . '/FacebookTestCredentials.php')) {
  throw new FacebookSDKException(
    'You must create a FacebookTestCredentials.php file from FacebookTestCredentials.php.dist'
  );
}

require_once __DIR__ . '/FacebookTestCredentials.php';
require_once __DIR__ . '/FacebookTestHelper.php';

// Uncomment two lines to force functional test curl implementation
//use Facebook\HttpClients\FacebookCurlHttpClient;
//FacebookRequest::setHttpClientHandler(new FacebookCurlHttpClient());

// Uncomment two lines to force functional test stream wrapper implementation
//use Facebook\HttpClients\FacebookStreamHttpClient;
//FacebookRequest::setHttpClientHandler(new FacebookStreamHttpClient());

// Uncomment two lines to force functional test Guzzle implementation
//use Facebook\HttpClients\FacebookGuzzleHttpClient;
//FacebookRequest::setHttpClientHandler(new FacebookGuzzleHttpClient());

// Create a temp test user to use for testing
FacebookTestHelper::initialize();

// Delete the temp test user after all tests have fired
register_shutdown_function(function ()
{
  FacebookTestHelper::deleteTestUser();
});
