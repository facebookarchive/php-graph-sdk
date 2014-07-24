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

use Facebook\FacebookClient;
use Facebook\Entities\FacebookApp;
use Facebook\Entities\FacebookRequest;
use Facebook\Entities\Code;
use Facebook\Entities\DebugAccessToken;
use Facebook\Exceptions\FacebookResponseException;

/**
 * Class AccessToken
 * @package Facebook
 */
class AccessToken implements \Serializable
{
  /**
   * @var FacebookApp
   */
  protected $app;

  /**
   * The access token.
   *
   * @var string
   */
  protected $value;

  /**
   * Date when token expires.
   *
   * @var \DateTime|null
   */
  protected $expiresAt;

  /**
   * A unique ID to identify a client.
   *
   * @var string
   */
  protected $machineId;

  /**
   * Create a new access token entity.
   *
   * @param FacebookApp $app
   * @param string $value
   * @param int $expiresAt
   * @param string|null machineId
   */
  public function __construct(FacebookApplication $app, $value, $expiresAt = 0, $machineId = null)
  {
    $this->app = $app;
    $this->value = $value;
    if ($expiresAt) {
      $this->expiresAt = new \DateTime();
      $this->expiresAt->setTimestamp($expiresAt);
    }
    $this->machineId = $machineId;
  }

  /**
   * @return string
   */
  public function getValue()
  {
    return $this->value;
  }

  /**
   * @return \DateTime|null
   */
  public function getExpiresAt()
  {
    return $this->expiresAt;
  }

  /**
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
   * Checks the validity of the access token. Ensures that the token has
   *   not expired and the machineId matches if it's being used
   *
   * @param string|null $machineId
   *
   * @return bool
   */
  public function isValid($machineId = null)
  {
    return !$this->isExpired() && $this->getMachineId() == $machineId;
  }

  /**
   * @return string
   */
  public function getSecretProof()
  {
    return hash_hmac('sha256', (string)$this, $this->app->getSecret());
  }

  /**
   * Get a valid code from an access token.
   *
   * @param FacebookClient $client
   *
   * @return Code
   */
  public function getCode(FacebookClient $client, $redirectUri = '')
  {
    $params = array(
      'client_id' => $this->app->getId(),
      'client_secret' => $this->app->getSecret(),
      'access_token' => $this->value,
      'redirect_uri' => $redirectUri,
    );

    $response = $client->handle(new FacebookRequest(
      $this->app->getAccessToken(),
      '/oauth/client_code',
      'GET',
      $params
    ));

    if (!isset($response['code'])) {
      throw FacebookResponseException::create($response);
    }

    return new Code($this->app, $response['code']);
  }

  /**
   * Exchanges a short lived access token with a long lived access token.
   *
   * @param FacebookClient $client
   *
   * @return AccessToken
   */
  public function getExtended(FacebookClient $client)
  {
    $params = array(
      'client_id' => $this->app->getId(),
      'client_secret' => $this->app->getSecret(),
      'grant_type' => 'fb_exchange_token',
      'fb_exchange_token' => $this->value,
    );

    $response = $client->handle(new FacebookRequest(
      $this->app->getAccessToken(),
      '/oauth/access_token',
      'GET',
      $params
    ));

    if (!isset($response['access_token'])) {
      throw FacebookResponseException::create($response);
    }

    $expiresAt = 0;
    if (isset($response['expires'])) {
      $expiresAt = time() + $response['expires'];
    } elseif($response['expires_in']) {
      $expiresAt = time() + $response['expires_in'];
    }

    $machineId = isset($response['machine_id']) ? $response['machine_id'] : null;

    return new AccessToken($this->app, $response['access_token'], $expiresAt, $machineId);
  }

  /**
   * Get more info about an access token.
   *
   * @param FacebookClient $client
   *
   * @return DebugAccessToken
   */
  public function getDebugged(FacebookClient $client)
  {
    return new DebugAccessToken($client, $this->app, $this->value);
  }

  public function __toString()
  {
    return $this->value;
  }

  public function serialize()
  {
    $expiresAt = 0;
    if ($this->expiresAt instanceof \DateTime) {
      $expiresAt = $this->expiresAt->getTimestamp();
    }

    return serialize(array($this->app, $this->value, $expiresAt, $this->machineId));
  }

  public function unserialize($serialized)
  {
    list($app, $value, $expiresAt, $machineId) = unserialize($serialized);

    $this->__construct($app, $value, $expiresAt, $machineId);
  }

}
