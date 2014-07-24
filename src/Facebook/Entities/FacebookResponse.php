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

use Facebook\Entities\FacebookRequest;
use Facebook\GraphNodes\GraphObject;
use Facebook\Exceptions\FacebookSDKException;

/**
 * Class FacebookResponse
 * @package Facebook
 */
class FacebookResponse implements \ArrayAccess
{
  /**
   * @var FacebookRequest The resquest that generate this response
   */
  protected $request;

  /**
   * @var int The HTTP status code response from Graph.
   */
  protected $statusCode;

  /**
   * @var array The headers returned from Graph.
   */
  protected $headers;

  /**
   * @var string The raw body of the response from Graph.
   */
  protected $raw;

  /**
   * @var mixed The decoded body of the Graph response.
   */
  protected $value;

  /**
   * Creates a new Response entity.
   *
   * @param FacebookRequest $request
   * @param string $raw
   * @param int|null $statusCode
   * @param array $headers
   */
  public function __construct(
    FacebookRequest $request,
    $raw,
    $statusCode = null,
    array $headers = []
  )
  {
    $this->request = $request;
    $this->raw = $raw;
    $this->statusCode = $statusCode;
    $this->headers = $headers;
    $this->value = $this->decodeRaw($raw);
  }

  /**
   * Return the request that generate this response
   *
   * @return FacebookRequest
   */
  public function getRequest()
  {
    return $this->request;
  }

  /**
   * Return the raw body response.
   *
   * @return string
   */
  public function getRaw()
  {
    return $this->raw;
  }

  /**
   * Return the HTTP status code for this response.
   *
   * @return int
   */
  public function getStatusCode()
  {
    return $this->statusCode;
  }

  /**
   * Return the HTTP headers for this response.
   *
   * @return array
   */
  public function getHeaders()
  {
    return $this->headers;
  }

  /**
   * Return the decoded body response.
   *
   * @return mixed
   */
  public function getValue()
  {
    return $this->value;
  }

  /**
   * Get the ETag associated with the response.
   *
   * @return string|null
   */
  public function getETag()
  {
    return isset($this->headers['ETag']) ? $this->headers['ETag'] : null;
  }

  /**
   * Returns true if this response is an eTag hit
   *
   * @return bool
   */
  public function isETagHit()
  {
    return 304 == $this->statusCode;
  }

  /**
   * Get the version of Graph that returned this response.
   *
   * @return string|null
   */
  public function getGraphVersion()
  {
    return isset($this->headers['Facebook-API-Version']) ? $this->headers['Facebook-API-Version'] : null;
  }

  /**
   * Returns true if Graph returned an error message.
   *
   * @return bool
   */
  public function isError()
  {
    return isset($this->value['error']);
  }

  /**
   * Gets the result as a GraphObject.  If a type is specified, returns the
   *   strongly-typed subclass of GraphObject for the data.
   *
   * @param string $type
   *
   * @return mixed
   */
  public function getGraphObject($type = 'Facebook\GraphNodes\GraphObject') {
    return (new GraphObject($this->value))->cast($type);
  }

  /**
   * Returns an array of GraphObject returned by the request.  If a type is
   * specified, returns the strongly-typed subclass of GraphObject for the data.
   *
   * @param string $type
   *
   * @return mixed
   */
  public function getGraphObjectList($type = 'Facebook\GraphNodes\GraphObject') {
    $out = array();
    for ($i = 0; $i < count($this->value['data']); $i++) {
      $out[] = (new GraphObject($this->value['data'][$i]))->cast($type);
    }

    return $out;
  }

  /**
   * If this response has paginated data, returns the FacebookRequest for the
   *   next page, or null.
   *
   * @return FacebookRequest|null
   */
  public function getRequestForNextPage()
  {
    return $this->handlePagination('next');
  }

  /**
   * If this response has paginated data, returns the FacebookRequest for the
   *   previous page, or null.
   *
   * @return FacebookRequest|null
   */
  public function getRequestForPreviousPage()
  {
    return $this->handlePagination('previous');
  }

  /**
   * Convert the raw response into an array if possible.
   *
   * Graph will return 3 types of responses:
   * - JSON(P)
   * - application/x-www-form-urlencoded key/value pairs
   * - The string "true"
   * ... And sometimes nothing :/ but that'd be a bug.
   *
   * @param string $raw
   *
   * @return array|bool
   */
  protected function decodeRaw($raw)
  {
    $body = json_decode($raw, true);

    if ($body === null) {
      $body = [];
      parse_str($raw, $body);
    }

    return $body;
  }

  /**
   * Returns the FacebookRequest for the previous or next page, or null.
   *
   * @param string $direction
   *
   * @return FacebookRequest|null
   */
  protected function handlePagination($direction) {
    if (!isset($this->body['paging'][$direction])) {
      return;
    }

    $url = parse_url($this->body['paging'][$direction]);
    $params = array();
    parse_str($url['query'], $params);

    return new FacebookRequest(
      $this->request->getEndpoint(),
      $this->request->getMethod(),
      array_merge($this->request->getParameters(), $params),
      $this->request->getAccessToken()
    );
  }

  public function offsetExists($offset)
  {
    return isset($this->value[$offset]);
  }

  public function offsetGet($offset)
  {
    return $this->value[$offset];
  }

  public function offsetSet($offset, $value)
  {
    throw new FacebookSDKException('FacebookResponse object can\'t be modified');
  }

  public function offsetUnset($offset)
  {
    throw new FacebookSDKException('FacebookResponse object can\'t be modified');
  }

}