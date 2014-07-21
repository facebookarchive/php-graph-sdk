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
namespace Facebook\Http;

use Facebook\Entities\BatchRequest;
use Facebook\Entities\BatchResponse;
use Facebook\Facebook;
use Facebook\Entities\AccessToken;
use Facebook\Exceptions\FacebookSDKException;

/**
 * Class FacebookBatchRequest
 * @package Facebook
 */
class FacebookBatchRequest extends BaseRequest
{

  /**
   * @var string|null The root access token for the batch request.
   */
  protected $batchAccessToken;

  /**
   * Set the root access token to use with this batch request.
   *
   * @param AccessToken|string|null $accessToken
   */
  public function setBatchRequestAccessToken($accessToken)
  {
    $this->batchAccessToken = $accessToken instanceof AccessToken
      ? (string) $accessToken
      : $accessToken;
  }

  /**
   * Return the access token for this request or fallback to default.
   *
   * @return string
   */
  public function getBatchRequestAccessToken()
  {
    return (string) Facebook::getAccessToken($this->batchAccessToken);
  }

  /**
   * Validate that an access token exists for this request.
   *
   * @throws FacebookSDKException
   */
  public function validateBatchAccessToken()
  {
    $accessToken = $this->getBatchRequestAccessToken();
    if (!$accessToken) {
      throw new FacebookSDKException(
        'You must provide an access token.'
      );
    }
  }

  /**
   * Validate that all the requests have an access token set.
   *
   * @return boolean
   */
  public function allRequestHaveAnAccessToken()
  {
    foreach ($this->requests as $request) {
      if (!$request->hasAccessToken()) {
        return false;
      }
    }
    return true;
  }

  /**
   * Instantiates a new Request entity.
   *
   * @param AccessToken|string|null $accessToken
   * @param string|null $name The name of the request so that
   *                          it can be referenced later.
   *
   * @return FacebookBatchRequest
   */
  public function newRequest($accessToken = null, $name = null)
  {
    $request = new BatchRequest($accessToken);
    $this->setNextBatchRequest($request);

    $this->getCurrentRequest()->setBatchRequestName($name);

    return $this;
  }

  /**
   * Sets the next instantiation of the BatchRequest entity.
   *
   * @param BatchRequest $request
   */
  public function setNextBatchRequest(BatchRequest $request)
  {
    parent::setNextRequest($request);
  }

  /**
   * Get the current BatchRequest entity.
   * We need this for "BatchRequest" return type in the doc block.
   *
   * @return BatchRequest
   */
  public function getCurrentRequest()
  {
    return parent::getCurrentRequest();
  }

  /**
   * Format all Request entities in a batch request and send to Graph.
   *
   * @return BatchResponse
   */
  public function send()
  {
    $params = [
      'batch' => $this->prepareRequest(),
      'include_headers' => true,
    ];

    $accessToken = '';
    if (!$this->allRequestHaveAnAccessToken()) {
      $this->validateBatchAccessToken();
      $accessToken = $this->getBatchRequestAccessToken();
    }

    $request = new BatchRequest($accessToken);
    $request->setMethod('POST')
            ->setParams($params);

    return $this->sendRequest(
              $request->getMethod(),
              $request->getUrl(),
              $request->getPostParams(),
              $request->getHeaders()
    );
  }

  /**
   * Return the proper response.
   *
   * @param int $httpStatusCode
   * @param array $headers
   * @param string $body
   * @param AccessToken|string|null $accessToken
   *
   * @return BatchResponse
   */
  public function makeResponseEntity($httpStatusCode, array $headers, $body, $accessToken = null)
  {
    return $this->lastResponse = new BatchResponse($httpStatusCode, $headers, $body, $accessToken);
  }

  /**
   * Iterates over all the Request entities and generates a
   * batch JSON string.
   *
   * @return string A JSON string of all the batch requests.
   */
  protected function prepareRequest()
  {
    $this->validateBatchRequestCount();

    $requests = [];
    foreach ($this->requests as $request) {
      $requests[] = static::requestEntityToBatchArray($request);
    }

    return json_encode($requests);
  }

  /**
   * Validate the request count before sending them as a batch.
   *
   * @throws FacebookSDKException
   */
  public function validateBatchRequestCount()
  {
    $batchCount = count($this->requests);
    if ($batchCount === 0) {
      throw new FacebookSDKException(
        'There are no batch requests to send.'
      );
    } elseif ($batchCount > 50) {
      // Per: https://developers.facebook.com/docs/graph-api/making-multiple-requests#limits
      throw new FacebookSDKException(
        'You cannot send more than 50 batch requests at a time.'
      );
    }
  }

  /**
   * Converts a Request entity into an array that is batch-friendly.
   *
   * @param BatchRequest $request The request entity to convert.
   *
   * @return array
   */
  public static function requestEntityToBatchArray(BatchRequest $request)
  {
    $compiledHeaders = [];
    $headers = $request->getHeaders();
    foreach ($headers as $name => $value) {
      $compiledHeaders[] = $name.': '.$value;
    }

    $batch = [
      'headers' => $compiledHeaders,
      'method' => $request->getMethod(),
      'relative_url' => $request->getUrl(),
    ];

    $params = $request->getPostParams();
    if ($params) {
      $batch['body'] = http_build_query($params, null, '&');
    }

    if ($request->getBatchRequestName()) {
      $batch['name'] = $request->getBatchRequestName();
    }

    // @TODO Add support for "omit_response_on_success"
    // @TODO Add support for "depends_on"
    // @TODO Add support for "attached_files"
    // @TODO Add support for JSONP with "callback"

    return $batch;
  }

  /**
   * Pass-along method to set the name of the batch request.
   *
   * @param string $requestName
   *
   * @return FacebookBatchRequest
   */
  public function withRequestName($requestName)
  {
    $this->getCurrentRequest()
         ->setBatchRequestName($requestName);

    return $this;
  }

}
