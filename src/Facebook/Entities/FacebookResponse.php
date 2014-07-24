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

use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;
use Facebook\GraphNodes\GraphObject;

/**
 * Class Response
 * @package Facebook
 */
class FacebookResponse
{

  /**
   * @var int The HTTP status code response from Graph.
   */
  protected $httpStatusCode;

  /**
   * @var array The headers returned from Graph.
   */
  protected $headers;

  /**
   * @var string The raw body of the response from Graph.
   */
  protected $body;

  /**
   * @var mixed The decoded body of the Graph response.
   */
  protected $decodedBody;

  /**
   * @var string The access token that was used.
   */
  protected $accessToken;

  /**
   * @var FacebookApp The facebook app entity.
   */
  protected $app;

  /**
   * @var FacebookSDKException The exception thrown by this request.
   */
  protected $thrownException;

  /**
   * Creates a new Response entity.
   *
   * @param FacebookApp $app
   * @param int|null $httpStatusCode
   * @param array|null $headers
   * @param string|null $body
   * @param string|null $accessToken
   */
  public function __construct(
    FacebookApp $app,
    $httpStatusCode = null,
    array $headers = [],
    $body = null,
    $accessToken = null
  )
  {
    $this->app = $app;
    $this->httpStatusCode = $httpStatusCode;
    $this->headers = $headers;
    $this->body = $body;
    $this->accessToken = $accessToken;

    $this->decodeBody();
  }

  /**
   * Return the FacebookApp entity used for this response.
   *
   * @return FacebookApp
   */
  public function getApp()
  {
    return $this->app;
  }

  /**
   * Return the access token that was used for this response.
   *
   * @return string
   */
  public function getAccessToken()
  {
    return $this->accessToken;
  }

  /**
   * Return the HTTP status code for this response.
   *
   * @return int
   */
  public function getHttpStatusCode()
  {
    return $this->httpStatusCode;
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
   * Return the raw body response.
   *
   * @return string
   */
  public function getBody()
  {
    return $this->body;
  }

  /**
   * Return the decoded body response.
   *
   * @return mixed
   */
  public function getDecodedBody()
  {
    return $this->decodedBody;
  }

  /**
   * @TODO Make this smarter - casting recursively.
   *
   * Gets the result as a GraphObject.  If a type is specified, returns the
   *   strongly-typed subclass of GraphObject for the data.
   *
   * @param string $type
   *
   * @return mixed
   */
  public function getGraphObject($type = 'Facebook\GraphNodes\GraphObject')
  {
    return (new GraphObject($this->decodedBody))->cast($type);
  }

  /**
   * @TODO This will soon return a GraphList object.
   *
   * Returns an array of GraphObject returned by the request.  If a type is
   * specified, returns the strongly-typed subclass of GraphObject for the data.
   *
   * @param string $type
   *
   * @return array|null
   */
  public function getGraphObjectList($type = 'Facebook\GraphNodes\GraphObject')
  {
    if (!isset($this->decodedBody['data'])) {
      return null;
    }
    $out = [];
    foreach ($this->decodedBody['data'] as $graphObject) {
      $out[] = (new GraphObject($graphObject))->cast($type);
    }
    return $out;
  }

  /**
   * Get the app secret proof that was used for this response.
   *
   * @return string
   */
  public function getAppSecretProof()
  {
    return AppSecretProof::make($this->accessToken, $this->app->getSecret());
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
   * @return boolean
   */
  public function isError()
  {
    return isset($this->decodedBody['error']);
  }

  /**
   * Throws the exception.
   *
   * @throws FacebookSDKException
   */
  public function throwException()
  {
    throw $this->thrownException;
  }

  /**
   * Instantiates an exception to be thrown later.
   */
  public function makeException()
  {
    $this->thrownException = FacebookResponseException::create(
                                $this->body,
                                $this->decodedBody,
                                $this->httpStatusCode);
  }

  /**
   * Returns the exception that was thrown for this request.
   *
   * @return FacebookSDKException|null
   */
  public function getThrownException()
  {
    return $this->thrownException;
  }

  /**
   * Convert the raw response into an array if possible.
   *
   * Graph will return 3 types of responses:
   * - JSON(P)
   * - application/x-www-form-urlencoded key/value pairs
   * - The string "true"
   * ... And sometimes nothing :/ but that'd be a bug.
   */
  public function decodeBody()
  {
    $this->decodedBody = json_decode($this->body, true);

    if ($this->decodedBody === null) {
      $this->decodedBody = [];
      parse_str($this->body, $this->decodedBody);
    } elseif (is_bool($this->decodedBody)) {
      $this->decodedBody = ['was_successful' => $this->decodedBody];
    }

    if ($this->isError()) {
      $this->makeException();
    }
  }

}
