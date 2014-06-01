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

/**
 * Class FacebookBatchConnection
 * @package Facebook
 */
class FacebookBatchConnection implements FacebookHttpable
{

  /**
   * @var array The headers received from the response
   */
  protected $responseHeaders = array();

  /**
   * @var int The HTTP status code returned from the server
   */
  protected $responseHttpStatusCode = 0;

  /**
   * @var string The client error message
   */
  protected $clientErrorMessage = '';

  /**
   * @var int The client error code
   */
  protected $clientErrorCode = 0;

  /**
   * @var string|boolean The raw response from the server
   */
  protected $rawResponse;

  /**
   * @param array $facebookCurl
   */
  public function __construct($response)
  {
    if (isset($response->headers)) {
      $this->responseHeaders = $response->headers;
    }
    if (isset($response->code)) {
      $this->responseHttpStatusCode = $response->code;
    }
    if (isset($response->body)) {
      $this->rawResponse = $response->body;
    }
  }

  public function addRequestHeader($key, $value) {
    throw new FacebookSDKException(
      'This class does not support this method.', 901
    );
  }

  /**
   * The headers returned in the response
   *
   * @return array
   */
  public function getResponseHeaders() {
    return $this->responseHeaders;
  }

  /**
   * The HTTP status response code
   *
   * @return int
   */
  public function getResponseHttpStatusCode() {
    return $this->responseHttpStatusCode;
  }

  /**
   * The error message returned from the client
   *
   * @return string
   */
  public function getErrorMessage() {
    return $this->clientErrorMessage;
  }

  /**
   * The error code returned by the client
   *
   * @return int
   */
  public function getErrorCode() {
    return $this->clientErrorCode;
  }

  public function send($url, $method = 'GET', $parameters = array()) {
    throw new FacebookSDKException(
      'This class does not support this method.', 901
    );
  }

  /**
   * Returns the response body
   *
   * @return string
   */
  public function getBody()
  {
    return $this->rawResponse;
  }

}
