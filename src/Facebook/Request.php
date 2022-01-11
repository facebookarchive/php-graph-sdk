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

namespace Facebook;

use Facebook\Authentication\AccessToken;
use Facebook\Url\FacebookUrlManipulator;
use Facebook\FileUpload\File;
use Facebook\FileUpload\Video;
use Facebook\Http\RequestBodyMultipart;
use Facebook\Http\RequestBodyUrlEncoded;
use Facebook\Exceptions\FacebookSDKException;
use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;

/**
 * Class Request
 *
 * @package Facebook
 */
class Request
{
    /**
     * @var ?Application The Facebook app entity.
     */
    protected ?Application $app;

    /**
     * @var string|null The access token to use for this request.
     */
    protected ?string $accessToken;

    /**
     * @var ?string The HTTP method for this request.
     */
    protected ?string $method = null;

    /**
     * @var string The Graph endpoint for this request.
     */
    protected string $endpoint;

    /**
     * @var array The headers to send with this request.
     */
    protected array $headers = [];

    /**
     * @var array The parameters to send with this request.
     */
    protected array $params = [];

    /**
     * @var array The files to send with this request.
     */
    protected array $files = [];

    /**
     * @var ?string ETag to send with this request.
     */
    protected ?string $eTag;

    /**
     * Creates a new Request entity.
     *
     * @param \Facebook\Application|null                       $application
     * @param \Facebook\Authentication\AccessToken|string|null $accessToken
     * @param string|null                                      $method
     * @param string|null                                      $endpoint
     * @param array                                            $params
     * @param string|null                                      $eTag
     * @param string|null                                      $graphVersion
     *
     * @throws \Facebook\Exceptions\FacebookSDKException
     */
    public function __construct(
        Application        $application = null,
        AccessToken|string $accessToken = null,
        string             $method = null,
        string             $endpoint = null,
        array              $params = [],
        string             $eTag = null,
        protected ?string  $graphVersion = null,
    )
    {
        $this->setApp($application);
        $this->setAccessToken($accessToken);
        $this->setMethod($method);
        $this->setEndpoint($endpoint);
        $this->setParams($params);
        $this->setETag($eTag);
    }

    /**
     * Set the access token for this request.
     *
     * @param AccessToken|string|null
     *
     * @return Request
     */
    public function setAccessToken($accessToken): static
    {
        $this->accessToken = $accessToken instanceof AccessToken
            ? $accessToken->getValue()
            : $accessToken;

        return $this;
    }

    /**
     * Sets the access token with one harvested from a URL or POST params.
     *
     * @param string $accessToken The access token.
     *
     * @return Request
     *
     * @throws FacebookSDKException
     */
    public function setAccessTokenFromParams(string $accessToken): static
    {
        $existingAccessToken = $this->getAccessToken();
        if (!$existingAccessToken) {
            $this->setAccessToken($accessToken);
        } elseif ($accessToken !== $existingAccessToken) {
            throw new FacebookSDKException('Access token mismatch. The access token provided in the FacebookRequest and the one provided in the URL or POST params do not match.');
        }

        return $this;
    }

    /**
     * Return the access token for this request.
     *
     * @return string|null
     */
    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }

    /**
     * Return the access token for this request as an AccessToken entity.
     *
     * @return AccessToken|null
     */
    public function getAccessTokenEntity(): ?AccessToken
    {
        return $this->accessToken !== null ? new AccessToken($this->accessToken) : null;
    }

    /**
     * Set the FacebookApp entity used for this request.
     *
     * @param Application|null $application
     */
    public function setApp(Application $application = null)
    {
        $this->app = $application;
    }

    /**
     * Return the FacebookApp entity used for this request.
     *
     * @return \Facebook\Application|null
     */
    public function getApplication(): ?Application
    {
        return $this->app;
    }

    /**
     * Generate an app secret proof to sign this request.
     *
     * @return string|null
     */
    public function getAppSecretProof(): ?string
    {
        $accessTokenEntity = $this->getAccessTokenEntity();
        if (!$accessTokenEntity instanceof AccessToken) {
            return null;
        }

        return $accessTokenEntity->getAppSecretProof((string)$this->app?->getSecret());
    }

    /**
     * Validate that an access token exists for this request.
     *
     * @throws FacebookSDKException
     */
    public function validateAccessToken()
    {
        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            throw new FacebookSDKException('You must provide an access token.');
        }
    }

    /**
     * Set the HTTP method for this request.
     */
    public function setMethod(?string $method): void
    {
        if ($method !== null) {
            $this->method = strtoupper($method);
        }
    }

    /**
     * Return the HTTP method for this request.
     *
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Validate that the HTTP method is set.
     *
     * @throws FacebookSDKException
     */
    public function validateMethod()
    {
        if (!$this->method) {
            throw new FacebookSDKException('HTTP method not specified.');
        }

        if (!in_array($this->method, ['GET', 'POST', 'DELETE'])) {
            throw new FacebookSDKException('Invalid HTTP method specified.');
        }
    }

    /**
     * Set the endpoint for this request.
     *
     * @param string
     *
     * @return Request
     *
     * @throws FacebookSDKException
     */
    public function setEndpoint($endpoint): static
    {
        if ($endpoint === null) {
            return $this;
        }

        // Harvest the access token from the endpoint to keep things in sync
        $params = FacebookUrlManipulator::getParamsAsArray($endpoint);
        if (isset($params['access_token'])) {
            $this->setAccessTokenFromParams($params['access_token']);
        }

        // Clean the token & app secret proof from the endpoint.
        $filterParams = ['access_token', 'appsecret_proof'];
        $this->endpoint = FacebookUrlManipulator::removeParamsFromUrl($endpoint, $filterParams);

        return $this;
    }

    /**
     * Return the endpoint for this request.
     *
     * @return ?string
     */
    public function getEndpoint(): ?string
    {
        // For batch requests, this will be empty
        return $this->endpoint;
    }

    /**
     * Generate and return the headers for this request.
     *
     * @return array
     */
    #[Pure] public function getHeaders(): array
    {
        $headers = static::getDefaultHeaders();

        if ($this->eTag !== null && $this->eTag !== '' && $this->eTag !== '0') {
            $headers['If-None-Match'] = $this->eTag;
        }

        return array_merge($this->headers, $headers);
    }

    /**
     * Set the headers for this request.
     *
     * @param array $headers
     */
    public function setHeaders(array $headers)
    {
        $this->headers = array_merge($this->headers, $headers);
    }

    /**
     * Sets the eTag value.
     *
     * @param ?string $eTag
     */
    public function setETag(?string $eTag)
    {
        $this->eTag = $eTag;
    }

    /**
     * Set the params for this request.
     *
     * @param array $params
     *
     * @return Request
     *
     * @throws FacebookSDKException
     */
    public function setParams(array $params = []): static
    {
        if (isset($params['access_token'])) {
            $this->setAccessTokenFromParams($params['access_token']);
        }

        // Don't let these buggers slip in.
        unset($params['access_token'], $params['appsecret_proof']);

        $params = $this->sanitizeFileParams($params);
        $this->dangerouslySetParams($params);

        return $this;
    }

    /**
     * Set the params for this request without filtering them first.
     *
     * @param array $params
     *
     * @return Request
     */
    public function dangerouslySetParams(array $params = []): static
    {
        $this->params = array_merge($this->params, $params);

        return $this;
    }

    /**
     * Iterate over the params and pull out the file uploads.
     *
     * @param array $params
     *
     * @return array
     */
    public function sanitizeFileParams(array $params): array
    {
        foreach ($params as $key => $value) {
            if ($value instanceof File) {
                $this->addFile($key, $value);
                unset($params[$key]);
            }
        }

        return $params;
    }

    /**
     * Add a file to be uploaded.
     *
     * @param string $key
     * @param File   $file
     */
    public function addFile(string $key, File $file)
    {
        $this->files[$key] = $file;
    }

    /**
     * Removes all the files from the upload queue.
     */
    public function resetFiles()
    {
        $this->files = [];
    }

    /**
     * Get the list of files to be uploaded.
     *
     * @return array
     */
    public function getFiles(): array
    {
        return $this->files;
    }

    /**
     * Lets us know if there is a file upload with this request.
     *
     * @return boolean
     */
    public function containsFileUploads(): bool
    {
        return !empty($this->files);
    }

    /**
     * Lets us know if there is a video upload with this request.
     *
     * @return boolean
     */
    public function containsVideoUploads(): bool
    {
        foreach ($this->files as $file) {
            if ($file instanceof Video) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns the body of the request as multipart/form-data.
     *
     * @return RequestBodyMultipart
     */
    public function getMultipartBody(): RequestBodyMultipart
    {
        $params = $this->getPostParams();

        return new RequestBodyMultipart($params, $this->files);
    }

    /**
     * Returns the body of the request as URL-encoded.
     *
     * @return RequestBodyUrlEncoded
     */
    public function getUrlEncodedBody(): RequestBodyUrlEncoded
    {
        $params = $this->getPostParams();

        return new RequestBodyUrlEncoded($params);
    }

    /**
     * Generate and return the params for this request.
     *
     * @return array
     */
    public function getParams(): array
    {
        $params = $this->params;

        $accessToken = $this->getAccessToken();
        if ($accessToken !== null) {
            $params['access_token'] = $accessToken;
            $params['appsecret_proof'] = $this->getAppSecretProof();
        }

        return $params;
    }

    /**
     * Only return params on POST requests.
     *
     * @return array
     */
    public function getPostParams(): array
    {
        if ($this->getMethod() === 'POST') {
            return $this->getParams();
        }

        return [];
    }

    /**
     * The graph version used for this request.
     *
     * @return string|null
     */
    public function getGraphVersion(): ?string
    {
        return $this->graphVersion;
    }

    /**
     * Generate and return the URL for this request.
     *
     * @return string
     */
    public function getUrl(): string
    {
        $this->validateMethod();

        $graphVersion = FacebookUrlManipulator::forceSlashPrefix($this->graphVersion);
        $endpoint = FacebookUrlManipulator::forceSlashPrefix($this->getEndpoint());

        $url = $graphVersion . $endpoint;

        if ($this->getMethod() !== 'POST') {
            $params = $this->getParams();
            $url = FacebookUrlManipulator::appendParamsToUrl($url, $params);
        }

        return $url;
    }

    /**
     * Return the default headers that every request should use.
     *
     * @return array
     */
    #[ArrayShape(['User-Agent' => "string", 'Accept-Encoding' => "string"])]
    public static function getDefaultHeaders(): array
    {
        return [
            'User-Agent' => 'fb-php-' . Facebook::VERSION,
            'Accept-Encoding' => '*',
        ];
    }
}
