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
 * Class FacebookJavaScriptLoginHelper
 * @package Facebook
 * @author Fosco Marotto <fjm@fb.com>
 * @author David Poll <depoll@fb.com>
 */
class FacebookJavaScriptLoginHelper
{

  private $appId;

  /**
   * Creates a JavaScript Login Helper for the given application id, or the
   *   default if not provided.
   *
   * @param string $appId
   *
   * @throws FacebookSDKException
   */
  public function __construct($appId = null)
  {
    $this->appId = FacebookSession::_getTargetAppId($appId);
    if (!$this->appId) {
      throw new FacebookSDKException(
        'You must provide or set a default application id.'
      );
    }
  }

  /**
   * Gets a FacebookSession from the cookies/params set by the Facebook
   *   JavaScript SDK.
   *
   * @return FacebookSession|null
   */
  public function getSession()
  {
    if ($signedRequest = $this->getSignedRequest()) {
      return FacebookSession::newSessionFromSignedRequest($signedRequest);
    }
    return null;
  }

  /**
   * Get signed request
   *
   * @return string|null
   */
  protected function getSignedRequest()
  {
    if (isset($_COOKIE['fbsr_' . $this->appId])) {
      return $_COOKIE['fbsr_' . $this->appId];
    }
    return null;
  }

}