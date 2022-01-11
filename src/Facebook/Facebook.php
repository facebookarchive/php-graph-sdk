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
 *
 */

namespace Facebook;

use Facebook\Authentication\AccessToken;
use Facebook\Authentication\OAuth2Client;
use Facebook\FileUpload\File;
use Facebook\FileUpload\ResumableUploader;
use Facebook\FileUpload\TransferChunk;
use Facebook\FileUpload\Video;
use Facebook\GraphNodes\GraphEdge;
use Facebook\Url\UrlDetectionInterface;
use Facebook\Url\UrlDetectionHandler;
use Facebook\PersistentData\PersistentDataFactory;
use Facebook\PersistentData\PersistentDataInterface;
use Facebook\Helpers\CanvasHelper;
use Facebook\Helpers\JavaScriptHelper;
use Facebook\Helpers\PageTabHelper;
use Facebook\Helpers\RedirectLoginHelper;
use Facebook\Exceptions\FacebookSDKException;
use InvalidArgumentException;
use JetBrains\PhpStorm\ArrayShape;

/**
 * Class Facebook
 *
 * @package Facebook
 */
class Facebook
{
    /**
     * @const string Version number of the Facebook PHP SDK.
     */
    const VERSION = '1.0';

    /**
     * @const string The name of the environment variable that contains the app ID.
     */
    const APP_ID_ENV_NAME = 'FACEBOOK_APP_ID';

    /**
     * @const string The name of the environment variable that contains the app secret.
     */
    const APP_SECRET_ENV_NAME = 'FACEBOOK_APP_SECRET';

    /**
     * @var Application The FacebookApp entity.
     */
    protected Application $app;

    /**
     * @var Client The Facebook client service.
     */
    protected Client $client;

    /**
     * @var ?OAuth2Client The OAuth 2.0 client service.
     */
    protected ?OAuth2Client $oAuth2Client = null;

    /**
     * @var UrlDetectionInterface|null The URL detection handler.
     */
    protected ?UrlDetectionInterface $urlDetectionHandler;

    /**
     * @var AccessToken|null The default access token to use with requests.
     */
    protected ?AccessToken $defaultAccessToken;

    /**
     * @var string|null The default Graph version we want to use.
     */
    protected ?string $defaultGraphVersion;

    /**
     * @var PersistentDataInterface|null The persistent data handler.
     */
    protected ?PersistentDataInterface $persistentDataHandler;

    /**
     * @var Response|BatchResponse|null Stores the last request made to Graph.
     */
    protected BatchResponse|null|Response $lastResponse;

    /**
     * Instantiates a new Facebook super-class object.
     *
     * @param array $config
     *
     * @throws FacebookSDKException
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
            throw new FacebookSDKException('Required "app_id" key not supplied in config and could not find fallback environment variable "' . static::APP_ID_ENV_NAME . '"');
        }
        if (!$config['app_secret']) {
            throw new FacebookSDKException('Required "app_secret" key not supplied in config and could not find fallback environment variable "' . static::APP_SECRET_ENV_NAME . '"');
        }
        if ($config['http_client'] !== null && !$config['http_client'] instanceof \GuzzleHttp\Client) {
            throw new InvalidArgumentException('Required "http_client" key to be null or an instance of \Http\Client\HttpClient');
        }
        if (!$config['default_graph_version']) {
            throw new InvalidArgumentException('Required "default_graph_version" key not supplied in config');
        }

        $this->app = new Application($config['app_id'], $config['app_secret']);
        $this->client = new Client($config['http_client'], $config['enable_beta_mode']);
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
     * Returns the FacebookApp entity.
     *
     * @return Application
     */
    public function getApplication(): Application
    {
        return $this->app;
    }

    /**
     * Returns the FacebookClient service.
     *
     * @return Client
     */
    public function getClient(): Client
    {
        return $this->client;
    }

    /**
     * Returns the OAuth 2.0 client service.
     *
     * @return OAuth2Client
     */
    public function getOAuth2Client(): OAuth2Client
    {
        if ($this->oAuth2Client === null) {
            $application = $this->getApplication();
            $client = $this->getClient();
            $this->oAuth2Client = new OAuth2Client($application, $client, $this->defaultGraphVersion);
        }

        return $this->oAuth2Client;
    }

    /**
     * Returns the last response returned from Graph.
     *
     * @return Response|BatchResponse|null
     */
    public function getLastResponse(): Response|BatchResponse|null
    {
        return $this->lastResponse;
    }

    /**
     * Returns the URL detection handler.
     *
     * @return \Facebook\Url\UrlDetectionInterface|null
     */
    public function getUrlDetectionHandler(): ?UrlDetectionInterface
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
     * @return AccessToken|null
     */
    public function getDefaultAccessToken(): ?AccessToken
    {
        return $this->defaultAccessToken;
    }

    /**
     * Sets the default access token to use with requests.
     *
     * @param mixed $accessToken The access token to save.
     *
     * @throws \InvalidArgumentException
     */
    public function setDefaultAccessToken(mixed $accessToken): void
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
     * @return string|null
     */
    public function getDefaultGraphVersion(): ?string
    {
        return $this->defaultGraphVersion;
    }

    /**
     * Returns the redirect login helper.
     *
     * @return RedirectLoginHelper
     */
    public function getRedirectLoginHelper(): RedirectLoginHelper
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
    public function getJavaScriptHelper(): JavaScriptHelper
    {
        return new JavaScriptHelper($this->app, $this->client, $this->defaultGraphVersion);
    }

    /**
     * Returns the canvas helper.
     *
     * @return CanvasHelper
     */
    public function getCanvasHelper(): CanvasHelper
    {
        return new CanvasHelper($this->app, $this->client, $this->defaultGraphVersion);
    }

    /**
     * Returns the page tab helper.
     *
     * @return PageTabHelper
     */
    public function getPageTabHelper(): PageTabHelper
    {
        return new PageTabHelper($this->app, $this->client, $this->defaultGraphVersion);
    }

    /**
     * Sends a GET request to Graph and returns the result.
     *
     * @param string                  $endpoint
     * @param string|AccessToken|null $accessToken
     * @param string|null             $eTag
     * @param string|null             $graphVersion
     *
     * @return Response
     *
     * @throws FacebookSDKException
     */
    public function get(
        string             $endpoint,
        string|AccessToken $accessToken = null,
        string             $eTag = null,
        string             $graphVersion = null
    ): Response
    {
        return $this->sendRequest(
            'GET',
            $endpoint,
            [],
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
     * @param string|AccessToken|null $accessToken
     * @param string|null             $eTag
     * @param string|null             $graphVersion
     *
     * @return Response
     *
     * @throws FacebookSDKException
     */
    public function post(
        string             $endpoint,
        array              $params = [],
        string|AccessToken $accessToken = null,
        string             $eTag = null,
        string             $graphVersion = null
    ): Response
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
     * @param string|AccessToken|null $accessToken
     * @param string|null             $eTag
     * @param string|null             $graphVersion
     *
     * @return Response
     *
     * @throws FacebookSDKException
     */
    public function delete(
        string             $endpoint,
        array              $params = [],
        string|AccessToken $accessToken = null,
        string             $eTag = null,
        string             $graphVersion = null,
    ): Response
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
     * @param GraphEdge $graphEdge The GraphEdge to paginate over.
     *
     * @return GraphEdge|null
     *
     * @throws FacebookSDKException
     */
    public function next(GraphEdge $graphEdge): ?GraphEdge
    {
        return $this->getPaginationResults($graphEdge, 'next');
    }

    /**
     * Sends a request to Graph for the previous page of results.
     *
     * @param GraphEdge $graphEdge The GraphEdge to paginate over.
     *
     * @return GraphEdge|null
     *
     * @throws FacebookSDKException
     */
    public function previous(GraphEdge $graphEdge): ?GraphEdge
    {
        return $this->getPaginationResults($graphEdge, 'previous');
    }

    /**
     * Sends a request to Graph for the next page of results.
     *
     * @param GraphEdge $graphEdge The GraphEdge to paginate over.
     * @param string    $direction The direction of the pagination: next|previous.
     *
     * @return GraphEdge|null
     *
     * @throws FacebookSDKException
     */
    public function getPaginationResults(GraphEdge $graphEdge, string $direction): ?GraphEdge
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
     * @param string|AccessToken|null $accessToken
     * @param string|null             $eTag
     * @param string|null             $graphVersion
     *
     * @return Response
     *
     * @throws FacebookSDKException
     */
    public function sendRequest(
        string             $method,
        string             $endpoint,
        array              $params = [],
        string|AccessToken $accessToken = null,
        string             $eTag = null,
        string             $graphVersion = null,
    ): Response
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
     * @param string|AccessToken|null $accessToken
     * @param string|null             $graphVersion
     *
     * @return BatchResponse
     *
     * @throws FacebookSDKException
     */
    public function sendBatchRequest(
        array              $requests,
        string|AccessToken $accessToken = null,
        string             $graphVersion = null
    ): BatchResponse
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
     * Instantiates an empty FacebookBatchRequest entity.
     *
     * @param string|AccessToken|null $accessToken   The top-level access token. Requests with no access token
     *                                               will fallback to this.
     * @param string|null             $graphVersion  The Graph API version to use.
     *
     * @return BatchRequest
     */
    public function newBatchRequest(string|AccessToken $accessToken = null, string $graphVersion = null): BatchRequest
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
     * Instantiates a new FacebookRequest entity.
     *
     * @param string                  $method
     * @param string                  $endpoint
     * @param array                   $params
     * @param string|AccessToken|null $accessToken
     * @param string|null             $eTag
     * @param string|null             $graphVersion
     *
     * @return Request
     *
     * @throws FacebookSDKException
     */
    public function request(
        string             $method,
        string             $endpoint,
        array              $params = [],
        string|AccessToken $accessToken = null,
        string             $eTag = null,
        string             $graphVersion = null,
    ): Request
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
     * Factory to create FacebookFile's.
     *
     * @param string $pathToFile
     *
     * @return File
     *
     * @throws FacebookSDKException
     */
    public function fileToUpload(string $pathToFile): File
    {
        return new File($pathToFile);
    }

    /**
     * Factory to create FacebookVideo's.
     *
     * @param string $pathToFile
     *
     * @return Video
     *
     * @throws FacebookSDKException
     */
    public function videoToUpload(string $pathToFile): Video
    {
        return new Video($pathToFile);
    }

    /**
     * Upload a video in chunks.
     *
     * @param int|string                                       $target           The id of the target node before the
     *                                                                           /videos edge.
     * @param string                                           $pathToFile       The full path to the file.
     * @param array                                            $metadata         The metadata associated with the video
     *                                                                           file.
     * @param string|\Facebook\Authentication\AccessToken|null $accessToken      The access token.
     * @param int                                              $maxTransferTries The max times to retry a failed upload
     *                                                                           chunk.
     * @param string|null                                      $graphVersion     The Graph API version to use.
     *
     * @return array
     *
     * @throws \Facebook\Exceptions\FacebookSDKException
     */
    #[ArrayShape(['video_id' => "int", 'success' => "bool"])] public function uploadVideo(
        int|string         $target,
        string             $pathToFile,
        array              $metadata = [],
        string|AccessToken $accessToken = null,
        int                $maxTransferTries = 5,
        string             $graphVersion = null,
    ): array
    {
        $accessToken ??= $this->defaultAccessToken;
        $graphVersion ??= $this->defaultGraphVersion;
        $uploader = new ResumableUploader($this->app, $this->client, $accessToken, $graphVersion);
        $endpoint = '/' . $target . '/videos';
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
     * @return TransferChunk
     *
     * @throws FacebookSDKException
     */
    private function maxTriesTransfer(
        ResumableUploader $uploader,
        string            $endpoint,
        TransferChunk     $chunk,
        int               $retryCountdown,
    ): TransferChunk
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
