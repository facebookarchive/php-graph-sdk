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
namespace Facebook\Helpers;

use Facebook\Entities\FacebookApp;
use Facebook\Entities\SignedRequest;
use Facebook\Entities\AccessToken;
use Facebook\FacebookClient;

/**
 * Class FacebookSignedRequestFromInputHelper
 * @package Facebook
 */
abstract class FacebookSignedRequestFromInputHelper
{

  /**
   * @var SignedRequest|null The SignedRequest entity.
   */
  protected $signedRequest;

  /**
   * @var FacebookApp The FacebookApp entity.
   */
  protected $app;

  /**
   * @var string|null Random string to prevent CSRF.
   */
  public $state;

  /**
   * Initialize the helper and process available signed request data.
   *
   * @param FacebookApp $app The FacebookApp entity.
   */
  public function __construct(FacebookApp $app)
  {
    $this->app = $app;

    $this->instantiateSignedRequest();
  }

  /**
   * Instantiates a new SignedRequest entity.
   *
   * @param string|null
   */
  public function instantiateSignedRequest($rawSignedRequest = null)
  {
    $rawSignedRequest = $rawSignedRequest ?: $this->getRawSignedRequest();

    if (!$rawSignedRequest) {
      return;
    }

    $this->signedRequest = new SignedRequest($rawSignedRequest, $this->state, $this->app->getSecret());
  }

  /**
   * Returns an AccessToken entity from the signed request.
   *
   * @param FacebookClient $client The Facebook client.
   *
   * @return AccessToken|null
   *
   * @throws \Facebook\Exceptions\FacebookSDKException
   */
  public function getAccessToken(FacebookClient $client)
  {
    if ($this->signedRequest && $this->signedRequest->hasOAuthData()) {
      $code = $this->signedRequest->get('code');
      $accessToken = $this->signedRequest->get('oauth_token');

      if ($code && !$accessToken) {
        return AccessToken::getAccessTokenFromCode($code, $this->app, $client);
      }

      $expiresAt = $this->signedRequest->get('expires', 0);
      return new AccessToken($accessToken, $expiresAt);
    }
    return null;
  }

  /**
   * Returns the SignedRequest entity.
   *
   * @return SignedRequest|null
   */
  public function getSignedRequest()
  {
    return $this->signedRequest;
  }

  /**
   * Returns the user_id if available.
   *
   * @return string|null
   */
  public function getUserId()
  {
    return $this->signedRequest ? $this->signedRequest->getUserId() : null;
  }

  /**
   * Get raw signed request from input.
   *
   * @return string|null
   */
  abstract public function getRawSignedRequest();

  /**
   * Get raw signed request from GET input.
   *
   * @return string|null
   */
  public function getRawSignedRequestFromGet()
  {
    if (isset($_GET['signed_request'])) {
      return $_GET['signed_request'];
    }

    return null;
  }

  /**
   * Get raw signed request from POST input.
   *
   * @return string|null
   */
  public function getRawSignedRequestFromPost()
  {
    if (isset($_POST['signed_request'])) {
      return $_POST['signed_request'];
    }

    return null;
  }

  /**
   * Get raw signed request from cookie set from the Javascript SDK.
   *
   * @return string|null
   */
  public function getRawSignedRequestFromCookie()
  {
    if (isset($_COOKIE['fbsr_' . $this->app->getId()])) {
      return $_COOKIE['fbsr_' . $this->app->getId()];
    }
    return null;
  }

}
