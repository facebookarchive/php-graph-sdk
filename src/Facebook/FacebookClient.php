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
namespace Facebook;

use Facebook\Entities\FacebookRequest;
use Facebook\Entities\FacebookBatchRequest;
use Facebook\Entities\FacebookResponse;
use Facebook\Entities\FacebookBatchResponse;
use Facebook\HttpClients\FacebookHttpClientInterface;
use Facebook\HttpClients\FacebookCurlHttpClient;
use Facebook\HttpClients\FacebookStreamHttpClient;
use Facebook\Exceptions\FacebookSDKException;

/**
 * Class FacebookClient
 * @package Facebook
 */
class FacebookClient
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
  protected $enableBetaMode = false;

  /**
   * @var FacebookHttpClientInterface HTTP client handler.
   */
  protected $httpClientHandler;

  /**
   * @var int The number of calls that have been made to Graph.
   */
  public static $requestCount = 0;

  /**
   * Instantiates a new FacebookClient object.
   *
   * @param FacebookHttpClientInterface|null $httpClientHandler
   * @param boolean $enableBeta
   */
  public function __construct(
    FacebookHttpClientInterface $httpClientHandler = null,
    $enableBeta = false
  )
  {
    $this->httpClientHandler = $httpClientHandler ?: $this->detectHttpClientHandler();
    $this->enableBetaMode = $enableBeta;
  }

  /**
   * Sets the HTTP client handler.
   *
   * @param FacebookHttpClientInterface $httpClientHandler
   */
  public function setHttpClientHandler(FacebookHttpClientInterface $httpClientHandler)
  {
    $this->httpClientHandler = $httpClientHandler;
  }

  /**
   * Returns the HTTP client handler.
   *
   * @return FacebookHttpClientInterface
   */
  public function getHttpClientHandler()
  {
    return $this->httpClientHandler;
  }

  /**
   * Detects which HTTP client handler to use.
   *
   * @return FacebookHttpClientInterface
   */
  public function detectHttpClientHandler()
  {
    return function_exists('curl_init')
      ? new FacebookCurlHttpClient()
      : new FacebookStreamHttpClient();
  }

  /**
   * Toggle beta mode.
   *
   * @param boolean $betaMode
   */
  public function enableBetaMode($betaMode = true)
  {
    $this->enableBetaMode = $betaMode;
  }

  /**
   * Returns the base Graph URL.
   *
   * @return string
   */
  public function getBaseGraphUrl()
  {
    return $this->enableBetaMode ? static::BASE_GRAPH_URL_BETA : static::BASE_GRAPH_URL;
  }

  /**
   * Makes the request to Graph and returns the result.
   *
   * @param FacebookRequest $request
   *
   * @return FacebookResponse
   *
   * @throws FacebookSDKException
   */
  public function sendRequest(FacebookRequest $request)
  {
    if (get_class($request) === 'FacebookRequest') {
      $request->validateAccessToken();
    }
    $url = $this->getBaseGraphUrl() . $request->getUrl();
    $method = $request->getMethod();
    $params = $request->getPostParams();
    $headers = $request->getHeaders();

    // Should throw `FacebookSDKException` exception on HTTP client error.
    // Don't catch to allow it to bubble up.
    $response = $this->httpClientHandler->send($url, $method, $params, $headers);

    static::$requestCount++;

    $httpResponseCode = $this->httpClientHandler->getResponseHttpStatusCode();
    $httpResponseHeaders = $this->httpClientHandler->getResponseHeaders();

    $accessToken = $request->getAccessToken();
    $app = $request->getApp();

    $returnResponse = new FacebookResponse($app, $httpResponseCode, $httpResponseHeaders, $response, $accessToken);

    if ($returnResponse->isError()) {
      throw $returnResponse->getThrownException();
    }

    return $returnResponse;
  }

  /**
   * Makes a batched request to Graph and returns the result.
   *
   * @param FacebookBatchRequest $request
   *
   * @return FacebookBatchResponse
   *
   * @throws FacebookSDKException
   */
  public function sendBatchRequest(FacebookBatchRequest $request)
  {
    $request->prepareRequestsForBatch();
    $facebookResponse = $this->sendRequest($request);

    return new FacebookBatchResponse($facebookResponse);
  }

}
