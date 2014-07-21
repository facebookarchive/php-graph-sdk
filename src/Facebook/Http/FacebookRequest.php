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

use Facebook\Entities\AccessToken;
use Facebook\Entities\Response;
use Facebook\GraphNodes\GraphObject;

/**
 * Class FacebookRequest
 * @package Facebook
 */
class FacebookRequest extends BaseRequest
{

  /**
   * {@inheritdoc}
   *
   * @return GraphObject
   */
  public function get($endpoint = null)
  {
    parent::get($endpoint);

    return $this->prepareRequest();
  }

  /**
   * {@inheritdoc}
   *
   * @return GraphObject
   */
  public function post($endpoint = null, $params = null)
  {
    parent::post($endpoint, $params);

    return $this->prepareRequest();
  }

  /**
   * {@inheritdoc}
   *
   * @return GraphObject
   */
  public function delete($endpoint = null)
  {
    parent::delete($endpoint);

    return $this->prepareRequest();
  }

  /**
   * {@inheritdoc}
   *
   * @return GraphObject
   */
  protected function prepareRequest()
  {
    $request = $this->getCurrentRequest();

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
   * @return GraphObject
   */
  public function makeResponseEntity($httpStatusCode, array $headers, $body, $accessToken = null)
  {
    $this->lastResponse = new Response($httpStatusCode, $headers, $body, $accessToken);
    return $this->lastResponse->getCollection();
  }

}
