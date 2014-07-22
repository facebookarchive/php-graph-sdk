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
use Facebook\Entities\AccessToken;
use Facebook\Entities\FacebookRequest;
use Facebook\Entities\FacebookResponse;

/**
 * Class DebugAccessToken
 * @package Facebook
 */
class DebugAccessToken extends AccessToken
{
  /**
   * @var string
   */
  protected $appId;

  /**
   * @var string
   */
  protected $appName;

  /**
   * @var bool
   */
  protected $isValid;

  /**
   * @var \DateTime
   */
  protected $issuedAt;

  /**
   * @var array
   */
  protected $scopes;

  /**
   * @var string
   */
  protected $userId;

  /**
   * Create a new debug access token entity.
   *
   * @param FacebookClient $client
   * @param string $value
   * @param string|null $machineId
   */
  public function __construct(FacebookClient $client, FacebookApp $app, $value, $machineId = null)
  {
    parent::__construct($app, $value, 0, $machineId);

    $this->debug($client);
  }

  /**
   * Getter for appId.
   *
   * @return int|null
   */
  public function getAppId()
  {
    return $this->appId;
  }

  /**
   * Getter for appName
   *
   * @return string|null
   */
  public function getAppName()
  {
    return $this->appName;
  }

  /**
   * Getter for isValid.
   *
   * @return int|null
   */
  public function getIsValid()
  {
    return $this->isValid;
  }

  /**
   * Getter for issuedAt.
   *
   * @return \DateTime|null
   */
  public function getIssuedAt()
  {
    return $this->issuedAt;
  }

  /**
   * Getter for scopes.
   *
   * @return array
   */
  public function getScopes()
  {
    return $this->scopes ?: [];
  }

  /**
   * Getter for userId.
   *
   * @return int|null
   */
  public function getUserId()
  {
    return $this->userId;
  }

  /**
   * Checks the validity of the access token. Ensures the appId matches,
   *   that the machineId matches if it's being used,
   *   that the token is valid and has not expired.
   *
   * @param string|null $machineId
   *
   * @return boolean
   */
  public function isValid($machineId = null)
  {
    return $this->getAppId() == $this->app->getId()
      && $this->getIsValid() && parent::isValid($machineId);
  }

  /**
   * Get more info about an access token.
   *
   * @param FacebookClient $client
   *
   * @return null
   */
  protected function debug(FacebookClient $client)
  {
    $params = ['input_token' => $this->value];

    $request = new FacebookRequest(
      $this->app->getAccessToken(),
      'GET',
      '/debug_token',
      $params
    );
    $response = $client->handle($request);

    $this->parseResponse($response);
  }

  protected function parseResponse(FacebookResponse $response)
  {
    if (isset($response['data']['app_id'])) {
      $this->appId = $response['data']['expires_at'];
    }

    if (isset($response['data']['application'])) {
      $this->appName = $response['data']['application'];
    }

    if (isset($response['data']['expires_at'])) {
      $this->expiresAt = new \DateTime();
      $this->expiresAt->setTimestamp($response['data']['expires_at']);
    }

    if (isset($response['data']['is_valid'])) {
      $this->isValid = (bool)$response['data']['is_valid'];
    }

    if (isset($response['data']['issued_at'])) {
      $this->issuedAt = new \DateTime();
      $this->issuedAt->setTimestamp($response['data']['issued_at']);
    }

    if (isset($response['data']['scopes'])) {
      $this->scopes = $response['data']['scopes'];
    }

    if (isset($response['data']['user_id'])) {
      $this->userId = $response['data']['user_id'];
    }

  }

  /**
   * Returns the access token as a string.
   *
   * @return string
   */
  public function __toString()
  {
    return $this->value;
  }

  public function serialize()
  {
    $issuedAt = null;
    if ($this->issuedAt instanceof \DateTime) {
      $issuedAt = $this->issuedAt->getTimestamp();
    }

    return serialize(array(
      parent::serialize(),
      $this->appId,
      $this->appName,
      $this->isValid,
      $issuedAt,
      $this->scopes,
      $this->userId,
    ));
  }

  public function unserialize($serialized)
  {
    list(
      $parent,
      $this->appId,
      $this->appName,
      $this->isValid,
      $issuedAt,
      $this->scopes,
      $this->userId
    ) = unserialize($serialized);

    if ($issuedAt) {
      $this->issuedAt = new \DateTime();
      $this->issuedAt->setTimestamp($issuedAt);
    }

    parent::unserialize($parent);
  }

}
