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

use Facebook\Entities\AccessToken;
use Facebook\Entities\FacebookRequest;
use Facebook\Exceptions\FacebookSDKException;

/**
 * Class FacebookBatchRequest
 * @package Facebook
 */
class FacebookBatchRequest extends FacebookRequest implements \IteratorAggregate
{
  /**
   * @var FacebookRequest[]
   */
  protected $requests = [];

  /**
   * Creates a new FacebookBatchRequest entity.
   *
   * @param FacebookRequest[] $requests
   * @param AccessToken|null $fallbackAccessToken
   */
  public function __construct(array $requests = [], AccessToken $fallbackAccessToken = null, $eTag = null)
  {
    parent::__construct(
      '/',
      'POST',
      [
        'batch' => [],
        'include_headers' => true,
      ],
      $fallbackAccessToken,
      $eTag
    );

    $this->processRequests($requests);
  }

  /**
   * Add a request to this batch request
   *
   * @param FacebookRequest $request
   * @param string $name
   * @param string $dependsOn
   * @param bool $omit
   *
   * @throws FacebookSDKException
   */
  public function add(FacebookRequest $request, $name = null, $dependsOn = null, $omit = true)
  {
    if (!$this->accessToken && !$request->getAccessToken()) {
      throw new FacebookSDKException('You cannot add a request without access token to a batch request that do not have a default one');
    }

    $this->requests[] = $request;

    $compiledHeaders = [];
    $headers = $request->getHeaders();
    foreach ($headers as $name => $value) {
      $compiledHeaders[] = $name.': '.$value;
    }

    $batch = [
      'headers' => $compiledHeaders,
      'method' => $request->getMethod(),
      'relative_url' => $request->getEndpoint(),
    ];

    if ($request->getAccessToken()) {
      $batch['access_token'] = (string)$request->getAccessToken();
    }

    $params = $request->getParameters();
    if ($params) {
      $params = http_build_query($params, null, '&');
      if ('GET' === $request->getMethod()) {
        $batch['relative_url'] .= '?' . $params;
      } else {
        $batch['body'] = $params;
      }
    }

    if ($name) {
      $batch['name'] = $name;
    }

    if ($dependsOn) {
      if (!$this->hasRequest($dependsOn)) {
        throw new FacebookSDKException(sprintf('FacebookRequest named "%s" don\'t exists', $dependsOn));
      }

      $batch['depends_on'] = $dependsOn;
    }

    if (!$omit) {
      $batch['omit_response_on_success'] = false;
    }

    /** @todo Add support for "attached_files" */
    /** @todo Add support for JSONP with "callback" */

    $this->params['batch'][] = $batch;
  }

  /**
   * Get all requests of this batch request
   *
   * @return FacebookRequest[]
   */
  public function getRequests()
  {
    return $this->requests;
  }

  /**
   * {@inheritdoc}
   */
  public function getIterator()
  {
    return new \ArrayIterator($this->requests);
  }

  /**
   * @param FacebookRequest[] $requests
   */
  protected function processRequests(array $requests = [])
  {
    foreach($requests as $request) {
      $this->add($request);
    }
  }

  /**
   * @param string $name
   *
   * @return bool
   */
  protected function hasRequest($name)
  {
    foreach($this->params['batch'] as $request) {
      if (isset($request['name']) && $request['name'] === $name) {
        return true;
      }
    }

    return false;
  }

}