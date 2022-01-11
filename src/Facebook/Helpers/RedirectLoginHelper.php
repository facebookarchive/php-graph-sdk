<?php

declare(strict_types=1);
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
 *
 */

namespace Facebook\Helpers;

use Facebook\Authentication\AccessToken;
use Facebook\Authentication\OAuth2Client;
use Facebook\Exceptions\FacebookSDKException;
use Facebook\PersistentData\FacebookSessionPersistentDataHandler;
use Facebook\PersistentData\PersistentDataInterface;
use Facebook\Url\UrlDetectionHandler;
use Facebook\Url\FacebookUrlManipulator;
use Facebook\Url\UrlDetectionInterface;
use JetBrains\PhpStorm\Pure;

/**
 * Class FacebookRedirectLoginHelper
 *
 * @package Facebook
 */
class RedirectLoginHelper
{
    /**
     * @const int The length of CSRF string to validate the login link.
     */
    const CSRF_LENGTH = 32;

    /**
     * @var UrlDetectionInterface The URL detection handler.
     */
    protected UrlDetectionInterface $urlDetectionHandler;

    /**
     * @var PersistentDataInterface The persistent data handler.
     */
    protected PersistentDataInterface $persistentDataHandler;

    /**
     * @param OAuth2Client                                          $oAuth2Client The OAuth 2.0 client service.
     * @param \Facebook\PersistentData\PersistentDataInterface|null $persistentData
     * @param \Facebook\Url\UrlDetectionInterface|null              $urlDetection
     */
    public function __construct(
        protected OAuth2Client  $oAuth2Client,
        PersistentDataInterface $persistentData = null,
        UrlDetectionInterface   $urlDetection = null,
    )
    {
        $this->persistentDataHandler = $persistentData ?? new FacebookSessionPersistentDataHandler;
        $this->urlDetectionHandler = $urlDetection ?? new UrlDetectionHandler;
    }

    /**
     * Returns the persistent data handler.
     */
    public function getPersistentDataHandler(): PersistentDataInterface
    {
        return $this->persistentDataHandler;
    }


    /**
     * Returns the URL detection handler.
     *
     * @return UrlDetectionInterface
     */
    public function getUrlDetectionHandler(): UrlDetectionInterface
    {
        return $this->urlDetectionHandler;
    }

    /**
     * Stores CSRF state and returns a URL to which the user should be sent to in order to continue the login process
     * with Facebook.
     *
     * @param string $redirectUrl The URL Facebook should redirect users to after login.
     * @param array  $scope       List of permissions to request during login.
     * @param array  $params      An array of parameters to generate URL.
     * @param string $separator   The separator to use in http_build_query().
     *
     * @return string
     */
    private function makeUrl(string $redirectUrl, array $scope, array $params = [], string $separator = '&'): string
    {
        $state = $this->persistentDataHandler->get('state') ?? $this->getPseudoRandomString();
        $this->persistentDataHandler->set('state', $state);

        return $this->oAuth2Client->getAuthorizationUrl($redirectUrl, $state, $scope, $params, $separator);
    }

    private function getPseudoRandomString(): string
    {
        return bin2hex(random_bytes(static::CSRF_LENGTH));
    }

    /**
     * Returns the URL to send the user in order to login to Facebook.
     *
     * @param string $redirectUrl The URL Facebook should redirect users to after login.
     * @param array  $scope       List of permissions to request during login.
     * @param string $separator   The separator to use in http_build_query().
     *
     * @return string
     */
    public function getLoginUrl(string $redirectUrl, array $scope = [], string $separator = '&'): string
    {
        return $this->makeUrl($redirectUrl, $scope, [], $separator);
    }

    /**
     * Returns the URL to send the user in order to log out of Facebook.
     *
     * @param string|AccessToken $accessToken The access token that will be logged out.
     * @param string             $next        The url Facebook should redirect the user to after a successful logout.
     * @param string             $separator   The separator to use in http_build_query().
     *
     * @return string
     *
     * @throws FacebookSDKException
     */
    public function getLogoutUrl(string|AccessToken $accessToken, string $next, string $separator = '&'): string
    {
        if (!$accessToken instanceof AccessToken) {
            $accessToken = new AccessToken($accessToken);
        }

        if ($accessToken->isAppAccessToken()) {
            throw new FacebookSDKException('Cannot generate a logout URL with an app access token.', 722);
        }

        $params = [
            'next' => $next,
            'access_token' => $accessToken->getValue(),
        ];

        return 'https://www.facebook.com/logout.php?' . http_build_query($params, '', $separator);
    }

    /**
     * Returns the URL to send the user in order to login to Facebook with permission(s) to be re-asked.
     *
     * @param string $redirectUrl The URL Facebook should redirect users to after login.
     * @param array  $scope       List of permissions to request during login.
     * @param string $separator   The separator to use in http_build_query().
     *
     * @return string
     */
    public function getReRequestUrl(string $redirectUrl, array $scope = [], string $separator = '&'): string
    {
        $params = ['auth_type' => 'rerequest'];

        return $this->makeUrl($redirectUrl, $scope, $params, $separator);
    }

    /**
     * Returns the URL to send the user in order to login to Facebook with user to be re-authenticated.
     *
     * @param string $redirectUrl The URL Facebook should redirect users to after login.
     * @param array  $scope       List of permissions to request during login.
     * @param string $separator   The separator to use in http_build_query().
     *
     * @return string
     */
    public function getReAuthenticationUrl(string $redirectUrl, array $scope = [], string $separator = '&'): string
    {
        $params = ['auth_type' => 'reauthenticate'];

        return $this->makeUrl($redirectUrl, $scope, $params, $separator);
    }

    /**
     * Takes a valid code from a login redirect, and returns an AccessToken entity.
     *
     * @param string|null $redirectUrl The redirect URL.
     *
     * @return AccessToken|null
     *
     * @throws FacebookSDKException
     */
    public function getAccessToken(string $redirectUrl = null): ?AccessToken
    {
        $code = $this->getCode();
        if ($code === null) {
            return null;
        }

        $this->validateCsrf();
        $this->resetCsrf();

        $redirectUrl = $redirectUrl ?: $this->urlDetectionHandler->getCurrentUrl();
        // At minimum we need to remove the 'code', 'enforce_https' and 'state' params
        $redirectUrl = FacebookUrlManipulator::removeParamsFromUrl($redirectUrl, ['code', 'enforce_https', 'state']);

        return $this->oAuth2Client->getAccessTokenFromCode($code, $redirectUrl);
    }

    /**
     * Validate the request against a cross-site request forgery.
     *
     * @throws FacebookSDKException
     */
    protected function validateCsrf(): void
    {
        $state = $this->getState();
        if ($state === null) {
            throw new FacebookSDKException('Cross-site request forgery validation failed. Required GET param "state" missing.');
        }
        $savedState = $this->persistentDataHandler->get('state');
        if ($savedState === null) {
            throw new FacebookSDKException('Cross-site request forgery validation failed. Required param "state" missing from persistent data.');
        }

        if (\hash_equals($savedState, $state)) {
            return;
        }

        throw new FacebookSDKException('Cross-site request forgery validation failed. The "state" param from the URL and session do not match.');
    }

    /**
     * Resets the CSRF so that it doesn't get reused.
     */
    private function resetCsrf(): void
    {
        $this->persistentDataHandler->set('state', null);
    }

    /**
     * Return the code.
     *
     * @return string|null
     */
    #[Pure] protected function getCode(): ?string
    {
        return $this->getInput('code');
    }

    /**
     * Return the state.
     *
     * @return string|null
     */
    #[Pure] protected function getState(): ?string
    {
        return $this->getInput('state');
    }

    /**
     * Return the error code.
     *
     * @return string|null
     */
    #[Pure] public function getErrorCode(): ?string
    {
        return $this->getInput('error_code');
    }

    /**
     * Returns the error.
     *
     * @return string|null
     */
    #[Pure] public function getError(): ?string
    {
        return $this->getInput('error');
    }

    /**
     * Returns the error reason.
     *
     * @return string|null
     */
    #[Pure] public function getErrorReason(): ?string
    {
        return $this->getInput('error_reason');
    }

    /**
     * Returns the error description.
     *
     * @return string|null
     */
    #[Pure] public function getErrorDescription(): ?string
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
    private function getInput(string $key): ?string
    {
        return $_GET[$key] ?? null;
    }
}
