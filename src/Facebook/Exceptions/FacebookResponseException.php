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
namespace Facebook\Exceptions;

use Facebook\Entities\FacebookResponse;

/**
 * Class FacebookResponseException
 * @package Facebook
 * @author Fosco Marotto <fjm@fb.com>
 * @author David Poll <depoll@fb.com>
 */
class FacebookResponseException extends FacebookSDKException
{

  /**
   * @var FacebookResponse The response causing the exception
   */
  protected $response;

  /**
   * Creates a FacebookResponseException.
   *
   * @param FacebookResponse $response The response from the Graph API
   */
  public function __construct(FacebookResponse $response)
  {
    $this->response = $response;

    parent::__construct(
      $this->get('message', 'Unknown Exception'), $this->get('code', -1), null
    );
  }

  /**
   * Process an error payload from the Graph API and return the appropriate
   *   exception subclass.
   *
   * @param FacebookResponse $response
   *
   * @return FacebookResponseException
   */
  public static function create(FacebookResponse $response)
  {
    if (!isset($response['error']['code']) && isset($response['code'])) {
      $response = array('error' => $response->getBody());
    }
    $code = (isset($response['error']['code']) ? $response['error']['code'] : null);

    if (isset($response['error']['error_subcode'])) {
      switch ($response['error']['error_subcode']) {
        // Other authentication issues
        case 458:
        case 459:
        case 460:
        case 463:
        case 464:
        case 467:
          return new FacebookAuthorizationException($response);
      }
    }

    switch ($code) {
      // Login status or token expired, revoked, or invalid
      case 100:
      case 102:
      case 190:
        return new FacebookAuthorizationException($response);

      // Server issue, possible downtime
      case 1:
      case 2:
        return new FacebookServerException($response);

      // API Throttling
      case 4:
      case 17:
      case 341:
        return new FacebookThrottleException($response);

      // Duplicate Post
      case 506:
        return new FacebookClientException($response);
    }

    // Missing Permissions
    if ($code == 10 || ($code >= 200 && $code <= 299)) {
      return new FacebookPermissionException($response);
    }

    // OAuth authentication error
    if (isset($response['error']['type'])
      and $response['error']['type'] === 'OAuthException') {
      return new FacebookAuthorizationException($response);
    }

    // All others
    return new FacebookOtherException($response);
  }

  /**
   * Returns the error sub-code
   *
   * @return int
   */
  public function getErrorSubCode()
  {
    return $this->get('error_subcode', -1);
  }

  /**
   * Returns the error type
   *
   * @return string
   */
  public function getErrorType()
  {
    return $this->get('type', '');
  }

  /**
   * Returns the response used to create the exception.
   *
   * @return FacebookResponse
   */
  public function getResponse()
  {
    return $this->response;
  }

  /**
   * Checks isset and returns that or a default value.
   *
   * @param string $key
   * @param mixed $default
   *
   * @return mixed
   */
  private function get($key, $default = null)
  {
    if (isset($this->response['error'][$key])) {
      return $this->response['error'][$key];
    }
    return $default;
  }

}