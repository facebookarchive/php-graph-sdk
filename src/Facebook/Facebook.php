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
namespace Facebook;

use Facebook\Entities\AccessToken;
use Facebook\Http\BaseRequest;
use Facebook\Http\FacebookRequest;
use Facebook\Http\FacebookBatchRequest;
use Facebook\Exceptions\FacebookSDKException;

/**
 * Class Facebook
 * @package Facebook
 */
class Facebook
{

  /**
   * @const string Version number of the Facebook PHP SDK.
   */
  const VERSION = '4.1.0';

  /**
   * @var string Default Graph API version for requests
   */
  private static $defaultGraphApiVersion = 'v2.0';

  /**
   * @var string The default app ID.
   */
  private static $defaultAppId;

  /**
   * @var string The default app secret.
   */
  private static $defaultAppSecret;

  /**
   * @var AccessToken The default access token.
   */
  private static $defaultAccessToken;

  /**
   * Factory for creating a new request.
   *
   * @param AccessToken|string Access token to use with the request.
   *
   * @return FacebookRequest
   */
  public static function newRequest($accessToken = null)
  {
    $httpClient = BaseRequest::getHttpClientHandler();
    $facebookRequest = new FacebookRequest($httpClient);
    $facebookRequest->newRequest($accessToken);
    return $facebookRequest;
  }

  /**
   * Factory for creating a new batch request.
   *
   * @param AccessToken|string Access token to use with the request.
   *
   * @return FacebookBatchRequest
   */
  public static function newBatchRequest($accessToken = null)
  {
    $httpClient = BaseRequest::getHttpClientHandler();
    $facebookRequest = new FacebookBatchRequest($httpClient);
    $facebookRequest->setBatchRequestAccessToken($accessToken);
    return $facebookRequest;
  }

  /**
   * Sets the default appId and appSecret to be used for API requests.
   *
   * @param string $appId Application ID to use by default
   * @param string $appSecret App secret value to use by default
   */
  public static function setDefaultApplication($appId, $appSecret)
  {
    self::$defaultAppId = $appId;
    self::$defaultAppSecret = $appSecret;
  }

  /**
   * Creates an app access token.
   *
   * @param string|null $appId Application ID to use
   * @param string|null $appSecret App secret value to use
   *
   * @return AccessToken
   */
  public static function getAppAccessToken($appId = null, $appSecret = null)
  {
    $targetAppId = static::getAppId($appId);
    $targetAppSecret = static::getAppSecret($appSecret);
    return new AccessToken($targetAppId.'|'.$targetAppSecret);
  }

  /**
   * Returns either the provided app ID or falls back to defaults.
   * Will throw if the app ID could not be determined.
   *
   * @param string $appId
   *
   * @return string
   *
   * @throws FacebookSDKException
   */
  public static function getAppId($appId = null) {
    $id = $appId ?: self::$defaultAppId;
    if (!$id) {
      $id = getenv('FACEBOOK_APP_ID');
    }
    if (!$id) {
      throw new FacebookSDKException(
        'You must provide a default application id.', 700
      );
    }
    return $id;
  }

  /**
   * Returns either the provided app secret or falls back to defaults.
   * Will throw if the app secret could not be determined.
   *
   * @param string $appSecret
   *
   * @return string
   *
   * @throws FacebookSDKException
   */
  public static function getAppSecret($appSecret = null) {
    $secret = $appSecret ?: self::$defaultAppSecret;
    if (!$secret) {
      $secret = getenv('FACEBOOK_APP_SECRET');
    }
    if (!$secret) {
      throw new FacebookSDKException(
        'You must provide a default application secret.', 701
      );
    }
    return $secret;
  }

  /**
   * Returns the default Graph version.
   *
   * @return string
   */
  public static function getDefaultGraphApiVersion() {
    return static::$defaultGraphApiVersion;
  }

  /**
   * Sets the default Graph API version.
   *
   * @param string $graphApiVersion
   */
  public static function setDefaultGraphApiVersion($graphApiVersion)
  {
    static::$defaultGraphApiVersion = $graphApiVersion;
  }

  /**
   * Returns the default access token.
   *
   * @param AccessToken|string|null $accessToken
   *
   * @return AccessToken|null
   */
  public static function getAccessToken($accessToken = null) {
    $accessToken = $accessToken ?: static::$defaultAccessToken;
    if (!$accessToken) {
      return null;
    }
    return $accessToken instanceof AccessToken
      ? $accessToken
      : new AccessToken($accessToken);
  }

  /**
   * Sets the default access token to use with all calls to Graph.
   *
   * @param AccessToken|string|null $accessToken
   */
  public static function setDefaultAccessToken($accessToken)
  {
    if (!$accessToken) {
      static::$defaultAccessToken = null;
      return;
    }
    static::$defaultAccessToken = $accessToken instanceof AccessToken
      ? $accessToken
      : new AccessToken($accessToken);
  }

  /**
   * Return the default headers that every request should use.
   *
   * @return array
   */
  public static function getDefaultHeaders()
  {
    return [
      'User-Agent' => 'fb-php-'.static::VERSION,
      'Accept-Encoding' => '*',
    ];
  }

}
