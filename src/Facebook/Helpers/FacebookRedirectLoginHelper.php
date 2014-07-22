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

use Facebook\FacebookClient;
use Facebook\Helpers\AbstractFacebookHelper;
use Facebook\Entities\AccessToken;
use Facebook\Entities\Code;
use Facebook\Exceptions\FacebookSDKException;

/**
 * Class FacebookRedirectLoginHelper
 * @package Facebook
 * @author Fosco Marotto <fjm@fb.com>
 * @author David Poll <depoll@fb.com>
 */
class FacebookRedirectLoginHelper extends AbstractFacebookHelper
{
  /**
   * @var string Prefix to use for session variables
   */
  protected $sessionPrefix = 'FBRLH_';

  /**
   * @var boolean Toggle for PHP session status check
   */
  protected $checkForSessionStatus = true;

  /**
   * Stores CSRF state and returns a URL to which the user should be sent to
   *   in order to continue the login process with Facebook.  The
   *   provided redirectUrl should invoke the handleRedirect method.
   *   If a previous request to certain permission(s) was declined
   *   by the user, rerequest should be set to true or the permission(s)
   *   will not be re-asked.
   *
   * @param string $redirectUrl The URL Facebook should redirect users to
   *                            after login
   * @param array $scope List of permissions to request during login
   * @param boolean $rerequest Whether this is the re-request to the previously
   * 				declined permissions
   * @param string $version Optional Graph API version if not default (v2.0)
   *
   * @return string
   */
  public function getLoginUrl($redirectUrl, $scope = array(), $rerequest = false)
  {
    $version = $this->client->getGraphVersion();
    $state = $this->generateState();
    $this->storeState($state);
    $params = array(
      'client_id' => $this->app->getId(),
      'redirect_uri' => $redirectUrl,
      'state' => $state,
      'sdk' => 'php-sdk-' . FacebookClient::VERSION,
      'scope' => implode(',', $scope)
    );
	
    if ($rerequest) {
      $params['auth_type'] = 'rerequest';
    }

    return 'https://www.facebook.com/' . $version . '/dialog/oauth?' .
      http_build_query($params, null, '&amp;');
  }

  /**
   * Returns the URL to send the user in order to log out of Facebook.
   *
   * @param AccessToken $accessToken The access token that will be logged out
   * @param string $next The url Facebook should redirect the user to after
   *   a successful logout
   *
   * @return string
   */
  public function getLogoutUrl(AccessToken $accessToken, $next)
  {
    $params = array(
      'next' => $next,
      'access_token' => $accessToken->getValue(),
    );
    return 'https://www.facebook.com/logout.php?' . http_build_query($params, null, '&amp;');
  }

  /**
   * Handles a response from Facebook, including a CSRF check, and returns a
   *   FacebookSession.
   *
   * @return AccessToken|null
   */
  public function getAccessToken()
  {
    if ($this->isValidRedirect()) {
      $redirectUri = $this->getFilteredUri($this->getCurrentUri());
      return (new Code($this->app, $this->getRawCode()))->getAccessToken($this->client, $redirectUri);
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
    return $this->getRawCode() && isset($_GET['state'])
        && $_GET['state'] == $this->loadState();
  }

  /**
   * Return the code.
   *
   * @return string|null
   */
  protected function getRawCode()
  {
    return isset($_GET['code']) ? $_GET['code'] : null;
  }

  /**
   * Generate a state string for CSRF protection.
   *
   * @return string
   */
  protected function generateState()
  {
    return $this->random(16);
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
    if ($this->checkForSessionStatus === true
      && session_status() !== PHP_SESSION_ACTIVE) {
      throw new FacebookSDKException(
        'Session not active, could not store state.', 720
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
    if ($this->checkForSessionStatus === true
      && session_status() !== PHP_SESSION_ACTIVE) {
      throw new FacebookSDKException(
        'Session not active, could not load state.', 721
      );
    }
    if (isset($_SESSION[$this->sessionPrefix . 'state'])) {
      return $_SESSION[$this->sessionPrefix . 'state'];
    }
    return null;
  }

  protected function getFilteredUri($uri)
  {
    $parts = parse_url($uri);
    $scheme = isset($parts['scheme']) ? $parts['scheme'] : $this->getHttpScheme();

    $path = isset($parts['path']) ? $parts['path'] : '';

    $query = '';
    if (isset($parts['query'])) {
      $full_query = array();
      parse_str($parts['query'], $full_query);

      // remove Facebook appended query params
      $toDrop = array();
      if (isset($full_query['state']) && isset($full_query['code'])) {
        $toDrop = array('state', 'code');
      } elseif (isset($full_query['state'])
          && isset($full_query['error'])
          && isset($full_query['error_reason'])
          && isset($full_query['error_description'])
          && isset($full_query['error_code'])) {
        $toDrop = array('state', 'error', 'error_reason', 'error_description', 'error_code');
      }
      $real_query = array_diff_key($full_query, array_flip($toDrop));

      $query = '';
      if (!empty($real_query)) {
        $query = '?' . http_build_query($real_query, null, '&');
      }
    }

    // use port if non default
    $port = isset($parts['port']) ? ':' . $parts['port'] : '';

    // rebuild
    return $scheme . '://' . $parts['host'] . $port . $path . $query;
  }

  /**
   * Returns the current URI
   *
   * @return string
   */
  protected function getCurrentUri()
  {
    return $this->getHttpScheme() . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
  }

  /**
   * Returns the HTTP Protocol
   *
   * @return string The HTTP Protocol
   */
  protected function getHttpScheme() {
    $scheme = 'http';
    if (isset($_SERVER['HTTPS']) &&
        ($_SERVER['HTTPS'] === 'on' || $_SERVER['HTTPS'] == 1)
        || isset($_SERVER['SERVER_PORT']) &&
        ($_SERVER['SERVER_PORT'] === '443')) {
      $scheme = 'https';
    }
    return $scheme;
  }

  /**
   * Generate a cryptographically secure pseudrandom number
   * 
   * @param integer $bytes - number of bytes to return
   * 
   * @return string
   * 
   * @throws FacebookSDKException
   * 
   * @todo Support Windows platforms
   */
  private function random($bytes)
  {
    if (!is_numeric($bytes)) {
      throw new FacebookSDKException(
        "random() expects an integer"
      );
    }
    if ($bytes < 1) {
      throw new FacebookSDKException(
        "random() expects an integer greater than zero"
      );
    }
    $buf = '';
    // http://sockpuppet.org/blog/2014/02/25/safely-generate-random-numbers/
    if (is_readable('/dev/urandom')) {
      $fp = fopen('/dev/urandom', 'rb');
      if ($fp !== FALSE) {
        $buf = fread($fp, $bytes);
        fclose($fp);
        if($buf !== FALSE) {
          return bin2hex($buf);
        }
      }
    }
	
    if (function_exists('mcrypt_create_iv')) {
        $buf = mcrypt_create_iv($bytes, MCRYPT_DEV_URANDOM);
        if ($buf !== FALSE) {
          return bin2hex($buf);
        }
    }
    
    while (strlen($buf) < $bytes) {
      $buf .= md5(uniqid(mt_rand(), true), true); 
      // We are appending raw binary
    }
    return bin2hex(substr($buf, 0, $bytes));
  }

  /**
   * Disables the session_status() check when using $_SESSION
   */
  public function disableSessionStatusCheck()
  {
    $this->checkForSessionStatus = false;
  }

}
