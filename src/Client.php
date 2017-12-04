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
namespace Facebook;

use Facebook\Exception\SDKException;
use Http\Client\HttpClient;
use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\MessageFactoryDiscovery;

/**
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
     * @var bool toggle to use Graph beta url
     */
    protected $enableBetaMode = false;

    /**
     * @var httpClient HTTP client handler
     */
    protected $httpClient;

    /**
     * @var int the number of calls that have been made to Graph
     */
    public static $requestCount = 0;

    /**
     * Instantiates a new Client object.
     *
     * @param null|HttpClient $httpClient
     * @param bool            $enableBeta
     */
    public function __construct(HttpClient $httpClient = null, $enableBeta = false)
    {
        $this->httpClient = $httpClient ?: HttpClientDiscovery::find();
        $this->enableBetaMode = $enableBeta;
    }

    /**
     * Sets the HTTP client handler.
     *
     * @param HttpClient $httpClient
     */
    public function setHttpClient(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * Returns the HTTP client handler.
     *
     * @return HttpClient
     */
    public function getHttpClient()
    {
        return $this->httpClient;
    }

    /**
     * Toggle beta mode.
     *
     * @param bool $betaMode
     */
    public function enableBetaMode($betaMode = true)
    {
        $this->enableBetaMode = $betaMode;
    }

    /**
     * Returns the base Graph URL.
     *
     * @param bool $postToVideoUrl post to the video API if videos are being uploaded
     *
     * @return string
     */
    public function getBaseGraphUrl($postToVideoUrl = false)
    {
        if ($postToVideoUrl) {
            return $this->enableBetaMode ? static::BASE_GRAPH_VIDEO_URL_BETA : static::BASE_GRAPH_VIDEO_URL;
        }

        return $this->enableBetaMode ? static::BASE_GRAPH_URL_BETA : static::BASE_GRAPH_URL;
    }

    /**
     * Prepares the request for sending to the client handler.
     *
     * @param Request $request
     *
     * @return array
     */
    public function prepareRequestMessage(Request $request)
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
     * @throws SDKException
     *
     * @return Response
     */
    public function sendRequest(Request $request)
    {
        if (get_class($request) === 'Facebook\Request') {
            $request->validateAccessToken();
        }

        list($url, $method, $headers, $body) = $this->prepareRequestMessage($request);

        $psr7Response = $this->httpClient->sendRequest(
            MessageFactoryDiscovery::find()->createRequest($method, $url, $headers, $body)
        );

        static::$requestCount++;

        // Prepare headers from associative array to a single string for each header.
        $responseHeaders = [];
        foreach ($psr7Response->getHeaders() as $name => $values) {
            $responseHeaders[] = sprintf('%s: %s', $name, implode(", ", $values));
        }

        $Response = new Response(
            $request,
            $psr7Response->getBody(),
            $psr7Response->getStatusCode(),
            $responseHeaders
        );

        if ($Response->isError()) {
            throw $Response->getThrownException();
        }

        return $Response;
    }

    /**
     * Makes a batched request to Graph and returns the result.
     *
     * @param BatchRequest $request
     *
     * @throws SDKException
     *
     * @return BatchResponse
     */
    public function sendBatchRequest(BatchRequest $request)
    {
        $request->prepareRequestsForBatch();
        $Response = $this->sendRequest($request);

        return new BatchResponse($request, $Response);
    }
}
