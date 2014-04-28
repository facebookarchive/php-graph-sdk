<?php

//namespace Facebook;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/FacebookTestHelper.php';

$baseDir = str_replace('/tests', '', __DIR__);
define('APPLICATION_PATH', $baseDir);