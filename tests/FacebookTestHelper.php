<?php

use Facebook\FacebookSession;
use Facebook\FacebookRequest;
use Facebook\FacebookSDKException;
use Facebook\FacebookContainer;
use Facebook\FacebookPersistable;

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
    FacebookContainer::setPersistentDataHandler(
      new FacebookPersistentDataHandlerTestHelper()
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

/**
 * Class FacebookTestPersistentDataHandler
 * An in-memory persistent data handler for testing
 */
class FacebookPersistentDataHandlerTestHelper implements FacebookPersistable
{

  protected $session = array();

  public function setPersistentData($key, $value)
  {
    $this->session[$key] = $value;
  }

  public function getPersistentData($key, $default = null)
  {
    if (isset($this->session[$key])) {
      return $this->session[$key];
    }

    return $default;
  }

}