<?php

use Facebook\FacebookSession;
use Facebook\FacebookRequest;
use Facebook\FacebookSDKException;

class FacebookTestHelper
{

  public static $testSession;

  public static function setUpBeforeClass()
  {
    if (!strlen(FacebookTestCredentials::$appId) ||
      !strlen(FacebookTestCredentials::$appSecret) ||
      !strlen(FacebookTestCredentials::$appToken)) {
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
      'permissions' => 'read_stream',
      'method' => 'post'
    );
    $response = (new FacebookRequest(
      new FacebookSession(FacebookTestCredentials::$appToken),
      'GET',
      $testUserPath,
      $params))->execute()->getGraphObject();
    return new FacebookSession($response->getProperty('access_token'));
  }

}