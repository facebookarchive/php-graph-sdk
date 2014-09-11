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
namespace Facebook\Entities;

use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;
use Facebook\FacebookClient;
use Facebook\GraphNodes\GraphSessionInfo;

/**
 * Class AccessToken
 * @package Facebook
 */
class AccessToken implements \Serializable
{

  /**
   * The access token.
   *
   * @var string
   */
  protected $accessToken = '';

  /**
   * A unique ID to identify a client.
   *
   * @var string|null
   */
  protected $machineId;

  /**
   * Date when token expires.
   *
   * @var \DateTime|null
   */
  protected $expiresAt;

  /**
   * Create a new access token entity.
   *
   * @param string $accessToken
   * @param int $expiresAt
   * @param string|null machineId
   */
  public function __construct($accessToken, $expiresAt = 0, $machineId = null)
  {
    $this->accessToken = $accessToken;
    if ($expiresAt) {
      $this->setExpiresAtFromTimeStamp($expiresAt);
    }
    $this->machineId = $machineId;
  }

  /**
   * Setter for expires_at.
   *
   * @param int $timeStamp
   */
  protected function setExpiresAtFromTimeStamp($timeStamp)
  {
    $dt = new \DateTime();
    $dt->setTimestamp($timeStamp);
    $this->expiresAt = $dt;
  }

  /**
   * Getter for expiresAt.
   *
   * @return \DateTime|null
   */
  public function getExpiresAt()
  {
    return $this->expiresAt;
  }

  /**
   * Getter for machineId.
   *
   * @return string|null
   */
  public function getMachineId()
  {
    return $this->machineId;
  }

  /**
   * Determines whether or not this is a long-lived token.
   *
   * @return bool
   */
  public function isLongLived()
  {
    if ($this->expiresAt) {
      return $this->expiresAt->getTimestamp() > time() + (60 * 60 * 2);
    }
    return false;
  }

  /**
   * Checks the expiration of the access token.
   *
   * @return boolean|null
   */
  public function isExpired()
  {
    if ($this->getExpiresAt() instanceof \DateTime) {
      return $this->getExpiresAt()->getTimestamp() < time();
    }

    // Not all access tokens return an expiration. E.g. an app access token.
    return false;
  }

  /**
   * Checks the validity of the access token.
   *
   * @param FacebookApp $app The FacebookApp entity.
   * @param FacebookClient $client The Facebook client.
   * @param string|null $machineId Unique client identifier.
   *
   * @return boolean
   */
  public function isValid(FacebookApp $app, FacebookClient $client, $machineId = null)
  {
    $accessTokenInfo = $this->getInfo($app, $client);
    $machineId = $machineId ?: $this->machineId;
    return static::validateAccessToken($accessTokenInfo, $app, $machineId);
  }

  /**
   * Ensures the provided GraphSessionInfo object is valid,
   *   throwing an exception if not.  Ensures the appId matches,
   *   that the machineId matches if it's being used,
   *   that the token is valid and has not expired.
   *
   * @param GraphSessionInfo $tokenInfo
   * @param FacebookApp $app The FacebookApp entity.
   * @param string|null $machineId
   *
   * @return boolean
   */
  public static function validateAccessToken(GraphSessionInfo $tokenInfo,
                                             FacebookApp $app,
                                             $machineId = null)
  {
    $appIdIsValid = $tokenInfo->getProperty('app_id') == $app->getId();
    $machineIdIsValid = $tokenInfo->getProperty('machine_id') == $machineId;
    $accessTokenIsValid = $tokenInfo->getIsValid();

    // Not all access tokens return an expiration. E.g. an app access token.
    if ($tokenInfo->getExpiresAt() instanceof \DateTime) {
      $accessTokenIsStillAlive = $tokenInfo->getExpiresAt()->getTimestamp() >= time();
    } else {
      $accessTokenIsStillAlive = true;
    }

    return $appIdIsValid && $machineIdIsValid && $accessTokenIsValid && $accessTokenIsStillAlive;
  }

  /**
   * Get a valid access token from a code.
   *
   * @param string $code
   * @param FacebookApp $app The FacebookApp entity.
   * @param FacebookClient $client The Facebook client.
   * @param string|null $redirectUri
   * @param string|null $machineId
   *
   * @return AccessToken
   *
   * @throws FacebookSDKException
   */
  public static function getAccessTokenFromCode($code,
                                                FacebookApp $app,
                                                FacebookClient $client,
                                                $redirectUri = '',
                                                $machineId = null)
  {
    $params = [
      'code' => $code,
      'redirect_uri' => $redirectUri,
    ];

    if ($machineId) {
      $params['machine_id'] = $machineId;
    }

    return static::requestAccessToken($params, $app, $client);
  }

  /**
   * Get a valid code from an access token.
   *
   * @param AccessToken|string $accessToken
   * @param FacebookApp $app The FacebookApp entity.
   * @param FacebookClient $client The Facebook client.
   * @param string|null $redirectUri
   *
   * @return AccessToken
   *
   * @throws FacebookSDKException
   */
  public static function getCodeFromAccessToken($accessToken,
                                                FacebookApp $app,
                                                FacebookClient $client,
                                                $redirectUri = '')
  {
    $accessToken = (string) $accessToken;

    $params = [
      'access_token' => $accessToken,
      'redirect_uri' => $redirectUri,
    ];

    return static::requestCode($params, $app, $client);
  }

  /**
   * Exchanges a short lived access token with a long lived access token.
   *
   * @param FacebookApp $app The FacebookApp entity.
   * @param FacebookClient $client The Facebook client.
   *
   * @return AccessToken
   *
   * @throws FacebookSDKException
   */
  public function extend(FacebookApp $app, FacebookClient $client)
  {
    $params = [
      'grant_type' => 'fb_exchange_token',
      'fb_exchange_token' => $this->accessToken,
    ];

    return static::requestAccessToken($params, $app, $client);
  }

  /**
   * Request an access token based on a set of params.
   *
   * @param array $params
   * @param FacebookApp $app The FacebookApp entity.
   * @param FacebookClient $client The Facebook client.
   *
   * @return AccessToken
   *
   * @throws FacebookSDKException
   */
  public static function requestAccessToken(array $params, FacebookApp $app, FacebookClient $client)
  {
    $response = static::request('/oauth/access_token', $params, $app, $client);
    $data = $response->getDecodedBody();

    if (!isset($data['access_token'])) {
      throw new FacebookSDKException('Access token was not returned from Graph.', 401);
    }

    // Graph returns two different key names for expiration time
    // on the same endpoint. Doh! :/
    $expiresAt = 0;
    if (isset($data['expires'])) {
      // For exchanging a short lived token with a long lived token.
      // The expiration time in seconds will be returned as "expires".
      $expiresAt = time() + $data['expires'];
    } elseif (isset($data['expires_in'])) {
      // For exchanging a code for a short lived access token.
      // The expiration time in seconds will be returned as "expires_in".
      // See: https://developers.facebook.com/docs/facebook-login/access-tokens#long-via-code
      $expiresAt = time() + $data['expires_in'];
    }
    $machineId = isset($data['machine_id']) ? $data['machine_id'] : null;
    return new static($data['access_token'], $expiresAt, $machineId);
  }

  /**
   * Request a code from a long lived access token.
   *
   * @param array $params
   * @param FacebookApp $app The FacebookApp entity.
   * @param FacebookClient $client The Facebook client.
   *
   * @return string
   *
   * @throws FacebookSDKException
   */
  public static function requestCode(array $params, FacebookApp $app, FacebookClient $client)
  {
    $response = static::request('/oauth/client_code', $params, $app, $client);
    $data = $response->getDecodedBody();

    if (isset($data['code'])) {
      return $data['code'];
    }

    throw new FacebookSDKException('Code was not returned from Graph.', 401);
  }

  /**
   * Send a request to Graph with an app access token.
   *
   * @param string $endpoint
   * @param array $params
   * @param FacebookApp $app The FacebookApp entity.
   * @param FacebookClient $client The Facebook client.
   *
   * @return FacebookResponse
   *
   * @throws FacebookResponseException
   */
  protected static function request($endpoint, array $params, FacebookApp $app, FacebookClient $client)
  {
    if (!isset($params['client_id'])) {
      $params['client_id'] = $app->getId();
    }
    if (!isset($params['client_secret'])) {
      $params['client_secret'] = $app->getSecret();
    }

    // The response for this endpoint is not JSON, so it must be handled
    //   differently, not as a GraphObject.
    $request = new FacebookRequest(
      $app,
      $app->getAccessToken(),
      'GET',
      $endpoint,
      $params
    );
    return $client->sendRequest($request);
  }

  /**
   * Get more info about an access token.
   *
   * @param FacebookApp $app The FacebookApp entity.
   * @param FacebookClient $client The Facebook client.
   *
   * @return GraphSessionInfo
   */
  public function getInfo(FacebookApp $app, FacebookClient $client)
  {
    $params = ['input_token' => $this->accessToken];

    $request = new FacebookRequest(
      $app,
      $app->getAccessToken(),
      'GET',
      '/debug_token',
      $params
    );
    $response = $client->sendRequest($request);
    $graphObject = $response->getGraphSessionInfo();

    // Update the data on this token
    if ($graphObject->getExpiresAt()) {
      $this->expiresAt = $graphObject->getExpiresAt();
    }

    return $graphObject;
  }

  /**
   * Returns the access token as a string.
   *
   * @return string
   */
  public function __toString()
  {
    return $this->accessToken;
  }

  /**
   * Serialize the entity.
   *
   * @return string
   */
  public function serialize()
  {
    $expiresAt = null;
    if ($this->expiresAt instanceof \DateTime) {
      $expiresAt = $this->expiresAt->getTimestamp();
    }

    return serialize([$this->accessToken, $expiresAt, $this->machineId]);
  }

  /**
   * Unserialize the entity.
   *
   * @param string $serialized
   */
  public function unserialize($serialized)
  {
    list($accessToken, $expiresAt, $machineId) = unserialize($serialized);

    $this->__construct($accessToken, $expiresAt, $machineId);
  }

}
