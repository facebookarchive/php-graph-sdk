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

use Facebook\Entities\FacebookBatchRequest;
use Facebook\Entities\FacebookResponse;

/**
 * Class FacebookBatchResponse
 * @package Facebook
 */
class FacebookBatchResponse extends FacebookResponse implements \IteratorAggregate
{
  /**
   * @var FacebookBatchRequest The batch request that generate this batch response
   */
  protected $request;

  /**
   * @var FacebookResponse[] Generated responses from the batch request
   */
  protected $responses;

  /**
   * Creates a new FacebookBatchResponse entity.
   *
   * @param FacebookBatchRequest $request
   * @param FacebookResponse $response
   */
  public function __construct(FacebookBatchRequest $request, FacebookResponse $response)
  {
    parent::__construct(
      $request,
      $response->getRaw(),
      $response->getStatusCode(),
      $response->getHeaders()
    );

    $this->responses = $this->processResponse($response);
  }

  /**
   * Get all responses of this batch response
   *
   * @return FacebookResponse[]
   */
  public function getResponses()
  {
    return $this->responses;
  }

  /**
   * {@inheritdoc}
   */
  public function getIterator()
  {
    return new \ArrayIterator($this->responses);
  }

  protected function processResponse(FacebookResponse $response)
  {
    foreach ($this->request as $i => $request) {
      $response = $this->value[$i];

      if (null === $response) {
        $this->responses[] = null;
        continue;
      }

      $this->responses[] = new FacebookResponse(
        $request,
        isset($response['body']) ? $response['body'] : '{}',
        isset($response['code']) ? $response['code'] : null,
        isset($response['headers']) ? $response['headers'] : []
      );
    }
  }

}