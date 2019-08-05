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
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;

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
     * @var ClientInterface HTTP client handler
     */
    private $httpClient;

    /**
     * @var RequestFactoryInterface
     */
    private $requestFactory;

    /**
     * @var StreamFactoryInterface
     */
    private $streamFactory;

    /**
     * @var int the number of calls that have been made to Graph
     */
    public static $requestCount = 0;

    /**
     * Instantiates a new Client object.
     *
     * @param ClientInterface $httpClient
     * @param RequestFactoryInterface $requestFactory
     * @param StreamFactoryInterface  $streamFactory
     * @param bool            $enableBeta
     */
    public function __construct(
        ClientInterface $httpClient,
        RequestFactoryInterface $requestFactory,
        StreamFactoryInterface $streamFactory,
        $enableBeta = false
    ) {
        $this->httpClient = $httpClient;
        $this->enableBetaMode = $enableBeta;
        $this->requestFactory = $requestFactory;
        $this->streamFactory = $streamFactory;
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
     * @deprecated
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
     * @return Response
     * @throws ClientExceptionInterface
     *
     * @throws SDKException
     */
    public function sendRequest(Request $request)
    {
        if (get_class($request) === 'Facebook\Request') {
            $request->validateAccessToken();
        }

        $psr7Request = $this->createPSR7RequestFromFacebookRequest($request);
        // Add headers to FacebookRequest
        $request->setHeaders($this->flattenPSR7Headers($psr7Request, $psr7Request->getHeaders()));

        $psr7Response = $this->httpClient->sendRequest($psr7Request);

        static::$requestCount++;

        // Prepare headers from associative array to a single string for each header.
        $responseHeaders = $this->flattenPSR7Headers($psr7Response);

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
     * @return BatchResponse
     * @throws ClientExceptionInterface
     *
     * @throws SDKException
     */
    public function sendBatchRequest(BatchRequest $request)
    {
        $request->prepareRequestsForBatch();
        $Response = $this->sendRequest($request);

        return new BatchResponse($request, $Response);
    }

    /**
     * @TODO Move this to the Facebook\Request class
     * Create and prepares a PSR-7 object from a Facebook\Request object to be used with a PSR-18 Client
     * @param Request $facebookRequest
     * @return RequestInterface
     */
    private function createPSR7RequestFromFacebookRequest(Request $facebookRequest): RequestInterface
    {
        $postToVideoUrl = $facebookRequest->containsVideoUploads();
        $uri = $this->getBaseGraphUrl($postToVideoUrl) . $facebookRequest->getUrl();

        $psrRequest = $this->requestFactory->createRequest($facebookRequest->getMethod(), $uri);

        // If we're sending files they should be sent as multipart/form-data
        if ($facebookRequest->containsFileUploads()) {
            $requestBody = $facebookRequest->getMultipartBody();
            $psrRequest = $psrRequest->withHeader(
                'Content-Type',
                'multipart/form-data; boundary=' . $requestBody->getBoundary()
            );
        } else {
            $requestBody = $facebookRequest->getUrlEncodedBody();
            $psrRequest = $psrRequest->withHeader('Content-Type', 'application/x-www-form-urlencoded');
        }

        // Create a StreamInterface from request body.
        $bodyStream = $this->streamFactory->createStream($requestBody->getBody());

        return $psrRequest->withBody($bodyStream);
    }

    /**
     * Flatten PSR-7 headers such they can be added to a Facebook\Response|Facebook\Request
     * @param MessageInterface $psr7Message
     * @param array $initialValues
     * @return array
     */
    private function flattenPSR7Headers(MessageInterface $psr7Message, array $initialValues = []): array
    {
        $flattenedHeaders = array_map(
            function ($value) { return implode(', ', $value); },
            $psr7Message->getHeaders()
        );
        return array_merge($initialValues, $flattenedHeaders);
    }
}
