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

use Facebook\Facebook;
use \Facebook\AccessToken;
use \Facebook\FacebookApp;
use Facebook\Url\UrlDetectionInterface;
use Facebook\Url\FacebookUrlDetectionHandler;
use Facebook\Url\FacebookUrlManipulator;
use Facebook\PersistentData\PersistentDataInterface;
use Facebook\PersistentData\FacebookSessionPersistentDataHandler;
use Facebook\PseudoRandomString\PseudoRandomStringGeneratorInterface;
use Facebook\PseudoRandomString\McryptPseudoRandomStringGenerator;
use Facebook\PseudoRandomString\OpenSslPseudoRandomStringGenerator;
use Facebook\PseudoRandomString\UrandomPseudoRandomStringGenerator;
use Facebook\Exceptions\FacebookSDKException;
use Facebook\FacebookClient;

/**
 * Class FacebookRedirectLoginHelper
 * @package Facebook
 * @author Fosco Marotto <fjm@fb.com>
 * @author David Poll <depoll@fb.com>
 */
class FacebookRedirectLoginHelper
{

  /**
   * @const int The length of CSRF string to validate the login link.
   */
  const CSRF_LENGTH = 32;

  /**
   * @var FacebookApp The FacebookApp entity.
   */
  protected $app;

  /**
   * @var UrlDetectionInterface The URL detection handler.
   */
  protected $urlDetectionHandler;

  /**
   * @var PersistentDataInterface The persistent data handler.
   */
  protected $persistentDataHandler;

  /**
   * @var PseudoRandomStringGeneratorInterface The cryptographically secure
   *                                           pseudo-random string generator.
   */
  protected $pseudoRandomStringGenerator;

  /**
   * Constructs a RedirectLoginHelper for a given appId.
   *
   * @param FacebookApp $app The FacebookApp entity.
   * @param PersistentDataInterface|null $persistentDataHandler The persistent data handler.
   * @param UrlDetectionInterface|null $urlHandler The URL detection handler.
   * @param PseudoRandomStringGeneratorInterface|null $prsg The cryptographically secure
   *                                                        pseudo-random string generator.
   */
  public function __construct(FacebookApp $app,
                              PersistentDataInterface $persistentDataHandler = null,
                              UrlDetectionInterface $urlHandler = null,
                              PseudoRandomStringGeneratorInterface $prsg = null)
  {
    $this->app = $app;
    $this->persistentDataHandler = $persistentDataHandler ?: new FacebookSessionPersistentDataHandler();
    $this->urlDetectionHandler = $urlHandler ?: new FacebookUrlDetectionHandler();
    $this->pseudoRandomStringGenerator = $prsg ?: $this->detectPseudoRandomStringGenerator();
  }

  /**
   * Returns the persistent data handler.
   *
   * @return PersistentDataInterface
   */
  public function getPersistentDataHandler()
  {
    return $this->persistentDataHandler;
  }

  /**
   * Returns the URL detection handler.
   *
   * @return UrlDetectionInterface
   */
  public function getUrlDetectionHandler()
  {
    return $this->urlDetectionHandler;
  }

  /**
   * Returns the cryptographically secure pseudo-random string generator.
   *
   * @return PseudoRandomStringGeneratorInterface
   */
  public function getPseudoRandomStringGenerator()
  {
    return $this->pseudoRandomStringGenerator;
  }

  /**
   * Detects which pseudo-random string generator to use.
   *
   * @return PseudoRandomStringGeneratorInterface
   *
   * @throws FacebookSDKException
   */
  public function detectPseudoRandomStringGenerator()
  {
    // Since openssl_random_pseudo_bytes() can sometimes return non-cryptographically
    // secure pseudo-random strings (in rare cases), we check for mcrypt_create_iv() first.
    if(function_exists('mcrypt_create_iv')) {
      return new McryptPseudoRandomStringGenerator();
    }
    if(function_exists('openssl_random_pseudo_bytes')) {
      return new OpenSslPseudoRandomStringGenerator();
    }
    if( ! ini_get('open_basedir') && is_readable('/dev/urandom')) {
      return new UrandomPseudoRandomStringGenerator();
    }

    throw new FacebookSDKException(
      'Unable to detect a cryptographically secure pseudo-random string generator.'
    );
  }

  /**
   * Stores CSRF state and returns a URL to which the user should be sent to
   *   in order to continue the login process with Facebook.  The
   *   provided redirectUrl should invoke the handleRedirect method.
   *
   * @param string $redirectUrl The URL Facebook should redirect users to
   *                            after login.
   * @param array $scope List of permissions to request during login.
   * @param string $version Optional Graph API version if not default (v2.0).
   * @param string $separator The separator to use in http_build_query().
   * @param array $params Array of parameters to generate URL.
   *
   * @return string
   */
  private function makeUrl($redirectUrl, array $scope, $version, $separator,  array $params = [])
  {
    $version = $version ?: Facebook::DEFAULT_GRAPH_VERSION;

    $state = $this->pseudoRandomStringGenerator->getPseudoRandomString(static::CSRF_LENGTH);
    $this->persistentDataHandler->set('state', $state);

    $params += [
      'client_id' => $this->app->getId(),
      'state' => $state,
      'response_type' => 'code',
      'sdk' => 'php-sdk-' . Facebook::VERSION,
      'redirect_uri' => $redirectUrl,
      'scope' => implode(',', $scope)
    ];

    return 'https://www.facebook.com/' . $version . '/dialog/oauth?' .
      http_build_query($params, null, $separator);
  }

  /**
   * Returns the URL to send the user in order to login to Facebook.
   *
   * @param string $redirectUrl The URL Facebook should redirect users to
   *                            after login.
   * @param array $scope List of permissions to request during login.
   * @param string $version Optional Graph API version if not default (v2.0).
   * @param string $separator The separator to use in http_build_query().
   *
   * @return string
   */
  public function getLoginUrl($redirectUrl,
                              array $scope = [],
                              $version = null,
                              $separator = '&')
  {
    return $this->makeUrl($redirectUrl, $scope, $version, $separator);
  }

  /**
   * Returns the URL to send the user in order to log out of Facebook.
   *
   * @param AccessToken|string $accessToken The access token that will be logged out.
   * @param string $next The url Facebook should redirect the user to after
   *                          a successful logout.
   * @param string $separator The separator to use in http_build_query().
   *
   * @return string
   */
  public function getLogoutUrl($accessToken, $next, $separator = '&')
  {
    $params = [
      'next' => $next,
      'access_token' => (string) $accessToken,
    ];
    return 'https://www.facebook.com/logout.php?' . http_build_query($params, null, $separator);
  }

  /**
   * Returns the URL to send the user in order to login to Facebook with
   * permission(s) to be re-asked.
   *
   * @param string $redirectUrl The URL Facebook should redirect users to
   *                            after login.
   * @param array $scope List of permissions to request during login.
   * @param string $version Optional Graph API version if not default (v2.0).
   * @param string $separator The separator to use in http_build_query().
   *
   * @return string
   */
  public function getReRequestUrl($redirectUrl,
                                  array $scope = [],
                                  $version = null,
                                  $separator = '&')
  {
    $params = [
      'auth_type' => 'rerequest'
    ];
    return $this->makeUrl($redirectUrl, $scope, $version, $separator, $params);
  }

  /**
   * Returns the URL to send the user in order to login to Facebook with
   * user to be re-authenticated.
   *
   * @param string $redirectUrl The URL Facebook should redirect users to
   *                            after login.
   * @param array $scope List of permissions to request during login.
   * @param string $version Optional Graph API version if not default (v2.0).
   * @param string $separator The separator to use in http_build_query().
   *
   * @return string
   */
  public function getReAuthenticationUrl($redirectUrl,
                                         array $scope = [],
                                         $version = null,
                                         $separator = '&')
  {
    $params = [
      'auth_type' => 'reauthenticate'
    ];
    return $this->makeUrl($redirectUrl, $scope, $version, $separator, $params);
  }

  /**
   * Takes a valid code from a login redirect, and returns an AccessToken entity.
   *
   * @param FacebookClient $client The Facebook client.
   * @param string|null $redirectUrl The redirect URL.
   *
   * @return AccessToken|null
   *
   * @throws FacebookSDKException
   */
  public function getAccessToken(FacebookClient $client, $redirectUrl = null)
  {
    if ( ! $code = $this->getCode()) {
      return null;
    }

    $this->validateCsrf();

    $redirectUrl = $redirectUrl ?: $this->urlDetectionHandler->getCurrentUrl();
    // At minimum we need to remove the state param
    $redirectUrl = FacebookUrlManipulator::removeParamsFromUrl($redirectUrl, ['state']);

    return AccessToken::getAccessTokenFromCode($code, $this->app, $client, $redirectUrl);
  }

  /**
   * Validate the request against a cross-site request forgery.
   *
   * @throws FacebookSDKException
   */
  protected function validateCsrf()
  {
    $state = $this->getState();
    $savedState = $this->persistentDataHandler->get('state');

    if ( ! $state || ! $savedState) {
      throw new FacebookSDKException(
        'Cross-site request forgery validation failed. ' .
        'Required param "state" missing.'
      );
    }
    if ($state !== $savedState) {
      throw new FacebookSDKException(
        'Cross-site request forgery validation failed. ' .
        'The "state" param from the URL and session do not match.'
      );
    }
  }

  /**
   * Return the code.
   *
   * @return string|null
   */
  protected function getCode()
  {
    return $this->getInput('code');
  }

  /**
   * Return the state.
   *
   * @return string|null
   */
  protected function getState()
  {
    return $this->getInput('state');
  }

  /**
   * Return the error code.
   *
   * @return string|null
   */
  public function getErrorCode()
  {
    return $this->getInput('error_code');
  }

  /**
   * Returns the error.
   *
   * @return string|null
   */
  public function getError()
  {
    return $this->getInput('error');
  }

  /**
   * Returns the error reason.
   *
   * @return string|null
   */
  public function getErrorReason()
  {
    return $this->getInput('error_reason');
  }

  /**
   * Returns the error description.
   *
   * @return string|null
   */
  public function getErrorDescription()
  {
    return $this->getInput('error_description');
  }

  /**
   * Returns a value from a GET param.
   *
   * @param string $key
   *
   * @return string|null
   */
  private function getInput($key)
  {
    return isset($_GET[$key]) ? $_GET[$key] : null;
  }

}
