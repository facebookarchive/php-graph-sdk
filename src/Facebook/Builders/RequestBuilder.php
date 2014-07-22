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
namespace Facebook\Builders;

use Facebook\FacebookClient;
use Facebook\Entities\FacebookApp;
use Facebook\Entities\FacebookRequest;
use Facebook\Entities\AccessToken;

class RequestBuilder
{
  protected $client;
  protected $app;

  private $endpoint = null;
  private $method = 'GET';
  private $params = [];
  private $accessToken = null;
  private $eTag = '';

  public function __construct(FacebookClient $client, FacebookApp $app)
  {
    $this->client = $client;
    $this->app = $app;
  }

  public function withEndpoint($endpoint)
  {
    $this->endpoint = $endpoint;

    return $this;
  }

  public function withMethod($method)
  {
    $this->method = $method;

    return $this;
  }

  public function withParams(array $params)
  {
    $this->params = $params;

    return $this;
  }

  public function withAccessToken(AccessToken $accessToken)
  {
    $this->accessToken = $accessToken;

    return $this;
  }

  public function withETag($eTag)
  {
    $this->eTag = $eTag;

    return $this;
  }

  public function get($endpoint)
  {
    $this->endpoint = $endpoint;
    $this->method = 'GET';

    return $this;
  }

  public function post($endpoint)
  {
    $this->endpoint = $endpoint;
    $this->method = 'POST';

    return $this;
  }

  public function delete($endpoint)
  {
    $this->endpoint = $endpoint;
    $this->method = 'DELETE';

    return $this;
  }

  public function send()
  {
    return $this->client->handle(new FacebookRequest(
      $this->endpoint,
      $this->method,
      $this->params,
      $this->accessToken,
      $this->eTag
    ));
  }
}
