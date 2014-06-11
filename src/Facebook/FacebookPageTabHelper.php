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
 * Class FacebookPageTabHelper
 * @package Facebook
 * @author Fosco Marotto <fjm@fb.com>
 */
class FacebookPageTabHelper extends FacebookCanvasLoginHelper
{

  /**
   * @var FacebookSession
   */
  public $session = null;

  /**
   * @var array|null
   */
  private $parsedSignedRequest = null;

  /**
   * @var array|null
   */
  private $pageData = null;

  /**
   * Initialize the Page Tab helper and process available signed request data
   */
  public function __construct()
  {
    $signedRequest = $this->getSignedRequest();
    if ($signedRequest) {
      $this->parsedSignedRequest = $this->parseSignedRequest($signedRequest);
      if (isset($this->parsedSignedRequest['page'])) {
        $this->pageData = $this->parsedSignedRequest['page'];
      }
    }
  }

  /**
   * Returns true if the page is liked by the user.
   *
   * @return bool
   */
  public function isLiked()
  {
    if (isset($this->pageData['liked']) && $this->pageData['liked'] == 'true') {
      return true;
    }
    return false;
  }

  /**
   * Returns true if the user is an admin.
   *
   * @return bool
   */
  public function isAdmin()
  {
    if (isset($this->pageData['admin']) && $this->pageData['admin'] == 'true') {
      return true;
    }
    return false;
  }

  /**
   * Returns the page id if available.
   *
   * @return int|null
   */
  public function getPageId()
  {
    if (isset($this->pageData['id'])) {
      return $this->pageData['id'];
    }
    return null;
  }

  /**
   * Returns the user_id if available.
   *
   * @return string|null
   */
  public function getUserId()
  {
    if (isset($this->parsedSignedRequest['user_id'])) {
      return $this->parsedSignedRequest['user_id'];
    }
    return null;
  }
  
  /**
   * Returns the app_data if available.
   *
   * @return object|null
   */
  public function getAppData()
  {
    if (isset($this->parsedSignedRequest['app_data'])) {
      return $this->parsedSignedRequest['app_data'];
    }
    return null;
  }

  /**
   * Parses a signed request.
   *
   * @param string $signedRequest
   *
   * @return array
   *
   * @throws FacebookSDKException
   */
  private function parseSignedRequest($signedRequest)
  {
    if (strpos($signedRequest, '.') !== false) {
      list($encodedSig, $encodedData) = explode('.', $signedRequest, 2);
      $sig = FacebookSession::_base64UrlDecode($encodedSig);
      $data = json_decode(FacebookSession::_base64UrlDecode($encodedData), true);
      if (isset($data['algorithm']) && $data['algorithm'] === 'HMAC-SHA256') {
        $expectedSig = hash_hmac(
          'sha256', $encodedData, FacebookSession::_getTargetAppSecret(), true
        );
        if (strlen($sig) !== strlen($expectedSig)) {
          throw new FacebookSDKException(
            'Invalid signature on signed request.', 602
          );
        }
        $validate = 0;
        for ($i = 0; $i < strlen($sig); $i++) {
          $validate |= ord($expectedSig[$i]) ^ ord($sig[$i]);
        }
        if ($validate !== 0) {
          throw new FacebookSDKException(
            'Invalid signature on signed request.', 602
          );
        }
        return $data;
      } else {
        throw new FacebookSDKException(
          'Invalid signed request, using wrong algorithm.', 605
        );
      }
    } else {
      throw new FacebookSDKException(
        'Malformed signed request.', 606
      );
    }
  }

}
