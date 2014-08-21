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

use ArrayIterator;
use IteratorAggregate;

/**
 * Class BatchResponse
 * @package Facebook
 */
class FacebookBatchResponse extends FacebookResponse implements IteratorAggregate
{

  /**
   * @var array An array of FacebookResponse entities.
   */
  protected $responses = [];

  /**
   * Creates a new Response entity.
   *
   * @param FacebookResponse $response
   */
  public function __construct(
    FacebookResponse $response
  )
  {
    $app = $response->getApp();
    $httpStatusCode = $response->getHttpStatusCode();
    $headers = $response->getHeaders();
    $body = $response->getBody();
    $accessToken = $response->getAccessToken();
    parent::__construct($app, $httpStatusCode, $headers, $body, $accessToken);

    $responses = $response->getDecodedBody();
    $this->setResponses($responses);
  }

  /**
   * Returns an array of FacebookResponse entities.
   *
   * @return array
   */
  public function getResponses()
  {
    return $this->responses;
  }

  /**
   * The main batch response will be an array of requests so
   * we need to iterate over all the responses.
   *
   * @param array $responses
   */
  public function setResponses(array $responses)
  {
    $this->responses = [];
    foreach ($responses as $graphResponse) {
      $httpResponseCode = isset($graphResponse['code']) ? $graphResponse['code'] : null;
      $httpResponseHeaders = isset($graphResponse['headers']) ? $graphResponse['headers'] : [];
      $httpResponseBody = isset($graphResponse['body']) ? $graphResponse['body'] : null;
      // @TODO Figure out an elegant way to get the access token that was used with this response.
      $accessToken = null;
      $this->responses[] = new FacebookResponse($this->app, $httpResponseCode, $httpResponseHeaders, $httpResponseBody, $accessToken);
    }
  }

  /**
   * Get an iterator for the items.
   *
   * @return ArrayIterator
   */
  public function getIterator()
  {
    return new ArrayIterator($this->responses);
  }

}
