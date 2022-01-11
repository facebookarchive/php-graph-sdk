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

use Facebook\Exceptions\FacebookSDKException;
use GuzzleHttp\Client as HttpClient;
use Psr\Http\Client\ClientInterface;

/**
 * Class Client
 *
 * @package Facebook
 */
class Client
{
    /**
     * @const string Production Graph API URL.
     */
    const BASE_GRAPH_URL = 'https://graph.facebook.com';

    /**
     * @const string Graph API URL for video uploads.
     */
    const BASE_GRAPH_VIDEO_URL = 'https://graph-video.facebook.com';

    /**
     * @const string Beta Graph API URL.
     */
    const BASE_GRAPH_URL_BETA = 'https://graph.beta.facebook.com';

    /**
     * @const string Beta Graph API URL for video uploads.
     */
    const BASE_GRAPH_VIDEO_URL_BETA = 'https://graph-video.beta.facebook.com';

    /**
     * @const int The timeout in seconds for a normal request.
     */
    const DEFAULT_REQUEST_TIMEOUT = 60;

    /**
     * @const int The timeout in seconds for a request that contains file uploads.
     */
    const DEFAULT_FILE_UPLOAD_REQUEST_TIMEOUT = 3600;

    /**
     * @const int The timeout in seconds for a request that contains video uploads.
     */
    const DEFAULT_VIDEO_UPLOAD_REQUEST_TIMEOUT = 7200;

    /**
     * @var ClientInterface HTTP client handler.
     */
    protected ClientInterface $httpCllient;

    /**
     * @var int The number of calls that have been made to Graph.
     */
    public static int $requestCount = 0;

    /**
     * Instantiates a new FacebookClient object.
     *
     * @param ?ClientInterface $httpClient
     */
    public function __construct(
        ClientInterface $httpClient = null,
        protected bool  $enableBetaMode = false
    )
    {
        $this->httpCllient = $httpClient ?? new HttpClient();
    }

    /**
     * Sets the HTTP client handler.
     */
    public function setHttpClient(HttpClient $httpClient): void
    {
        $this->httpCllient = $httpClient;
    }

    /**
     * Returns the HTTP client handler.
     *
     */
    public function getHttpCllient(): HttpClient
    {
        return $this->httpCllient;
    }

    /**
     * Toggle beta mode.
     *
     * @param boolean $betaMode
     */
    public function enableBetaMode(bool $betaMode = true)
    {
        $this->enableBetaMode = $betaMode;
    }

    /**
     * Returns the base Graph URL.
     *
     * @param boolean $postToVideoUrl Post to the video API if videos are being uploaded.
     *
     * @return string
     */
    public function getBaseGraphUrl(bool $postToVideoUrl = false): string
    {
        if ($postToVideoUrl) {
            return $this->enableBetaMode ? static::BASE_GRAPH_VIDEO_URL_BETA : static::BASE_GRAPH_VIDEO_URL;
        }

        return $this->enableBetaMode ? static::BASE_GRAPH_URL_BETA : static::BASE_GRAPH_URL;
    }

    /**
     * Prepares the request for sending to the client handler.
     *
     * @param Request|BatchRequest $request
     *
     * @return array
     */
    public function prepareRequestMessage(Request|BatchRequest $request): array
    {
        $postToVideoUrl = $request->containsVideoUploads();
        $url = $this->getBaseGraphUrl($postToVideoUrl) . $request->getUrl();

        // If we're sending files they should be sent as multipart/form-data
        if ($request->containsFileUploads()) {
            $requestBody = $request->getMultipartBody();
            $request->setHeaders([
                'Content-Type' => 'multipart/form-data; boundary=' . $requestBody->getBoundary(),
            ]);
        } else {
            $requestBody = $request->getUrlEncodedBody();
            $request->setHeaders([
                'Content-Type' => 'application/x-www-form-urlencoded',
            ]);
        }

        return [
            $url,
            $request->getMethod(),
            $request->getHeaders(),
            $requestBody->getBody(),
        ];
    }

    /**
     * Makes the request to Graph and returns the result.
     *
     * @param Request $request
     *
     * @return Response
     *
     * @throws FacebookSDKException
     */
    public function sendRequest(Request $request): Response
    {
        if ($request::class === Request::class) {
            $request->validateAccessToken();
        }

        [$url, $method, $headers, $body] = $this->prepareRequestMessage($request);

        // Should throw `FacebookSDKException` exception on HTTP client error.
        // Don't catch to allow it to bubble up.
        $rawResponse = $this->getHttpCllient()->request($method, $url, [
            'body' => $body,
            'headers' => $headers,
            'verify' => false
        ]);

        static::$requestCount++;

        $response = new Response(
            $request,
            $rawResponse->getBody()->getContents(),
            $rawResponse->getStatusCode(),
            $rawResponse->getHeaders()
        );

        if ($response->isError()) {
            throw $response->getThrownException();
        }

        return $response;
    }

    /**
     * Makes a batched request to Graph and returns the result.
     *
     * @param BatchRequest $request
     *
     * @return BatchResponse
     *
     * @throws FacebookSDKException
     */
    public function sendBatchRequest(BatchRequest $request): BatchResponse
    {
        $request->prepareRequestsForBatch();
        $facebookResponse = $this->sendRequest($request);

        return new BatchResponse($request, $facebookResponse);
    }
}
