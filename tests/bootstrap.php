<?php
/**
 * Copyright 2014 Facebook, Inc.
 *
 * You are hereby granted a non-exclusive, worldwide, royalty-free license to
 * use, copy, modify, and distribute this software in source code or binary
 * form for use in connection with the web services and APIs provided by
 * Facebook.
 *
 * As with any software that integrates with the Facebook platform, your use
 * of this software is subject to the Facebook Developer Principles and
 * Policies [http://developers.facebook.com/policy/]. This copyright notice
 * shall be included in all copies or substantial portions of the software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
 * DEALINGS IN THE SOFTWARE.
 *
 */
require_once __DIR__ . '/../vendor/autoload.php';

use Facebook\FacebookSDKException;
use Facebook\Tests\FacebookTestHelper;

if (!file_exists(__DIR__ . '/FacebookTestCredentials.php')) {
  throw new FacebookSDKException(
    'You must create a FacebookTestCredentials.php file from FacebookTestCredentials.php.dist'
  );
}

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
