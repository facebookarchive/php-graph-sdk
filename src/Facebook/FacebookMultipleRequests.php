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
namespace Facebook;

/**
 * Class FacebookRequest
 * @package Facebook
 * @author Yassine Guedidi <yassine@guedidi.com>
 */
class FacebookMultipleRequests extends FacebookRequest
{

  /**
   * @var array FacebookRequest array
   */
  private $requests;

  /**
   * FacebookRequest - Returns a new request using the given session.  optional
   *   parameters hash will be sent with the request.  This object is
   *   immutable.
   *
   * @param FacebookSession $session
   * @param array $requests
   * @param bool $headers
   * @param string|null $token
   * @param string|null $version
   * @param string|null $etag
   */
  public function __construct(
    $session, array $requests, $headers = false, $token = null, $version = null, $etag = null
  ) {
    $params = array();
    if (!$headers) {
      $params['include_headers'] = 'false';
    }
    if ($session) {
      $params['access_token'] = $session->getToken();
    }
    if ($token) {
      $params['access_token'] = $token;
    }

    $this->requests = array_filter($requests, function($v) {
      return $v instanceof FacebookRequest;
    });

    $batch = array();
    foreach ($this->requests as $request) {
      $obj = new \stdClass();
      $obj->method = $request->getMethod();

      $url = $request->getPath();
      $request_params = $request->getParameters();
      unset($request_params['access_token']);
      if ('GET' === $obj->method) {
        $url = self::appendParamsToUrl($url, $request_params);
      } else {
        $obj->body = http_build_query($request->getParameters());
      }
      $obj->relative_url = ltrim($url, '/');

      $batch[] = $obj;
    }
    $params['batch'] = json_encode($batch);

    parent::__construct($session, 'POST', '/', $params, $version, $etag);
  }

  /**
   * execute - Makes the request to Facebook and returns the result.
   *
   * @return array
   */
  public function execute() {
    $responses = parent::execute()->getResponse();

    if (!is_array($responses)) {
      return $responses;
    }

    $responsesArray = array();
    foreach ($responses as $i => $response) {
      $headers = array();
      if (isset($response->headers)) {
        foreach ($response->headers as $header) {
          $headers[$header->name] = $header->value;
        }
      }

      $responsesArray[$i] = new FacebookResponse(
        $this->requests[$i],
        json_decode($response->body),
        $response->body,
        $headers,
        $response->code
      );
    }

    return $responsesArray;
  }

}