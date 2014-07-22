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

use Facebook\Entities\FacebookApp;
use Facebook\FacebookClient;
use Facebook\Entities\AccessToken;
use Facebook\Entities\FacebookRequest;
use Facebook\Exceptions\FacebookResponseException;

/**
 * Class Code
 * @package Facebook
 */
class Code
{
  /**
   * @var FacebookApp
   */
  protected $app;

  /**
   * The code
   *
   * @var string
   */
  protected $value;

  /**
   * Instanciate a new Code
   *
   * @param FacebookApp $app
   * @param string $value
   */
  public function __construct(FacebookApp $app, $value)
  {
    $this->app = $app;
    $this->value = $value;
  }

  /**
   * @return string
   */
  public function getValue()
  {
    return $this->value;
  }

  /**
   * Get a valid access token from this code.
   *
   * @param FacebookClient $client
   * @param string $redirectUri
   * @param string|null $machineId
   *
   * @return AccessToken
   */
  public function getAccessToken(FacebookClient $client, $redirectUri, $machineId = null)
  {
    $params = array(
      'client_id' => $this->app->getId(),
      'client_secret' => $this->app->getSecret(),
      'code' => $this->value,
      'redirect_uri' => $redirectUri,
    );

    if ($machineId) {
      $params['machine_id'] = $machineId;
    }

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

  public function __toString()
  {
    return (string)$this->value;
  }
}