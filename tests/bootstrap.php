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

$baseDir = str_replace('/tests', '', __DIR__);
define('APPLICATION_PATH', $baseDir);