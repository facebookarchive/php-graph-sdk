<?php
/**
 * Copyright 2017 Facebook, Inc.
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
 */
namespace Facebook\Helper;

use Facebook\Authentication\AccessToken;
use Facebook\Authentication\OAuth2Client;
use Facebook\Exception\SDKException;
use Facebook\PersistentData\SessionPersistentDataHandler;
use Facebook\PersistentData\PersistentDataInterface;
use Facebook\Url\UrlDetectionHandler;
use Facebook\Url\UrlManipulator;
use Facebook\Url\UrlDetectionInterface;

/**
 * @package Facebook
 */
class RedirectLoginHelper
{
    /**
     * @const int The length of CSRF string to validate the login link.
     */
    const CSRF_LENGTH = 32;

    /**
     * @var OAuth2Client The OAuth 2.0 client service.
     */
    protected $oAuth2Client;

    /**
     * @var UrlDetectionInterface the URL detection handler
     */
    protected $urlDetectionHandler;

    /**
     * @var PersistentDataInterface the persistent data handler
     */
    protected $persistentDataHandler;

    /**
     * @param OAuth2Client                 $oAuth2Client          The OAuth 2.0 client service.
     * @param null|PersistentDataInterface $persistentDataHandler the persistent data handler
     * @param null|UrlDetectionInterface   $urlHandler            the URL detection handler
     */
    public function __construct(OAuth2Client $oAuth2Client, PersistentDataInterface $persistentDataHandler = null, UrlDetectionInterface $urlHandler = null)
    {
        $this->oAuth2Client = $oAuth2Client;
        $this->persistentDataHandler = $persistentDataHandler ?: new SessionPersistentDataHandler();
        $this->urlDetectionHandler = $urlHandler ?: new UrlDetectionHandler();
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
     * Stores CSRF state and returns a URL to which the user should be sent to in order to continue the login process with Facebook.
     *
     * @param string $redirectUrl the URL Facebook should redirect users to after login
     * @param array  $scope       list of permissions to request during login
     * @param array  $params      an array of parameters to generate URL
     * @param string $separator   the separator to use in http_build_query()
     *
     * @return string
     */
    private function makeUrl($redirectUrl, array $scope, array $params = [], $separator = '&')
    {
        $state = $this->persistentDataHandler->get('state') ?: $this->getPseudoRandomString();
        $this->persistentDataHandler->set('state', $state);

        return $this->oAuth2Client->getAuthorizationUrl($redirectUrl, $state, $scope, $params, $separator);
    }

    private function getPseudoRandomString()
    {
        return bin2hex(random_bytes(static::CSRF_LENGTH));
    }

    /**
     * Returns the URL to send the user in order to login to Facebook.
     *
     * @param string $redirectUrl the URL Facebook should redirect users to after login
     * @param array  $scope       list of permissions to request during login
     * @param string $separator   the separator to use in http_build_query()
     *
     * @return string
     */
    public function getLoginUrl($redirectUrl, array $scope = [], $separator = '&')
    {
        return $this->makeUrl($redirectUrl, $scope, [], $separator);
    }

    /**
     * Returns the URL to send the user in order to log out of Facebook.
     *
     * @param AccessToken|string $accessToken the access token that will be logged out
     * @param string             $next        the url Facebook should redirect the user to after a successful logout
     * @param string             $separator   the separator to use in http_build_query()
     *
     * @throws SDKException
     *
     * @return string
     */
    public function getLogoutUrl($accessToken, $next, $separator = '&')
    {
        if (!$accessToken instanceof AccessToken) {
            $accessToken = new AccessToken($accessToken);
        }

        if ($accessToken->isAppAccessToken()) {
            throw new SDKException('Cannot generate a logout URL with an app access token.', 722);
        }

        $params = [
            'next' => $next,
            'access_token' => $accessToken->getValue(),
        ];

        return 'https://www.facebook.com/logout.php?' . http_build_query($params, null, $separator);
    }

    /**
     * Returns the URL to send the user in order to login to Facebook with permission(s) to be re-asked.
     *
     * @param string $redirectUrl the URL Facebook should redirect users to after login
     * @param array  $scope       list of permissions to request during login
     * @param string $separator   the separator to use in http_build_query()
     *
     * @return string
     */
    public function getReRequestUrl($redirectUrl, array $scope = [], $separator = '&')
    {
        $params = ['auth_type' => 'rerequest'];

        return $this->makeUrl($redirectUrl, $scope, $params, $separator);
    }

    /**
     * Returns the URL to send the user in order to login to Facebook with user to be re-authenticated.
     *
     * @param string $redirectUrl the URL Facebook should redirect users to after login
     * @param array  $scope       list of permissions to request during login
     * @param string $separator   the separator to use in http_build_query()
     *
     * @return string
     */
    public function getReAuthenticationUrl($redirectUrl, array $scope = [], $separator = '&')
    {
        $params = ['auth_type' => 'reauthenticate'];

        return $this->makeUrl($redirectUrl, $scope, $params, $separator);
    }

    /**
     * Takes a valid code from a login redirect, and returns an AccessToken entity.
     *
     * @param null|string $redirectUrl the redirect URL
     *
     * @throws SDKException
     *
     * @return null|AccessToken
     */
    public function getAccessToken($redirectUrl = null)
    {
        if (!$code = $this->getCode()) {
            return null;
        }

        $this->validateCsrf();
        $this->resetCsrf();

        $redirectUrl = $redirectUrl ?: $this->urlDetectionHandler->getCurrentUrl();
        // At minimum we need to remove the state param
        $redirectUrl = UrlManipulator::removeParamsFromUrl($redirectUrl, ['state']);

        return $this->oAuth2Client->getAccessTokenFromCode($code, $redirectUrl);
    }

    /**
     * Validate the request against a cross-site request forgery.
     *
     * @throws SDKException
     */
    protected function validateCsrf()
    {
        $state = $this->getState();
        if (!$state) {
            throw new SDKException('Cross-site request forgery validation failed. Required GET param "state" missing.');
        }
        $savedState = $this->persistentDataHandler->get('state');
        if (!$savedState) {
            throw new SDKException('Cross-site request forgery validation failed. Required param "state" missing from persistent data.');
        }

        if (\hash_equals($savedState, $state)) {
            return;
        }

        throw new SDKException('Cross-site request forgery validation failed. The "state" param from the URL and session do not match.');
    }

    /**
     * Resets the CSRF so that it doesn't get reused.
     */
    private function resetCsrf()
    {
        $this->persistentDataHandler->set('state', null);
    }

    /**
     * Return the code.
     *
     * @return null|string
     */
    protected function getCode()
    {
        return $this->getInput('code');
    }

    /**
     * Return the state.
     *
     * @return null|string
     */
    protected function getState()
    {
        return $this->getInput('state');
    }

    /**
     * Return the error code.
     *
     * @return null|string
     */
    public function getErrorCode()
    {
        return $this->getInput('error_code');
    }

    /**
     * Returns the error.
     *
     * @return null|string
     */
    public function getError()
    {
        return $this->getInput('error');
    }

    /**
     * Returns the error reason.
     *
     * @return null|string
     */
    public function getErrorReason()
    {
        return $this->getInput('error_reason');
    }

    /**
     * Returns the error description.
     *
     * @return null|string
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
     * @return null|string
     */
    private function getInput($key)
    {
        return $_GET[$key] ?? null;
    }
}
