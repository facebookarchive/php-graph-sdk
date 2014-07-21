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
use Facebook\GraphNodes\Collection;
use Facebook\GraphNodes\GraphObject;

/**
 * Class Response
 * @package Facebook
 */
class Response
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
   * @var Collection The Graph response body as a Collection.
   */
  protected $graphCollection;

  /**
   * @var string The access token that was used.
   */
  protected $accessToken;

  /**
   * @var string The app secret for this request.
   */
  protected $appSecret;

  /**
   * @var FacebookSDKException The exception thrown by this request.
   */
  protected $thrownException;

  /**
   * Creates a new Response entity.
   *
   * @param int|null $httpStatusCode
   * @param array|null $headers
   * @param string|null $body
   * @param AccessToken|string|null $accessToken
   * @param string|null $appSecret
   */
  public function __construct(
    $httpStatusCode = null,
    array $headers = [],
    $body = null,
    $accessToken = null,
    $appSecret = null
  )
  {
    $this->httpStatusCode = $httpStatusCode;
    $this->headers = $headers;
    $this->body = $body;
    $this->setAccessToken($accessToken);
    $this->appSecret = $appSecret;

    $this->decodeBody();
  }

  /**
   * Set the access token for this response.
   *
   * @param AccessToken|string
   *
   * @return Request
   */
  public function setAccessToken($accessToken)
  {
    $this->accessToken = $accessToken instanceof AccessToken
      ? (string) $accessToken
      : $accessToken;
    return $this;
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
   * Return the decoded body as a Collection.
   *
   * @return Collection|null
   */
  public function getCollection()
  {
    return $this->graphCollection;
  }

  /**
   * Makes a Collection from the decoded body.
   */
  public function makeGraphCollection()
  {
    if (is_array($this->decodedBody)) {
      $this->graphCollection = GraphObject::make($this->decodedBody);
    }
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
   * Get the app secret proof that was used for this response.
   *
   * @return string
   */
  public function getAppSecretProof()
  {
    return AppSecretProof::make($this->accessToken, $this->appSecret);
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
    $this->thrownException = FacebookResponseException::create($this);
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

    $this->makeGraphCollection();

    if ($this->isError()) {
      $this->makeException();
    }
  }

}
