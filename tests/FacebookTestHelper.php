<?php

use Facebook\FacebookSession;
use Facebook\FacebookRequest;
use Facebook\FacebookSDKException;

class FacebookTestHelper
{

  public static $testSession;
  public static $testUserId;
  public static $testUserAccessToken;
  public static $testUserPermissions = array('read_stream', 'user_photos');

  public static function initialize()
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
    if (!static::$testSession instanceof FacebookSession) {
      static::$testSession = static::createTestSession();
    }
  }

  public static function createTestSession()
  {
    static::createTestUserAndGetAccessToken();
    return new FacebookSession(static::$testUserAccessToken);
  }

  public static function createTestUserAndGetAccessToken()
  {
    $testUserPath = '/' . FacebookTestCredentials::$appId . '/accounts/test-users';
    $params = array(
      'installed' => true,
      'name' => 'Foo Phpunit User',
      'locale' => 'en_US',
      'permissions' => implode(',', static::$testUserPermissions),
    );

    $request = new FacebookRequest(static::getAppSession(), 'POST', $testUserPath, $params);
    $response = $request->execute()->getGraphObject();

    static::$testUserId = $response->getProperty('id');
    static::$testUserAccessToken = $response->getProperty('access_token');
  }

  public static function getAppSession()
  {
    return new FacebookSession(static::getAppToken());
  }

  public static function getAppToken()
  {
    return FacebookTestCredentials::$appId . '|' . FacebookTestCredentials::$appSecret;
  }

  public static function deleteTestUser()
  {
    if (!static::$testUserId) {
      return;
    }
    $testUserPath = '/' . static::$testUserId;
    $request = new FacebookRequest(static::getAppSession(), 'DELETE', $testUserPath);
    $request->execute();
  }

}
