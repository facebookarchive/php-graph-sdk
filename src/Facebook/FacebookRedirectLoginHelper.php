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
 * Class FacebookRedirectLoginHelper
 * @package Facebook
 * @author Fosco Marotto <fjm@fb.com>
 * @author David Poll <depoll@fb.com>
 */
class FacebookRedirectLoginHelper
{

  /**
   * @var string The application id
   */
  private $appId;

  /**
   * @var string The application secret
   */
  private $appSecret;

  /**
   * @var string The redirect URL for the application
   */
  private $redirectUrl;

  /**
   * @var string Prefix to use for session variables
   */
  private $sessionPrefix = 'FBRLH_';

  /**
   * @var string State token for CSRF validation
   */
  protected $state;

  /**
   * Constructs a RedirectLoginHelper for a given appId and redirectUrl.
   *
   * @param string $redirectUrl The URL Facebook should redirect users to
   *                            after login
   * @param string $appId The application id
   * @param string $appSecret The application secret
   */
  public function __construct($redirectUrl, $appId = null, $appSecret = null)
  {
    $this->appId = FacebookSession::_getTargetAppId($appId);
    $this->appSecret = FacebookSession::_getTargetAppSecret($appSecret);
    $this->redirectUrl = $redirectUrl;
  }

  /**
   * Stores CSRF state and returns a URL to which the user should be sent to
   *   in order to continue the login process with Facebook.  The
   *   provided redirectUrl should invoke the handleRedirect method.
   *
   * @param array $scope List of permissions to request during login
   * @param string $version Optional Graph API version if not default (v2.0)
   *
   * @return string
   */
  public function getLoginUrl($scope = array(), $version = null)
  {
    $version = ($version ?: FacebookRequest::GRAPH_API_VERSION);
    $this->state = md5(uniqid(mt_rand(), true));
    $this->storeState($this->state);
    $params = array(
      'client_id' => $this->appId,
      'redirect_uri' => $this->redirectUrl,
      'state' => $this->state,
      'sdk' => 'php-sdk-' . FacebookRequest::VERSION,
      'scope' => implode(',', $scope)
    );
    return 'https://www.facebook.com/' . $version . '/dialog/oauth?' .
      http_build_query($params);
  }

  /**
   * Returns the URL to send the user in order to log out of Facebook.
   *
   * @param FacebookSession $session The session that will be logged out
   * @param string $next The url Facebook should redirect the user to after
   *   a successful logout
   *
   * @return string
   */
  public function getLogoutUrl($session, $next)
  {
    $params = array(
      'next' => $next,
      'access_token' => $session->getToken()
    );
    return 'https://www.facebook.com/logout.php?' . http_build_query($params);
  }

  /**
   * Handles a response from Facebook, including a CSRF check, and returns a
   *   FacebookSession.
   *
   * @return FacebookSession|null
   */
  public function getSessionFromRedirect()
  {
    $this->loadState();
    if ($this->isValidRedirect()) {
      $params = array(
        'client_id' => FacebookSession::_getTargetAppId($this->appId),
        'redirect_uri' => $this->redirectUrl,
        'client_secret' =>
          FacebookSession::_getTargetAppSecret($this->appSecret),
        'code' => $this->getCode()
      );
      $response = (new FacebookRequest(
        FacebookSession::newAppSession($this->appId, $this->appSecret),
        'GET',
        '/oauth/access_token',
        $params
      ))->execute()->getResponse();
      if (isset($response['access_token'])) {
        return new FacebookSession($response['access_token']);
      }
    }
    return null;
  }

  /**
   * Check if a redirect has a valid state.
   *
   * @return bool
   */
  protected function isValidRedirect()
  {
    return $this->getCode() && isset($_GET['state'])
        && $_GET['state'] == $this->state;
  }

  /**
   * Return the code.
   *
   * @return string|null
   */
  protected function getCode()
  {
    return isset($_GET['code']) ? $_GET['code'] : null;
  }

  /**
   * Stores a state string in session storage for CSRF protection.
   * Developers should subclass and override this method if they want to store
   *   this state in a different location.
   *
   * @param string $state
   *
   * @throws FacebookSDKException
   */
  protected function storeState($state)
  {
    if (session_status() !== PHP_SESSION_ACTIVE) {
      throw new FacebookSDKException(
        'Session not active, could not store state.'
      );
    }
    $_SESSION[$this->sessionPrefix . 'state'] = $state;
  }

  /**
   * Loads a state string from session storage for CSRF validation.  May return
   *   null if no object exists.  Developers should subclass and override this
   *   method if they want to load the state from a different location.
   *
   * @return string|null
   *
   * @throws FacebookSDKException
   */
  protected function loadState()
  {
    if (session_status() !== PHP_SESSION_ACTIVE) {
      throw new FacebookSDKException(
        'Session not active, could not load state.'
      );
    }
    if (isset($_SESSION[$this->sessionPrefix . 'state'])) {
      $this->state = $_SESSION[$this->sessionPrefix . 'state'];
      return $this->state;
    }
    return null;
  }

}