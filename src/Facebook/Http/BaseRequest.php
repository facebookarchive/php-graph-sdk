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
namespace Facebook\Http;

use Facebook\Entities\Request;
use Facebook\Entities\AccessToken;
use Facebook\Entities\Response;
use Facebook\Entities\BatchResponse;
use Facebook\GraphNodes\Collection;
use Facebook\Http\Clients\FacebookHttpClientInterface;
use Facebook\Http\Clients\FacebookCurlHttpClient;
use Facebook\Http\Clients\FacebookStreamHttpClient;
use Facebook\Exceptions\FacebookSDKException;

/**
 * Class BaseRequest
 * @package Facebook
 */
abstract class BaseRequest
{

  /**
   * @const string Production Graph API URL.
   */
  const BASE_GRAPH_URL = 'https://graph.facebook.com';

  /**
   * @const string Beta Graph API URL.
   */
  const BASE_GRAPH_URL_BETA = 'https://graph.beta.facebook.com';

  /**
   * @var bool Toggle to use Graph beta url.
   */
  protected static $betaMode = false;

  /**
   * @var FacebookHttpClientInterface HTTP client handler.
   */
  protected static $httpClientHandler;

  /**
   * @var Response|BatchResponse The last response entity returned.
   */
  protected $lastResponse;

  /**
   * @var array Array of Request entities.
   */
  protected $requests = [];

  /**
   * @var int The array key of the current request.
   */
  protected $currentRequestKey = -1;

  /**
   * @var int The number of calls that have been made to Graph.
   */
  public static $requestCount = 0;

  /**
   * Instantiates a new BaseRequest object.
   *
   * @param FacebookHttpClientInterface $httpClientHandler
   */
  public function __construct(FacebookHttpClientInterface $httpClientHandler)
  {
    static::$httpClientHandler = $httpClientHandler;
  }

  /**
   * Sets the HTTP client handler.
   *
   * @param \Facebook\Http\Clients\FacebookHttpClientInterface
   */
  public static function setHttpClientHandler(FacebookHttpClientInterface $httpClientHandler)
  {
    static::$httpClientHandler = $httpClientHandler;
  }

  /**
   * Returns an instance of the HTTP client data handler.
   *
   * @return FacebookHttpClientInterface
   */
  public static function getHttpClientHandler()
  {
    if (static::$httpClientHandler) {
      return static::$httpClientHandler;
    }
    return static::detectHttpClientHandler();
  }

  /**
   * Detects which HTTP client handler to use.
   *
   * @return FacebookHttpClientInterface
   */
  public static function detectHttpClientHandler()
  {
    return function_exists('curl_init')
      ? new FacebookCurlHttpClient()
      : new FacebookStreamHttpClient();
  }

  /**
   * Instantiates a new Request entity and stores it.
   *
   * @param AccessToken|string|null
   *
   * @return BaseRequest
   */
  public function newRequest($accessToken = null)
  {
    $request = new Request($accessToken);
    $this->setNextRequest($request);

    return $this;
  }

  /**
   * Sets the next instantiation of the Request entity.
   *
   * @param Request $request
   */
  public function setNextRequest(Request $request)
  {
    $this->currentRequestKey++;
    $this->requests[$this->currentRequestKey] = $request;
  }

  /**
   * Get the current Request entity.
   *
   * @return Request|/Facebook/Entities/BatchRequest
   *
   * @throws FacebookSDKException
   */
  public function getCurrentRequest()
  {
    if (isset($this->requests[$this->currentRequestKey])) {
      return $this->requests[$this->currentRequestKey];
    }
    throw new FacebookSDKException(
      'No request has been created yet.'
    );
  }

  /**
   * Get the the last response object.
   *
   * @return Response|BatchResponse
   */
  public function getLastResponse()
  {
    return $this->lastResponse;
  }

  /**
   * Returns the base Graph URL.
   *
   * @return string
   */
  public static function getBaseGraphUrl()
  {
    return static::$betaMode ? static::BASE_GRAPH_URL_BETA : static::BASE_GRAPH_URL;
  }

  /**
   * Finalize the current Request entity for a GET request.
   *
   * @param string|null $endpoint
   *
   * @return Collection|null
   */
  public function get($endpoint = null)
  {
    $this->getCurrentRequest()
      ->setMethod('GET')
      ->setEndpoint($endpoint);
  }

  /**
   * Finalize the current Request entity for a POST request.
   *
   * @param string|null $endpoint
   * @param array|null $params
   *
   * @return Collection|null
   */
  public function post($endpoint = null, $params = null)
  {
    $this->getCurrentRequest()
      ->setMethod('POST')
      ->setEndpoint($endpoint)
      ->setParams($params);
  }

  /**
   * Finalize the current Request entity for a DELETE request.
   *
   * @param string|null $endpoint
   *
   * @return Collection|null
   */
  public function delete($endpoint = null)
  {
    $this->getCurrentRequest()
      ->setMethod('DELETE')
      ->setEndpoint($endpoint);
  }

  /**
   * Prepares the Request entities to be sent to Graph.
   */
  abstract protected function prepareRequest();

  /**
   * Makes the request to Graph and returns the result.
   *
   * @param string $method
   * @param string $url
   * @param array|null $params
   * @param array $headers
   *
   * @return Collection|BatchResponse
   *
   * @throws FacebookSDKException
   */
  public function sendRequest($method, $url, $params = null, $headers = [])
  {
    $url = static::getBaseGraphUrl().$url;

    $connection = static::getHttpClientHandler();

    foreach ($headers as $name => $value) {
      $connection->addRequestHeader($name, $value);
    }

    // Should throw `FacebookSDKException` exception on HTTP client error.
    // Don't catch to allow it to bubble up.
    $response = $connection->send($url, $method, $params);

    static::$requestCount++;

    $httpResponseCode = $connection->getResponseHttpStatusCode();
    $httpResponseHeaders = $connection->getResponseHeaders();

    $returnResponse = $this->makeResponseEntity($httpResponseCode, $httpResponseHeaders, $response);

    if ($this->lastResponse->isError()) {
      throw $this->lastResponse->getThrownException();
    }

    return $returnResponse;
  }

  /**
   * Return the proper response.
   *
   * @param int $httpStatusCode
   * @param array $headers
   * @param string $body
   * @param AccessToken|string|null $accessToken
   *
   * @return Response|BatchResponse
   */
  abstract public function makeResponseEntity($httpStatusCode, array $headers, $body, $accessToken = null);

  /**
   * Toggle beta mode.
   *
   * @param boolean $betaMode
   */
  public static function enableBetaMode($betaMode = true)
  {
    static::$betaMode = $betaMode;
  }

  /**
   * Pass-along the access token to the current Request entity.
   *
   * @param AccessToken|string $accessToken
   *
   * @return BaseRequest
   */
  public function withAccessToken($accessToken)
  {
    $this->getCurrentRequest()
      ->setAccessToken($accessToken);
    return $this;
  }

  /**
   * Pass-along the params to the current Request entity.
   *
   * @param array $params
   *
   * @return BaseRequest
   */
  public function withFields(array $params)
  {
    $this->getCurrentRequest()
      ->setParams($params);
    return $this;
  }

  /**
   * Pass-along the eTag to the current Request entity.
   *
   * @param string $eTag
   *
   * @return BaseRequest
   */
  public function withETag($eTag)
  {
    $this->getCurrentRequest()
      ->setETag($eTag);
    return $this;
  }

}
