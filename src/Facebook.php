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

use Facebook\Authentication\AccessToken;
use Facebook\Authentication\OAuth2Client;
use Facebook\FileUpload\File;
use Facebook\FileUpload\ResumableUploader;
use Facebook\FileUpload\TransferChunk;
use Facebook\FileUpload\Video;
use Facebook\GraphNode\GraphEdge;
use Facebook\Url\UrlDetectionInterface;
use Facebook\Url\UrlDetectionHandler;
use Facebook\PersistentData\PersistentDataFactory;
use Facebook\PersistentData\PersistentDataInterface;
use Facebook\Helper\CanvasHelper;
use Facebook\Helper\JavaScriptHelper;
use Facebook\Helper\PageTabHelper;
use Facebook\Helper\RedirectLoginHelper;
use Facebook\Exception\SDKException;
use Http\Client\HttpClient;

/**
 * @package Facebook
 */
class Facebook
{
    /**
     * @const string Version number of the Facebook PHP SDK.
     */
    const VERSION = '6.0-dev';

    /**
     * @const string The name of the environment variable that contains the app ID.
     */
    const APP_ID_ENV_NAME = 'FACEBOOK_APP_ID';

    /**
     * @const string The name of the environment variable that contains the app secret.
     */
    const APP_SECRET_ENV_NAME = 'FACEBOOK_APP_SECRET';

    /**
     * @var Application the Application entity
     */
    protected $app;

    /**
     * @var Client the Facebook client service
     */
    protected $client;

    /**
     * @var OAuth2Client The OAuth 2.0 client service.
     */
    protected $oAuth2Client;

    /**
     * @var null|UrlDetectionInterface the URL detection handler
     */
    protected $urlDetectionHandler;

    /**
     * @var null|AccessToken the default access token to use with requests
     */
    protected $defaultAccessToken;

    /**
     * @var null|string the default Graph version we want to use
     */
    protected $defaultGraphVersion;

    /**
     * @var null|PersistentDataInterface the persistent data handler
     */
    protected $persistentDataHandler;

    /**
     * @var null|BatchResponse|Response stores the last request made to Graph
     */
    protected $lastResponse;

    /**
     * Instantiates a new Facebook super-class object.
     *
     * @param array $config
     *
     * @throws SDKException
     */
    public function __construct(array $config = [])
    {
        $config = array_merge([
            'app_id' => getenv(static::APP_ID_ENV_NAME),
            'app_secret' => getenv(static::APP_SECRET_ENV_NAME),
            'default_graph_version' => null,
            'enable_beta_mode' => false,
            'http_client' => null,
            'persistent_data_handler' => null,
            'url_detection_handler' => null,
        ], $config);

        if (!$config['app_id']) {
            throw new SDKException('Required "app_id" key not supplied in config and could not find fallback environment variable "' . static::APP_ID_ENV_NAME . '"');
        }
        if (!$config['app_secret']) {
            throw new SDKException('Required "app_secret" key not supplied in config and could not find fallback environment variable "' . static::APP_SECRET_ENV_NAME . '"');
        }
        if ($config['http_client'] !== null && !$config['http_client'] instanceof HttpClient) {
            throw new \InvalidArgumentException('Required "http_client" key to be null or an instance of \Http\Client\HttpClient');
        }
        if (!$config['default_graph_version']) {
            throw new \InvalidArgumentException('Required "default_graph_version" key not supplied in config');
        }

        $this->app = new Application($config['app_id'], $config['app_secret']);
        $this->client = new Client(
            $config['http_client'],
            $config['enable_beta_mode']
        );
        $this->setUrlDetectionHandler($config['url_detection_handler'] ?: new UrlDetectionHandler());
        $this->persistentDataHandler = PersistentDataFactory::createPersistentDataHandler(
            $config['persistent_data_handler']
        );

        if (isset($config['default_access_token'])) {
            $this->setDefaultAccessToken($config['default_access_token']);
        }

        $this->defaultGraphVersion = $config['default_graph_version'];
    }

    /**
     * Returns the Application entity.
     *
     * @return Application
     */
    public function getApplication()
    {
        return $this->app;
    }

    /**
     * Returns the Client service.
     *
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Returns the OAuth 2.0 client service.
     *
     * @return OAuth2Client
     */
    public function getOAuth2Client()
    {
        if (!$this->oAuth2Client instanceof OAuth2Client) {
            $app = $this->getApplication();
            $client = $this->getClient();
            $this->oAuth2Client = new OAuth2Client($app, $client, $this->defaultGraphVersion);
        }

        return $this->oAuth2Client;
    }

    /**
     * Returns the last response returned from Graph.
     *
     * @return null|BatchResponse|Response
     */
    public function getLastResponse()
    {
        return $this->lastResponse;
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
     * Changes the URL detection handler.
     *
     * @param UrlDetectionInterface $urlDetectionHandler
     */
    private function setUrlDetectionHandler(UrlDetectionInterface $urlDetectionHandler)
    {
        $this->urlDetectionHandler = $urlDetectionHandler;
    }

    /**
     * Returns the default AccessToken entity.
     *
     * @return null|AccessToken
     */
    public function getDefaultAccessToken()
    {
        return $this->defaultAccessToken;
    }

    /**
     * Sets the default access token to use with requests.
     *
     * @param AccessToken|string $accessToken the access token to save
     *
     * @throws \InvalidArgumentException
     */
    public function setDefaultAccessToken($accessToken)
    {
        if (is_string($accessToken)) {
            $this->defaultAccessToken = new AccessToken($accessToken);

            return;
        }

        if ($accessToken instanceof AccessToken) {
            $this->defaultAccessToken = $accessToken;

            return;
        }

        throw new \InvalidArgumentException('The default access token must be of type "string" or Facebook\AccessToken');
    }

    /**
     * Returns the default Graph version.
     *
     * @return string
     */
    public function getDefaultGraphVersion()
    {
        return $this->defaultGraphVersion;
    }

    /**
     * Returns the redirect login helper.
     *
     * @return RedirectLoginHelper
     */
    public function getRedirectLoginHelper()
    {
        return new RedirectLoginHelper(
            $this->getOAuth2Client(),
            $this->persistentDataHandler,
            $this->urlDetectionHandler
        );
    }

    /**
     * Returns the JavaScript helper.
     *
     * @return JavaScriptHelper
     */
    public function getJavaScriptHelper()
    {
        return new JavaScriptHelper($this->app, $this->client, $this->defaultGraphVersion);
    }

    /**
     * Returns the canvas helper.
     *
     * @return CanvasHelper
     */
    public function getCanvasHelper()
    {
        return new CanvasHelper($this->app, $this->client, $this->defaultGraphVersion);
    }

    /**
     * Returns the page tab helper.
     *
     * @return PageTabHelper
     */
    public function getPageTabHelper()
    {
        return new PageTabHelper($this->app, $this->client, $this->defaultGraphVersion);
    }

    /**
     * Sends a GET request to Graph and returns the result.
     *
     * @param string                  $endpoint
     * @param null|AccessToken|string $accessToken
     * @param null|string             $eTag
     * @param null|string             $graphVersion
     *
     * @throws SDKException
     *
     * @return Response
     */
    public function get($endpoint, $accessToken = null, $eTag = null, $graphVersion = null)
    {
        return $this->sendRequest(
            'GET',
            $endpoint,
            $params = [],
            $accessToken,
            $eTag,
            $graphVersion
        );
    }

    /**
     * Sends a POST request to Graph and returns the result.
     *
     * @param string                  $endpoint
     * @param array                   $params
     * @param null|AccessToken|string $accessToken
     * @param null|string             $eTag
     * @param null|string             $graphVersion
     *
     * @throws SDKException
     *
     * @return Response
     */
    public function post($endpoint, array $params = [], $accessToken = null, $eTag = null, $graphVersion = null)
    {
        return $this->sendRequest(
            'POST',
            $endpoint,
            $params,
            $accessToken,
            $eTag,
            $graphVersion
        );
    }

    /**
     * Sends a DELETE request to Graph and returns the result.
     *
     * @param string                  $endpoint
     * @param array                   $params
     * @param null|AccessToken|string $accessToken
     * @param null|string             $eTag
     * @param null|string             $graphVersion
     *
     * @throws SDKException
     *
     * @return Response
     */
    public function delete($endpoint, array $params = [], $accessToken = null, $eTag = null, $graphVersion = null)
    {
        return $this->sendRequest(
            'DELETE',
            $endpoint,
            $params,
            $accessToken,
            $eTag,
            $graphVersion
        );
    }

    /**
     * Sends a request to Graph for the next page of results.
     *
     * @param GraphEdge $graphEdge the GraphEdge to paginate over
     *
     * @throws SDKException
     *
     * @return null|GraphEdge
     */
    public function next(GraphEdge $graphEdge)
    {
        return $this->getPaginationResults($graphEdge, 'next');
    }

    /**
     * Sends a request to Graph for the previous page of results.
     *
     * @param GraphEdge $graphEdge the GraphEdge to paginate over
     *
     * @throws SDKException
     *
     * @return null|GraphEdge
     */
    public function previous(GraphEdge $graphEdge)
    {
        return $this->getPaginationResults($graphEdge, 'previous');
    }

    /**
     * Sends a request to Graph for the next page of results.
     *
     * @param GraphEdge $graphEdge the GraphEdge to paginate over
     * @param string    $direction the direction of the pagination: next|previous
     *
     * @throws SDKException
     *
     * @return null|GraphEdge
     */
    public function getPaginationResults(GraphEdge $graphEdge, $direction)
    {
        $paginationRequest = $graphEdge->getPaginationRequest($direction);
        if (!$paginationRequest) {
            return null;
        }

        $this->lastResponse = $this->client->sendRequest($paginationRequest);

        // Keep the same GraphNode subclass
        $subClassName = $graphEdge->getSubClassName();
        $graphEdge = $this->lastResponse->getGraphEdge($subClassName, false);

        return count($graphEdge) > 0 ? $graphEdge : null;
    }

    /**
     * Sends a request to Graph and returns the result.
     *
     * @param string                  $method
     * @param string                  $endpoint
     * @param array                   $params
     * @param null|AccessToken|string $accessToken
     * @param null|string             $eTag
     * @param null|string             $graphVersion
     *
     * @throws SDKException
     *
     * @return Response
     */
    public function sendRequest($method, $endpoint, array $params = [], $accessToken = null, $eTag = null, $graphVersion = null)
    {
        $accessToken = $accessToken ?: $this->defaultAccessToken;
        $graphVersion = $graphVersion ?: $this->defaultGraphVersion;
        $request = $this->request($method, $endpoint, $params, $accessToken, $eTag, $graphVersion);

        return $this->lastResponse = $this->client->sendRequest($request);
    }

    /**
     * Sends a batched request to Graph and returns the result.
     *
     * @param array                   $requests
     * @param null|AccessToken|string $accessToken
     * @param null|string             $graphVersion
     *
     * @throws SDKException
     *
     * @return BatchResponse
     */
    public function sendBatchRequest(array $requests, $accessToken = null, $graphVersion = null)
    {
        $accessToken = $accessToken ?: $this->defaultAccessToken;
        $graphVersion = $graphVersion ?: $this->defaultGraphVersion;
        $batchRequest = new BatchRequest(
            $this->app,
            $requests,
            $accessToken,
            $graphVersion
        );

        return $this->lastResponse = $this->client->sendBatchRequest($batchRequest);
    }

    /**
     * Instantiates an empty BatchRequest entity.
     *
     * @param null|AccessToken|string $accessToken  The top-level access token. Requests with no access token
     *                                              will fallback to this.
     * @param null|string             $graphVersion the Graph API version to use
     *
     * @return BatchRequest
     */
    public function newBatchRequest($accessToken = null, $graphVersion = null)
    {
        $accessToken = $accessToken ?: $this->defaultAccessToken;
        $graphVersion = $graphVersion ?: $this->defaultGraphVersion;

        return new BatchRequest(
            $this->app,
            [],
            $accessToken,
            $graphVersion
        );
    }

    /**
     * Instantiates a new Request entity.
     *
     * @param string                  $method
     * @param string                  $endpoint
     * @param array                   $params
     * @param null|AccessToken|string $accessToken
     * @param null|string             $eTag
     * @param null|string             $graphVersion
     *
     * @throws SDKException
     *
     * @return Request
     */
    public function request($method, $endpoint, array $params = [], $accessToken = null, $eTag = null, $graphVersion = null)
    {
        $accessToken = $accessToken ?: $this->defaultAccessToken;
        $graphVersion = $graphVersion ?: $this->defaultGraphVersion;

        return new Request(
            $this->app,
            $accessToken,
            $method,
            $endpoint,
            $params,
            $eTag,
            $graphVersion
        );
    }

    /**
     * Factory to create File's.
     *
     * @param string $pathToFile
     *
     * @throws SDKException
     *
     * @return File
     */
    public function fileToUpload($pathToFile)
    {
        return new File($pathToFile);
    }

    /**
     * Factory to create Video's.
     *
     * @param string $pathToFile
     *
     * @throws SDKException
     *
     * @return Video
     */
    public function videoToUpload($pathToFile)
    {
        return new Video($pathToFile);
    }

    /**
     * Upload a video in chunks.
     *
     * @param int         $target           the id of the target node before the /videos edge
     * @param string      $pathToFile       the full path to the file
     * @param array       $metadata         the metadata associated with the video file
     * @param null|string $accessToken      the access token
     * @param int         $maxTransferTries the max times to retry a failed upload chunk
     * @param null|string $graphVersion     the Graph API version to use
     *
     * @throws SDKException
     *
     * @return array
     */
    public function uploadVideo($target, $pathToFile, $metadata = [], $accessToken = null, $maxTransferTries = 5, $graphVersion = null)
    {
        $accessToken = $accessToken ?: $this->defaultAccessToken;
        $graphVersion = $graphVersion ?: $this->defaultGraphVersion;

        $uploader = new ResumableUploader($this->app, $this->client, $accessToken, $graphVersion);
        $endpoint = '/'.$target.'/videos';
        $file = $this->videoToUpload($pathToFile);
        $chunk = $uploader->start($endpoint, $file);

        do {
            $chunk = $this->maxTriesTransfer($uploader, $endpoint, $chunk, $maxTransferTries);
        } while (!$chunk->isLastChunk());

        return [
          'video_id' => $chunk->getVideoId(),
          'success' => $uploader->finish($endpoint, $chunk->getUploadSessionId(), $metadata),
        ];
    }

    /**
     * Attempts to upload a chunk of a file in $retryCountdown tries.
     *
     * @param ResumableUploader $uploader
     * @param string            $endpoint
     * @param TransferChunk     $chunk
     * @param int               $retryCountdown
     *
     * @throws SDKException
     *
     * @return TransferChunk
     */
    private function maxTriesTransfer(ResumableUploader $uploader, $endpoint, TransferChunk $chunk, $retryCountdown)
    {
        $newChunk = $uploader->transfer($endpoint, $chunk, $retryCountdown < 1);

        if ($newChunk !== $chunk) {
            return $newChunk;
        }

        $retryCountdown--;

        // If transfer() returned the same chunk entity, the transfer failed but is resumable.
        return $this->maxTriesTransfer($uploader, $endpoint, $chunk, $retryCountdown);
    }
}
