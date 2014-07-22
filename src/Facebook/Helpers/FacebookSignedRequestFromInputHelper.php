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
namespace Facebook\Helpers;

use Facebook\Helpers\AbstractFacebookHelper;
use Facebook\Entities\SignedRequest;

/**
 * Class FacebookSignedRequestFromInputHelper
 * @package Facebook
 */
abstract class FacebookSignedRequestFromInputHelper extends AbstractFacebookHelper
{
  /**
   * @var SignedRequest|null
   */
  protected $signedRequest;

  /**
   * Instantiates a new SignedRequest entity.
   *
   * @param SignedRequest
   */
  final public function getSignedRequest($state = null)
  {
    if (!$this->signedRequest instanceof SignedRequest) {
      $this->signedRequest = new SignedRequest($this->app, $this->getRawSignedRequest(), $state);
    }

    return $this->signedRequest;
  }

  /**
   * @param string|null $state
   *
   * @return AccessToken
   */
  final public function getAccessToken($state = null)
  {
    return $this->getSignedRequest($state)->getAccessToken($this->client);
  }

  /**
   * Returns the user_id if available.
   *
   * @return string|null
   */
  public function getUserId()
  {
    return $this->getSignedRequest()->getUserId();
  }

  /**
   * Get raw signed request from input.
   *
   * @return string|null
   */
  abstract public function getRawSignedRequest();

}
