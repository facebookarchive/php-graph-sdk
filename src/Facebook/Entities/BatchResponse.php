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
class BatchResponse extends Response implements IteratorAggregate
{

  /**
   * @var array An array of Response entities.
   */
  protected $responses = [];

  /**
   * Returns an array of Response entities.
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
   */
  public function makeGraphCollection()
  {
    foreach ($this->decodedBody as $graphResponse) {
      $httpResponseCode = isset($graphResponse['code']) ? $graphResponse['code'] : null;
      $httpResponseHeaders = isset($graphResponse['headers']) ? $graphResponse['headers'] : [];
      $httpResponseBody = isset($graphResponse['body']) ? $graphResponse['body'] : null;
      $this->responses[] = new Response($httpResponseCode, $httpResponseHeaders, $httpResponseBody);
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
