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
use ArrayAccess;

/**
 * Class BatchResponse
 * @package Facebook
 */
class FacebookBatchResponse extends FacebookResponse implements IteratorAggregate, ArrayAccess
{

  /**
   * @var FacebookBatchRequest The original entity that made the batch request.
   */
  protected $batchRequest;

  /**
   * @var array An array of FacebookResponse entities.
   */
  protected $responses = [];

  /**
   * Creates a new Response entity.
   *
   * @param FacebookBatchRequest $batchRequest
   * @param FacebookResponse $response
   */
  public function __construct(
    FacebookBatchRequest $batchRequest,
    FacebookResponse $response
  )
  {
    $this->batchRequest = $batchRequest;

    $request = $response->getRequest();
    $body = $response->getBody();
    $httpStatusCode = $response->getHttpStatusCode();
    $headers = $response->getHeaders();
    parent::__construct($request, $body, $httpStatusCode, $headers);

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
    foreach ($responses as $k => $graphResponse) {
      $this->addResponse($graphResponse, $k);
    }
  }

  /**
   * Add a response to the list.
   *
   * @param array $response
   * @param mixed|null $key
   */
  public function addResponse(array $response, $key = null)
  {
    $originalRequest = isset($this->batchRequest[$key]) ? $this->batchRequest[$key] : $this->batchRequest;

    $httpResponseBody = isset($response['body']) ? $response['body'] : null;
    $httpResponseCode = isset($response['code']) ? $response['code'] : null;
    $httpResponseHeaders = isset($response['headers']) ? $response['headers'] : [];

    $this->responses[] = new FacebookResponse(
      $originalRequest,
      $httpResponseBody,
      $httpResponseCode,
      $httpResponseHeaders);
  }

  /**
   * @return @inheritdoc
   */
  public function getIterator()
  {
    return new ArrayIterator($this->responses);
  }

  /**
   * @return @inheritdoc
   */
  public function offsetSet($offset, $value)
  {
    $this->addResponse($value, $offset);
  }

  /**
   * @return @inheritdoc
   */
  public function offsetExists($offset)
  {
    return isset($this->responses[$offset]);
  }

  /**
   * @return @inheritdoc
   */
  public function offsetUnset($offset)
  {
    unset($this->responses[$offset]);
  }

  /**
   * @return @inheritdoc
   */
  public function offsetGet($offset)
  {
    return isset($this->responses[$offset]) ? $this->responses[$offset] : null;
  }

}
