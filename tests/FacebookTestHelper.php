<?php

use Facebook\FacebookSession;
use Facebook\FacebookRequest;
use Facebook\FacebookSDKException;

// Uncomment two lines to force functional test curl implementation
//use Facebook\HttpClients\FacebookCurlHttpClient;
//FacebookRequest::setHttpClientHandler(new FacebookCurlHttpClient());

// Uncomment two lines to force functional test stream wrapper implementation
//use Facebook\HttpClients\FacebookStreamHttpClient;
//FacebookRequest::setHttpClientHandler(new FacebookStreamHttpClient());

// Uncomment two lines to force functional test Guzzle implementation
//use Facebook\HttpClients\FacebookGuzzleHttpClient;
//FacebookRequest::setHttpClientHandler(new FacebookGuzzleHttpClient());

class FacebookTestHelper
{

  public static $testSession;

  public static function setUpBeforeClass()
  {
    if (!strlen(FacebookTestCredentials::$appId) ||
      !strlen(FacebookTestCredentials::$appSecret)) {
      throw new FacebookSDKException(
        'You must fill out FacebookTestCredentials.php'
      );
    }
    FacebookSession::setDefaultApplication(
      FacebookTestCredentials::$appId, FacebookTestCredentials::$appSecret
    );
    if (!(static::$testSession instanceof FacebookSession)) {
      static::$testSession = static::createTestSession();
    }
  }

  public static function setUp()
  {

  }

  public static function tearDownAfterClass()
  {

  }

  public static function tearDown()
  {

  }

  public static function createTestSession()
  {
    $testUserPath = '/' . FacebookTestCredentials::$appId . '/accounts/test-users';
    $params = array(
      'installed' => true,
      'name' => 'PHPUnitTestUser',
      'locale' => 'en_US',
      'permissions' => 'read_stream, user_photos',
      'method' => 'post'
    );
    $response = (new FacebookRequest(
      new FacebookSession(FacebookTestHelper::getAppToken()),
      'GET',
      $testUserPath,
      $params))->execute()->getGraphObject();
    return new FacebookSession($response->getProperty('access_token'));
  }

  public static function getAppToken()
  {
    return FacebookTestCredentials::$appId . '|' . FacebookTestCredentials::$appSecret;
  }

}