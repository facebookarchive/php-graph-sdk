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

use Facebook\Entities\SignedRequest;

/**
 * Class FacebookSession
 * @package Facebook
 * @author Fosco Marotto <fjm@fb.com>
 * @author David Poll <depoll@fb.com>
 */
class FacebookSession
{

  /**
   * @var string
   */
  private static $defaultAppId;

  /**
   * @var string
   */
  private static $defaultAppSecret;

  /**
   * @var string The token string for the session
   */
  private $token;

  /**
   * @var SignedRequest
   */
  private $signedRequest;

  /**
   * @var bool
   */
  private static $useAppSecretProof = true;

  /**
   * When creating a Session from an access_token, use:
   *   var $session = new FacebookSession($accessToken);
   * This will validate the token and provide a Session object ready for use.
   * It will throw a SessionException in case of error.
   *
   * @param string $accessToken
   * @param SignedRequest $signedRequest The SignedRequest entity
   */
  public function __construct($accessToken, SignedRequest $signedRequest = null)
  {
    $this->token = $accessToken;
    $this->signedRequest = $signedRequest;
  }

  /**
   * Returns the access token
   *
   * @return string
   */
  public function getToken()
  {
    return $this->token;
  }

  /**
   * Returns the SignedRequest entity.
   *
   * @return SignedRequest
   */
  public function getSignedRequest()
  {
    return $this->signedRequest;
  }

  /**
   * Returns the signed request payload.
   *
   * @return null|array
   */
  public function getSignedRequestData()
  {
    return $this->signedRequest ? $this->signedRequest->getPayload() : null;
  }

  /**
   * Returns a property from the signed request data if available.
   *
   * @param string $key
   *
   * @return null|mixed
   */
  public function getSignedRequestProperty($key)
  {
    return $this->signedRequest ? $this->signedRequest->get($key) : null;
  }

  /**
   * Returns user_id from signed request data if available.
   *
   * @return null|string
   */
  public function getUserId()
  {
    return $this->signedRequest ? $this->signedRequest->getUserId() : null;
  }

  /**
   * getSessionInfo - Makes a request to /debug_token with the appropriate
   *   arguments to get debug information about the sessions token.
   *
   * @param string|null $appId
   * @param string|null $appSecret
   *
   * @return GraphSessionInfo
   */
  public function getSessionInfo($appId = null, $appSecret = null)
  {
    return (new FacebookRequest(
      static::newAppSession($appId, $appSecret),
      'GET',
      '/debug_token',
      array(
        'input_token' => $this->getToken(),
      )
    ))->execute()->getGraphObject(GraphSessionInfo::className());
  }

  /**
   * getLongLivedSession - Returns a new Facebook session resulting from
   *   extending a short-lived access token.  If this session is not
   *   short-lived, returns $this.
   *
   * @param string|null $appId
   * @param string|null $appSecret
   *
   * @return FacebookSession
   */
  public function getLongLivedSession($appId = null, $appSecret = null)
  {
    $targetAppId = static::_getTargetAppId($appId);
    $targetAppSecret = static::_getTargetAppSecret($appSecret);
    $params = array(
      'client_id' => $targetAppId,
      'client_secret' => $targetAppSecret,
      'grant_type' => 'fb_exchange_token',
      'fb_exchange_token' => $this->getToken()
    );
    // The response for this endpoint is not JSON, so it must be handled
    //   differently, not as a GraphObject.
    $response = (new FacebookRequest(
      self::newAppSession($targetAppId, $targetAppSecret),
      'GET',
      '/oauth/access_token',
      $params
    ))->execute()->getResponse();
    if ($response) {
      return new FacebookSession($response['access_token']);
    } else {
      return $this;
    }
  }

  /**
   * getExchangeToken - Returns an exchange token string which can be sent
   *   back to clients and exchanged for a device-linked access token.
   *
   * @param string|null $appId
   * @param string|null $appSecret
   *
   * @return string
   */
  public function getExchangeToken($appId = null, $appSecret = null)
  {
    $targetAppId = static::_getTargetAppId($appId);
    $targetAppSecret = static::_getTargetAppSecret($appSecret);
    // Redirect URI is being removed as a requirement.  Passing an empty string.
    $params = array(
      'client_id' => $targetAppId,
      'access_token' => $this->getToken(),
      'client_secret' => $targetAppSecret,
      'redirect_uri' => ''
    );
    $response = (new FacebookRequest(
      self::newAppSession($targetAppId, $targetAppSecret),
      'GET',
      '/oauth/client_code',
      $params
    ))->execute()->getGraphObject();
    return $response->getProperty('code');
  }

  /**
   * validate - Ensures the current session is valid, throwing an exception if
   *   not.  Fetches token info from Facebook.
   *
   * @param string|null $appId Application ID to use
   * @param string|null $appSecret App secret value to use
   *
   * @return boolean
   */
  public function validate($appId = null, $appSecret = null)
  {
    $targetAppId = static::_getTargetAppId($appId);
    $targetAppSecret = static::_getTargetAppSecret($appSecret);
    $info = $this->getSessionInfo($targetAppId, $targetAppSecret);
    return self::validateSessionInfo($info, $targetAppId);
  }

  /**
   * validateTokenInfo - Ensures the provided GraphSessionInfo object is valid,
   *   throwing an exception if not.  Ensures the appId matches,
   *   that the token is valid and has not expired.
   *
   * @param GraphSessionInfo $tokenInfo
   * @param string|null $appId Application ID to use
   *
   * @return boolean
   *
   * @throws FacebookSDKException
   */
  public static function validateSessionInfo(GraphSessionInfo $tokenInfo,
                                           $appId = null)
  {
    $targetAppId = static::_getTargetAppId($appId);
    if ($tokenInfo->getAppId() !== $targetAppId
      || !$tokenInfo->isValid()
      || (
        $tokenInfo->getExpiresAt() !== null
        && $tokenInfo->getExpiresAt()->getTimestamp() < time()
        )
      ) {
      throw new FacebookSDKException(
        'Session has expired, or is not valid for this app.', 601
      );
    }
    return true;
  }

  /**
   * newSessionFromSignedRequest - Returns a FacebookSession for a
   *   given signed request.
   *
   * @param SignedRequest $signedRequest
   *
   * @return FacebookSession
   */
  public static function newSessionFromSignedRequest(SignedRequest $signedRequest)
  {
    if ($signedRequest->get('code')
      && !$signedRequest->get('oauth_token')) {
      return self::newSessionAfterValidation($signedRequest);
    }
    return new static($signedRequest->get('oauth_token'), $signedRequest);
  }

  /**
   * newSessionAfterValidation - Returns a FacebookSession for a
   *   validated & parsed signed request.
   *
   * @param SignedRequest $signedRequest
   *
   * @return FacebookSession
   *
   * @throws FacebookRequestException
   */
  protected static function newSessionAfterValidation(SignedRequest $signedRequest)
  {
    $params = array(
      'client_id' => self::$defaultAppId,
      'redirect_uri' => '',
      'client_secret' => self::$defaultAppSecret,
      'code' => $signedRequest->get('code'),
    );
    $response = (new FacebookRequest(
      self::newAppSession(),
      'GET',
      '/oauth/access_token',
      $params
    ))->execute()->getResponse();
    if (isset($response['access_token'])) {
      return new static($response['access_token'], $signedRequest);
    }
    throw FacebookRequestException::create(
      json_encode($signedRequest->getRawSignedRequest()),
      $signedRequest->getPayload(),
      401
    );
  }

  /**
   * newAppSession - Returns a FacebookSession configured with a token for the
   *   application which can be used for publishing and requesting app-level
   *   information.
   *
   * @param string|null $appId Application ID to use
   * @param string|null $appSecret App secret value to use
   *
   * @return FacebookSession
   */
  public static function newAppSession($appId = null, $appSecret = null)
  {
    $targetAppId = static::_getTargetAppId($appId);
    $targetAppSecret = static::_getTargetAppSecret($appSecret);
    return new FacebookSession(
      $targetAppId . '|' . $targetAppSecret
    );
  }

  /**
   * setDefaultApplication - Will set the static default appId and appSecret
   *   to be used for API requests.
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
   * _getTargetAppId - Will return either the provided app Id or the default,
   *   throwing if neither are populated.
   *
   * @param string $appId
   *
   * @return string
   *
   * @throws FacebookSDKException
   */
  public static function _getTargetAppId($appId = null) {
    $target = ($appId ?: self::$defaultAppId);
    if (!$target) {
      throw new FacebookSDKException(
        'You must provide or set a default application id.', 700
      );
    }
    return $target;
  }

  /**
   * _getTargetAppSecret - Will return either the provided app secret or the
   *   default, throwing if neither are populated.
   *
   * @param string $appSecret
   *
   * @return string
   *
   * @throws FacebookSDKException
   */
  public static function _getTargetAppSecret($appSecret = null) {
    $target = ($appSecret ?: self::$defaultAppSecret);
    if (!$target) {
      throw new FacebookSDKException(
        'You must provide or set a default application secret.', 701
      );
    }
    return $target;
  }

  /**
   * Enable or disable sending the appsecret_proof with requests.
   *
   * @param bool $on
   */
  public static function enableAppSecretProof($on = true)
  {
    static::$useAppSecretProof = ($on ? true : false);
  }

  /**
   * Get whether or not appsecret_proof should be sent with requests.
   *
   * @return bool
   */
  public static function useAppSecretProof()
  {
    return static::$useAppSecretProof;
  }

}
