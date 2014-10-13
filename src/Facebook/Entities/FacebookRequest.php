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

use Facebook\Facebook;
use Facebook\Exceptions\FacebookSDKException;

/**
 * Class Request
 * @package Facebook
 */
class FacebookRequest
{

  /**
   * @var FacebookApp The Facebook app entity.
   */
  protected $app;

  /**
   * @var string The access token to use for this request.
   */
  protected $accessToken;

  /**
   * @var string The HTTP method for this request.
   */
  protected $method;

  /**
   * @var string The Graph endpoint for this request.
   */
  protected $endpoint;

  /**
   * @var array The parameters to send with this request.
   */
  protected $params = [];

  /**
   * @var string ETag to send with this request.
   */
  protected $eTag;

  /**
   * @var string Graph version to use for this request.
   */
  protected $graphVersion;

  /**
   * Creates a new Request entity.
   *
   * @param FacebookApp|null $app
   * @param AccessToken|string|null $accessToken
   * @param string|null $method
   * @param string|null $endpoint
   * @param array|null $params
   * @param string|null $eTag
   * @param string|null $graphVersion
   */
  public function __construct(
    FacebookApp $app = null,
    $accessToken = null,
    $method = null,
    $endpoint = null,
    array $params = [],
    $eTag = null,
    $graphVersion = null
  )
  {
    $this->setApp($app);
    $this->setAccessToken($accessToken);
    $this->setMethod($method);
    $this->setEndpoint($endpoint);
    $this->setParams($params);
    $this->setETag($eTag);
    $this->graphVersion = $graphVersion ?: Facebook::DEFAULT_GRAPH_VERSION;
  }

  /**
   * Set the access token for this request.
   *
   * @param AccessToken|string
   *
   * @return FacebookRequest
   */
  public function setAccessToken($accessToken)
  {
    $this->accessToken = $accessToken instanceof AccessToken
      ? (string) $accessToken
      : $accessToken;
    return $this;
  }

  /**
   * Return the access token for this request.
   *
   * @return string
   */
  public function getAccessToken()
  {
    return (string) $this->accessToken;
  }

  /**
   * Set the FacebookApp entity used for this request.
   *
   * @param FacebookApp|null $app
   */
  public function setApp(FacebookApp $app = null)
  {
    $this->app = $app;
  }

  /**
   * Return the FacebookApp entity used for this request.
   *
   * @return FacebookApp
   */
  public function getApp()
  {
    return $this->app;
  }

  /**
   * Validate that an access token exists for this request.
   *
   * @throws FacebookSDKException
   */
  public function validateAccessToken()
  {
    $accessToken = $this->getAccessToken();
    if (!$accessToken) {
      throw new FacebookSDKException(
        'You must provide an access token.'
      );
    }
  }

  /**
   * Set the HTTP method for this request.
   *
   * @param string
   *
   * @return FacebookRequest
   */
  public function setMethod($method)
  {
    $this->method = strtoupper($method);
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
   * Validate that the HTTP method is set.
   *
   * @throws FacebookSDKException
   */
  public function validateMethod()
  {
    if (!$this->method) {
      throw new FacebookSDKException(
        'HTTP method not specified.'
      );
    }
    switch ($this->method) {
      case 'GET':
      case 'POST':
      case 'DELETE':
        return;
        break;
    }
    throw new FacebookSDKException(
      'Invalid HTTP method specified.'
    );
  }

  /**
   * Set the endpoint for this request.
   *
   * @param string
   *
   * @return FacebookRequest
   */
  public function setEndpoint($endpoint)
  {
    $this->endpoint = $endpoint;
    return $this;
  }

  /**
   * Return the HTTP method for this request.
   *
   * @return string
   */
  public function getEndpoint()
  {
    // For batch requests, this will be empty
    return $this->endpoint;
  }

  /**
   * Generate and return the headers for this request.
   *
   * @return array
   */
  public function getHeaders()
  {
    $headers = static::getDefaultHeaders();

    if ($this->eTag) {
      $headers['If-None-Match'] = $this->eTag;
    }

    return $headers;
  }

  /**
   * Sets the eTag value.
   *
   * @param string $eTag
   */
  public function setETag($eTag)
  {
    $this->eTag = $eTag;
  }

  /**
   * Set the params for this request.
   *
   * @param array $params
   *
   * @return FacebookRequest
   */
  public function setParams(array $params = [])
  {
    if (is_array($params)) {
      $this->params = array_merge($this->params, $params);
    }
    return $this;
  }

  /**
   * Generate and return the params for this request.
   *
   * @return array
   */
  public function getParams()
  {
    $params = $this->params;

    if (!isset($params['access_token']) && $this->getAccessToken()) {
      $params['access_token'] = $this->getAccessToken();
    }
    if (!isset($params['appsecret_proof']) && isset($params['access_token'])) {
      $params['appsecret_proof'] = AppSecretProof::make($params['access_token'], $this->app->getSecret());
    }

    return $params;
  }

  /**
   * Only return params on POST requests.
   *
   * @return array
   */
  public function getPostParams()
  {
    if ($this->getMethod() === 'POST') {
      return $this->getParams();
    }

    return [];
  }

  /**
   * The graph version used for this request.
   *
   * @return string
   */
  public function getGraphVersion()
  {
    return $this->graphVersion;
  }

  /**
   * Generate and return the URL for this request.
   *
   * @return string
   */
  public function getUrl()
  {
    $this->validateMethod();

    $graphVersion = static::forceSlashPrefix($this->graphVersion);
    $endpoint = static::forceSlashPrefix($this->getEndpoint());

    $url = $graphVersion.$endpoint;

    if ($this->getMethod() !== 'POST') {
      $params = $this->getParams();
      $url = static::appendParamsToUrl($url, $params);
    }

    return $url;
  }

  /**
   * Gracefully appends params to the URL.
   *
   * @param string $url
   * @param array $params
   *
   * @return string
   */
  public static function appendParamsToUrl($url, $params = [])
  {
    if (!$params) {
      return $url;
    }

    if (strpos($url, '?') === false) {
      return $url . '?' . http_build_query($params, null, '&');
    }

    list($path, $query_string) = explode('?', $url, 2);
    parse_str($query_string, $query_array);

    // Favor params from the original URL over $params
    $params = array_merge($params, $query_array);

    return $path . '?' . http_build_query($params, null, '&');
  }

  /**
   * Check for a "/" prefix and prepend it if not exists.
   *
   * @param string|null $string
   *
   * @return string|null
   */
  public static function forceSlashPrefix($string)
  {
    if (!$string) {
      return $string;
    }
    return strpos($string, '/') === 0 ? $string : '/'.$string;
  }

  /**
   * Return the default headers that every request should use.
   *
   * @return array
   */
  public static function getDefaultHeaders()
  {
    return [
      'User-Agent' => 'fb-php-' . Facebook::VERSION,
      'Accept-Encoding' => '*',
    ];
  }

}
