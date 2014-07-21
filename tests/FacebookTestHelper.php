<?php

use Facebook\Facebook;
use Facebook\Http\BaseRequest;
use Facebook\Exceptions\FacebookSDKException;
use Facebook\Http\Clients\FacebookCurlHttpClient;
use Facebook\Http\Clients\FacebookStreamHttpClient;
use Facebook\Http\Clients\FacebookGuzzleHttpClient;

class FacebookTestHelper
{

  public static $testUserId;
  public static $testUserAccessToken;
  public static $testUserPermissions = ['read_stream', 'user_photos'];

  public static function initialize()
  {
    if (!strlen(FacebookTestCredentials::$appId) ||
      !strlen(FacebookTestCredentials::$appSecret)) {
      throw new FacebookSDKException(
        'You must fill out FacebookTestCredentials.php'
      );
    }
    static::createTestUserAndGetAccessToken();
  }

  public static function resetTestCredentials()
  {
    $httpHandler = BaseRequest::detectHttpClientHandler();
    BaseRequest::setHttpClientHandler($httpHandler);

    // Uncomment to force functional test curl implementation
    //BaseRequest::setHttpClientHandler(new FacebookCurlHttpClient());

    // Uncomment to force functional test stream wrapper implementation
    //BaseRequest::setHttpClientHandler(new FacebookStreamHttpClient());

    // Uncomment to force functional test Guzzle implementation
    //BaseRequest::setHttpClientHandler(new FacebookGuzzleHttpClient());

    Facebook::setDefaultApplication(
            FacebookTestCredentials::$appId, FacebookTestCredentials::$appSecret
    );
  }

  public static function createTestUserAndGetAccessToken()
  {
    static::resetTestCredentials();

    $testUserPath = '/' . FacebookTestCredentials::$appId . '/accounts/test-users';
    $params = [
      'installed' => true,
      'name' => 'Foo Phpunit User',
      'locale' => 'en_US',
      'permissions' => implode(',', static::$testUserPermissions),
    ];
    $appAccessToken = Facebook::getAppAccessToken();

    $graphObject = Facebook::newRequest($appAccessToken)->post($testUserPath, $params);

    static::$testUserId = $graphObject->getId();
    static::$testUserAccessToken = $graphObject->getAccessToken();
  }

  public static function deleteTestUser()
  {
    if (!static::$testUserId) {
      return;
    }
    static::resetTestCredentials();

    $testUserPath = '/' . static::$testUserId;
    $appAccessToken = Facebook::getAppAccessToken();

    Facebook::newRequest($appAccessToken)->delete($testUserPath);

    //echo "\nTotal requests made to Graph: " . BaseRequest::$requestCount . "\n\n";
  }

}
