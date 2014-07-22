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

use Facebook\Entities\AccessToken;
use Facebook\Exceptions\FacebookSDKException;

/**
 * Class FacebookRequest
 * @package Facebook
 */
class FacebookRequest
{
  /**
   * @const string Version number of the Facebook PHP SDK.
   */
  const SDK_VERSION = '4.1.x-dev';

  /**
   * @var AccessToken The access token for this request.
   */
  protected $accessToken;

  /**
   * @var string The Graph endpoint for this request.
   */
  protected $endpoint;

  /**
   * @var string The HTTP method for this request.
   */
  protected $method;

  /**
   * @var array The parameters to send with this request.
   */
  protected $params;

  /**
   * @var string ETag to send with this request.
   */
  protected $eTag;

  /**
   * Creates a new Request entity.
   *
   * @param string $endpoint
   * @param string $method
   * @param array $params
   * @param AccessToken|null $accessToken
   * @param string $eTag
   */
  public function __construct(
    $endpoint,
    $method = 'GET',
    array $params = [],
    AccessToken $accessToken = null,
    $eTag = ''
  )
  {
    $this->validateEndpoint($endpoint);
    $this->validateMethod($method);

    $this->accessToken = $accessToken;
    $this->endpoint = '/' . ltrim($endpoint, '/');
    $this->method = strtoupper($method);
    $this->params = [];
    $this->eTag = $eTag;

    $this->processGetParametersInEndpoint();
    // Favor params from the original URL over $params
    $this->params = array_merge($params, $this->params);
  }

  /**
   * Return the HTTP method for this request.
   *
   * @return string
   */
  public function getEndpoint()
  {
    return $this->endpoint;
  }

  /**
   * Return the HTTP method for this request.
   *
   * @return string
   */
  public function getMethod()
  {
    return $this->method;
  }

  /**
   * Generate and return the params for this request.
   *
   * @return array
   */
  public function getParameters()
  {
    return $this->params;
  }

  /**
   * Return the access token for this request.
   *
   * @return AccessToken|null
   */
  public function getAccessToken()
  {
    return $this->accessToken;
  }

  /**
   * Generate and return the headers for this request.
   *
   * @return array
   */
  public function getHeaders()
  {
    $headers = [
      'User-Agent' => 'fb-php-' . static::SDK_VERSION,
      'Accept-Encoding' => '*',
    ];

    if ($this->eTag) {
      $headers['If-None-Match'] = $this->eTag;
    }

    return $headers;
  }

  protected function validateEndpoint($endpoint)
  {
    if (!is_string($endpoint)) {
      throw new FacebookSDKException('Endpoint have to be a string');
    }

    if ('/' !== $endpoint[0]) {
      throw new FacebookSDKException('Endpoint have to start with "/"');
    }
  }

  protected function validateMethod($method)
  {
    if (!in_array(strtoupper($method), ['GET', 'POST', 'DELETE'])) {
      throw new FacebookSDKException('Invalid method');
    }
  }

  protected function processGetParametersInEndpoint()
  {
    if (false === strpos($this->endpoint, '?')) {
      return;
    }

    list($this->endpoint, $query_string) = explode('?', $this->endpoint, 2);
    parse_str($query_string, $this->params);
  }

}