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

use Facebook\Entities\Response;

/**
 * Class FacebookResponseException
 * @package Facebook
 * @author Fosco Marotto <fjm@fb.com>
 * @author David Poll <depoll@fb.com>
 */
class FacebookResponseException extends FacebookSDKException
{

  /**
   * @var Response The Response entity that threw the exception.
   */
  private $responseEntity;

  /**
   * Creates a FacebookResponseException.
   *
   * @param Response $responseEntity The Request entity that threw the exception.
   */
  public function __construct(Response $responseEntity)
  {
    $this->responseEntity = $responseEntity;

    parent::__construct(
      $this->get('message', 'Unknown Exception'),
      $this->get('code', -1),
      null
    );
  }

  /**
   * Process an error payload from the Graph API and return the appropriate
   *   exception subclass.
   *
   * @param Response $responseEntity The Request entity that threw the exception.
   *
   * @return FacebookResponseException
   */
  public static function create(Response $responseEntity)
  {
    $data = $responseEntity->getDecodedBody();
    if (!isset($data['error']['code']) && isset($data['code'])) {
      $data = ['error' => $data];
    }
    $code = isset($data['error']['code']) ? $data['error']['code'] : null;

    if (isset($data['error']['error_subcode'])) {
      switch ($data['error']['error_subcode']) {
        // Other authentication issues
        case 458:
        case 459:
        case 460:
        case 463:
        case 464:
        case 467:
          return new FacebookAuthorizationException($responseEntity);
          break;
      }
    }

    switch ($code) {
      // Login status or token expired, revoked, or invalid
      case 100:
      case 102:
      case 190:
        return new FacebookAuthorizationException($responseEntity);
        break;

      // Server issue, possible downtime
      case 1:
      case 2:
        return new FacebookServerException($responseEntity);
        break;

      // API Throttling
      case 4:
      case 17:
      case 341:
        return new FacebookThrottleException($responseEntity);
        break;

      // Duplicate Post
      case 506:
        return new FacebookClientException($responseEntity);
        break;
    }

    // Missing Permissions
    if ($code == 10 || ($code >= 200 && $code <= 299)) {
      return new FacebookPermissionException($responseEntity);
    }

    // OAuth authentication error
    if (isset($data['error']['type'])
      and $data['error']['type'] === 'OAuthException') {
      return new FacebookAuthorizationException($responseEntity);
    }

    // All others
    return new FacebookOtherException($responseEntity);
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
    $response = $this->getResponse();
    if (isset($response['error'][$key])) {
      return $response['error'][$key];
    }
    return $default;
  }

  /**
   * Returns the HTTP status code
   *
   * @return int
   */
  public function getHttpStatusCode()
  {
    return $this->responseEntity->getHttpStatusCode();
  }

  /**
   * Returns the sub-error code
   *
   * @return int
   */
  public function getSubErrorCode()
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
   * Returns the raw response used to create the exception.
   *
   * @return string
   */
  public function getRawResponse()
  {
    return $this->responseEntity->getBody();
  }

  /**
   * Returns the decoded response used to create the exception.
   *
   * @return mixed
   */
  public function getResponse()
  {
    return $this->responseEntity->getDecodedBody();
  }

}
