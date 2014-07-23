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
namespace Facebook\Tests;

use Facebook\Tests\FacebookTestCredentials;
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
