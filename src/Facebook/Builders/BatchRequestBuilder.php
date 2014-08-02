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
use Facebook\Entities\FacebookBatchRequest;
use Facebook\Builders\RequestBuilder;

class BatchRequestBuilder extends RequestBuilder
{
  private $request;
  private $current;
  private $fallbackAccessToken;

  public function __construct(FacebookClient $client, FacebookApp $app, AccessToken $fallbackAccessToken = null)
  {
    parent::__construct($client, $app);

    $this->request = new FacebookBatchRequest();
    $this->current = $this->initRequest();
    $this->fallbackAccessToken = $fallbackAccessToken;
  }

  public function withEndpoint($endpoint)
  {
    $this->current['endpoint'] = $endpoint;

    return $this;
  }

  public function withMethod($method)
  {
    $this->current['method'] = $method;

    return $this;
  }

  public function withParams(array $params)
  {
    $this->current['params'] = $params;

    return $this;
  }

  public function withAccessToken(AccessToken $accessToken)
  {
    $this->current['accessToken'] = $accessToken;

    return $this;
  }

  public function withETag($eTag)
  {
    $this->current['eTag'] = $eTag;

    return $this;
  }

  public function withName($name)
  {
    $this->current['name'] = $name;

    return $this;
  }

  public function dependsOn($name)
  {
    $this->current['dependsOn'] = $name;

    return $this;
  }

  public function omitInResult($omit = true)
  {
    $this->current['omit'] = (bool)$omit;

    return $this;
  }

  public function get($endpoint)
  {
    $this->nextRequest();

    $this->current['endpoint'] = $endpoint;
    $this->current['method'] = 'GET';

    return $this;
  }

  public function post($endpoint)
  {
    $this->nextRequest();

    $this->current['endpoint'] = $endpoint;
    $this->current['method'] = 'POST';

    return $this;
  }

  public function delete($endpoint)
  {
    $this->nextRequest();

    $this->current['endpoint'] = $endpoint;
    $this->current['method'] = 'DELETE';

    return $this;
  }

  public function send()
  {
    $this->nextRequest();

    return $this->client->batch($this->request, $this->fallbackAccessToken);
  }

  private function nextRequest()
  {
    $this->request->add(new FacebookRequest(
      $this->current['endpoint'],
      $this->current['method'],
      $this->current['params'],
      $this->current['accessToken'],
      $this->current['eTag']
    ), $this->current['name'], $this->current['dependsOn'], $this->current['omit']);

    $this->current = $this->initRequest();
  }

  private function initRequest()
  {
    $this->current = [
      'endpoint' => null,
      'method' => 'GET',
      'params' => [],
      'accessToken' => null,
      'eTag' => '',
      'name' => null,
      'dependsOn' => null,
      'omit' => true,
    ];
  }
}
