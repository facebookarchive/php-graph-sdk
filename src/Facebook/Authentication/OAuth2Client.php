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

namespace Facebook\Authentication;

use Facebook\Facebook;
use Facebook\Application;
use Facebook\Request;
use Facebook\Response;
use Facebook\Client;
use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;
use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;

/**
 * Class OAuth2Client
 *
 * @package Facebook
 */
class OAuth2Client
{
    /**
     * @const string The base authorization URL.
     */
    const BASE_AUTHORIZATION_URL = 'https://www.facebook.com';

    /**
     * The last request sent to Graph.
     *
     * @var Request|null
     */
    protected ?Request $lastRequest;

    /**
     * @param \Facebook\Application $application
     * @param Client                $client
     * @param string                $graphVersion The version of the Graph API to use.
     */
    public function __construct(
        protected Application $application,
        protected Client      $client,
        protected string      $graphVersion,
    )
    {

    }

    /**
     * Returns the last FacebookRequest that was sent.
     * Useful for debugging and testing.
     *
     * @return Request|null
     */
    public function getLastRequest(): ?Request
    {
        return $this->lastRequest;
    }

    /**
     * Get the metadata associated with the access token.
     *
     * @param string|AccessToken $accessToken The access token to debug.
     *
     * @return AccessTokenMetadata
     */
    public function debugToken(string|AccessToken $accessToken): AccessTokenMetadata
    {
        $accessToken = $accessToken instanceof AccessToken ? $accessToken->getValue() : $accessToken;
        $params = ['input_token' => $accessToken];

        $this->lastRequest = new Request(
            $this->application,
            $this->application->getAccessToken(),
            'GET',
            '/debug_token',
            $params,
            null,
            $this->graphVersion
        );
        $response = $this->client->sendRequest($this->lastRequest);
        $metadata = $response->getDecodedBody();

        return new AccessTokenMetadata($metadata);
    }

    /**
     * Generates an authorization URL to begin the process of authenticating a user.
     *
     * @param string $redirectUrl The callback URL to redirect to.
     * @param string $state       The CSPRNG-generated CSRF value.
     * @param array  $scope       An array of permissions to request.
     * @param array  $params      An array of parameters to generate URL.
     * @param string $separator   The separator to use in http_build_query().
     *
     * @return string
     */
    #[Pure] public function getAuthorizationUrl(
        string $redirectUrl,
        string $state,
        array  $scope = [],
        array  $params = [],
        string $separator = '&',
    ): string
    {
        $params += [
            'client_id' => $this->application->getId(),
            'state' => $state,
            'response_type' => 'code',
            'sdk' => 'php-sdk-' . Facebook::VERSION,
            'redirect_uri' => $redirectUrl,
            'scope' => implode(',', $scope)
        ];

        return static::BASE_AUTHORIZATION_URL . '/' . $this->graphVersion . '/dialog/oauth?' . http_build_query($params, '', $separator);
    }

    /**
     * Get a valid access token from a code.
     *
     * @param string $code
     * @param string $redirectUri
     *
     * @return AccessToken
     *
     * @throws FacebookSDKException
     */
    public function getAccessTokenFromCode(string $code, string $redirectUri = ''): AccessToken
    {
        $params = [
            'code' => $code,
            'redirect_uri' => $redirectUri,
        ];

        return $this->requestAnAccessToken($params);
    }

    /**
     * Exchanges a short-lived access token with a long-lived access token.
     *
     * @param string|AccessToken $accessToken
     *
     * @return AccessToken
     *
     * @throws FacebookSDKException
     */
    public function getLongLivedAccessToken(string|AccessToken $accessToken): AccessToken
    {
        $accessToken = $accessToken instanceof AccessToken ? $accessToken->getValue() : $accessToken;
        $params = [
            'grant_type' => 'fb_exchange_token',
            'fb_exchange_token' => $accessToken,
        ];

        return $this->requestAnAccessToken($params);
    }

    /**
     * Get a valid code from an access token.
     *
     * @param string|AccessToken $accessToken
     * @param string             $redirectUri
     *
     * @return string
     *
     * @throws FacebookSDKException
     */
    public function getCodeFromLongLivedAccessToken(string|AccessToken $accessToken, string $redirectUri = ''): string
    {
        $params = [
            'redirect_uri' => $redirectUri,
        ];

        $response = $this->sendRequestWithClientParams('/oauth/client_code', $params, $accessToken);
        $data = $response->getDecodedBody();

        if (!isset($data['code'])) {
            throw new FacebookSDKException('Code was not returned from Graph.', 401);
        }

        return $data['code'];
    }

    /**
     * Send a request to the OAuth endpoint.
     *
     * @param array $params
     *
     * @return AccessToken
     *
     * @throws FacebookSDKException
     */
    protected function requestAnAccessToken(array $params): AccessToken
    {
        $response = $this->sendRequestWithClientParams('/oauth/access_token', $params);
        $data = $response->getDecodedBody();

        if (!isset($data['access_token'])) {
            throw new FacebookSDKException('Access token was not returned from Graph.', 401);
        }

        // Graph returns two different key names for expiration time
        // on the same endpoint. Doh! :/
        $expiresAt = 0;
        if (isset($data['expires'])) {
            // For exchanging a short-lived token with a long-lived token.
            // The expiration time in seconds will be returned as "expires".
            $expiresAt = time() + $data['expires'];
        } elseif (isset($data['expires_in'])) {
            // For exchanging a code for a short-lived access token.
            // The expiration time in seconds will be returned as "expires_in".
            // See: https://developers.facebook.com/docs/facebook-login/access-tokens#long-via-code
            $expiresAt = time() + $data['expires_in'];
        }

        return new AccessToken($data['access_token'], $expiresAt);
    }

    /**
     * Send a request to Graph with an app access token.
     *
     * @param string                  $endpoint
     * @param array                   $params
     * @param string|AccessToken|null $accessToken
     *
     * @return Response
     *
     * @throws FacebookResponseException
     */
    protected function sendRequestWithClientParams(
        string             $endpoint,
        array              $params,
        string|AccessToken $accessToken = null,
    ): Response
    {
        $params += $this->getClientParams();

        $accessToken = $accessToken ?: $this->application->getAccessToken();

        $this->lastRequest = new Request(
            $this->application,
            $accessToken,
            'GET',
            $endpoint,
            $params,
            null,
            $this->graphVersion
        );

        return $this->client->sendRequest($this->lastRequest);
    }

    /**
     * Returns the client_* params for OAuth requests.
     *
     * @return array
     */
    #[ArrayShape(['client_id' => "string", 'client_secret' => "string"])] #[Pure] protected function getClientParams(): array
    {
        return [
            'client_id' => $this->application->getId(),
            'client_secret' => $this->application->getSecret(),
        ];
    }
}
