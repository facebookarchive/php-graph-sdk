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

use Facebook\HttpClients\FacebookHttpClientInterface;
use Facebook\HttpClients\FacebookCurlHttpClient;
use Facebook\HttpClients\FacebookStreamHttpClient;
use Facebook\Entities\FacebookRequest;
use Facebook\Entities\FacebookResponse;
use Facebook\Entities\AccessToken;
use Facebook\Exceptions\FacebookResponseException;

class FacebookClient
{
  /**
   * @const string Version number of the Facebook PHP SDK.
   */
  const VERSION = '4.1.x-dev';

  /**
   * @const string Graph API URL
   */
  const GRAPH_BASE_URL = 'https://graph.facebook.com';

  /**
   * @const string Beta Graph API URL
   */
  const BETA_GRAPH_BASE_URL = 'https://graph.beta.facebook.com';

  /**
   * @var FacebookHttpClientInterface
   */
  protected $httpClient;

  /**
   * @var bool
   */
  protected $useSecretProof;

  /**
   * @var bool
   */
  protected $useBeta;

  /**
   * Instanciate a new FacebookClient
   *
   * @param FacebookHttpClientInterface|null $httpClient
   * @param bool $useSecretProof
   * @param bool $useBeta
   */
  public function __construct(FacebookHttpClientInterface $httpClient = null, $useSecretProof = true, $useBeta = false)
  {
    if (null === $httpClient) {
      $httpClient = $this->getDefaultHttpClient();
    }

    $this->httpClient = $httpClient;
    $this->useSecretProof = (bool)$useSecretProof;
    $this->useBeta = (bool)$useBeta;
  }

  /**
   * @param bool $enable
   */
  public function useSecretProof($enable = true)
  {
    $this->useSecretProof = (bool)$enable;
  }

  /**
   * @param bool $enable
   */
  public function useBeta($enable = true)
  {
    $this->useBeta = (bool)$enable;
  }

  /**
   * @param FacebookRequest $request
   *
   * @return FacebookResponse
   *
   * @throws FacebookResponseException
   */
  public function handle(FacebookRequest $request)
  {
    $url = $this->getUrl($request);
    $params = $request->getParameters();

    foreach($request->getHeaders() as $header => $value) {
      $this->httpClient->addRequestHeader($header, $value);
    }

    $requestAccessToken = $request->getAccessToken();
    if ($requestAccessToken instanceof AccessToken) {
      $params['access_token'] = (string)$requestAccessToken;
    }

    if ($this->useSecretProof) {
      $accessToken = $request->getAccessToken();
      if (!isset($params['appsecret_proof']) && $accessToken) {
        $params['appsecret_proof'] = $accessToken->getSecretProof();
      }
    } else {
      unset($params['appsecret_proof']);
    }

    if ('GET' === $request->getMethod()) {
      $url = $this->appendParamsToUrl($url, $params);
      $params = [];
    }

    // Should throw `FacebookSDKException` exception on HTTP client error.
    // Don't catch to allow it to bubble up.
    $result = $this->httpClient->send($url, $request->getMethod(), $params);

    $response = new FacebookResponse(
      $request,
      $result,
      $this->httpClient->getResponseHttpStatusCode(),
      $this->httpClient->getResponseHeaders()
    );

    if ($response->isError()) {
      throw FacebookResponseException::create($response);
    }

    return $response;
  }

  protected function getUrl(FacebookRequest $request)
  {
    $url = !$this->useBeta ? static::GRAPH_BASE_URL : static::BETA_GRAPH_BASE_URL;

    return $url . '/' . $request->getGraphVersion() . $request->getEndpoint();
  }

  protected function getDefaultHttpClient()
  {
    return function_exists('curl_init') ? new FacebookCurlHttpClient() : new FacebookStreamHttpClient();
  }

  /**
   * Gracefully appends params to the URL.
   *
   * @param string $url
   * @param array $params
   *
   * @return string
   */
  protected function appendParamsToUrl($url, $params = [])
  {
    if (!$params) {
      return $url;
    }

    if (strpos($url, '?') === false) {
      return $url . '?' . http_build_query($params, null, '&');
    }

    list($path, $query_string) = explode('?', $url, 2);
    $query_array = [];
    parse_str($query_string, $query_array);

    // Favor params from the original URL over $params
    $params = array_merge($params, $query_array);

    return $path . '?' . http_build_query($params, null, '&');
  }

}