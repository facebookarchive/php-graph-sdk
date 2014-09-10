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
namespace Facebook\HttpClients;

use Facebook\Exceptions\FacebookSDKException;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\AdapterException;
use GuzzleHttp\Exception\RequestException;

class FacebookGuzzleHttpClient implements FacebookHttpClientInterface
{

  /**
   * @var array The headers received from the response.
   */
  protected $responseHeaders = [];

  /**
   * @var int The HTTP status code returned from the server.
   */
  protected $responseHttpStatusCode = 0;

  /**
   * @var \GuzzleHttp\Client The Guzzle client.
   */
  protected static $guzzleClient;

  /**
   * @param \GuzzleHttp\Client|null The Guzzle client.
   */
  public function __construct(Client $guzzleClient = null)
  {
    self::$guzzleClient = $guzzleClient ?: new Client();
  }

  /**
   * The headers returned in the response.
   *
   * @return array
   */
  public function getResponseHeaders()
  {
    return $this->responseHeaders;
  }

  /**
   * The HTTP status response code.
   *
   * @return int
   */
  public function getResponseHttpStatusCode()
  {
    return $this->responseHttpStatusCode;
  }

  /**
   * Sends a request to the server and returns the raw response.
   *
   * @param string $url The endpoint to send the request to.
   * @param string $method The request method.
   * @param array  $parameters The key value pairs to be sent in the body.
   * @param array  $headers The request headers.
   *
   * @return string Raw response from the server.
   *
   * @throws \Facebook\Exceptions\FacebookSDKException
   */
  public function send($url, $method = 'GET', array $parameters = [], array $headers = [])
  {
    $options = [];
    if ($parameters) {
      $options = ['body' => $parameters];
    }

    $request = self::$guzzleClient->createRequest($method, $url, $options);

    foreach($headers as $k => $v) {
      $request->setHeader($k, $v);
    }

    try {
      $rawResponse = self::$guzzleClient->send($request);
    } catch (RequestException $e) {
      if ($e->getPrevious() instanceof AdapterException) {
        throw new FacebookSDKException($e->getMessage(), $e->getCode());
      }
      $rawResponse = $e->getResponse();
    }

    $this->responseHttpStatusCode = $rawResponse->getStatusCode();
    $this->responseHeaders = $rawResponse->getHeaders();

    return $rawResponse->getBody();
  }

}
