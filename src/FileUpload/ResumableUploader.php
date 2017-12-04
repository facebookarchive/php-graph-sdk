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
namespace Facebook\FileUpload;

use Facebook\Authentication\AccessToken;
use Facebook\Exception\ResponseException;
use Facebook\Exception\ResumableUploadException;
use Facebook\Exception\SDKException;
use Facebook\Application;
use Facebook\Client;
use Facebook\Request;

/**
 * @package Facebook
 */
class ResumableUploader
{
    /**
     * @var Application
     */
    protected $app;

    /**
     * @var string
     */
    protected $accessToken;

    /**
     * @var Client the Facebook client service
     */
    protected $client;

    /**
     * @var string graph version to use for this request
     */
    protected $graphVersion;

    /**
     * @param Application             $app
     * @param Client                  $client
     * @param null|AccessToken|string $accessToken
     * @param string                  $graphVersion
     */
    public function __construct(Application $app, Client $client, $accessToken, $graphVersion)
    {
        $this->app = $app;
        $this->client = $client;
        $this->accessToken = $accessToken;
        $this->graphVersion = $graphVersion;
    }

    /**
     * Upload by chunks - start phase.
     *
     * @param string $endpoint
     * @param File   $file
     *
     * @throws SDKException
     *
     * @return TransferChunk
     */
    public function start($endpoint, File $file)
    {
        $params = [
            'upload_phase' => 'start',
            'file_size' => $file->getSize(),
        ];
        $response = $this->sendUploadRequest($endpoint, $params);

        return new TransferChunk($file, $response['upload_session_id'], $response['video_id'], $response['start_offset'], $response['end_offset']);
    }

    /**
     * Upload by chunks - transfer phase.
     *
     * @param string        $endpoint
     * @param TransferChunk $chunk
     * @param bool          $allowToThrow
     *
     * @throws ResponseException
     *
     * @return TransferChunk
     */
    public function transfer($endpoint, TransferChunk $chunk, $allowToThrow = false)
    {
        $params = [
            'upload_phase' => 'transfer',
            'upload_session_id' => $chunk->getUploadSessionId(),
            'start_offset' => $chunk->getStartOffset(),
            'video_file_chunk' => $chunk->getPartialFile(),
        ];

        try {
            $response = $this->sendUploadRequest($endpoint, $params);
        } catch (ResponseException $e) {
            $preException = $e->getPrevious();
            if ($allowToThrow || !$preException instanceof ResumableUploadException) {
                throw $e;
            }

            // Return the same chunk entity so it can be retried.
            return $chunk;
        }

        return new TransferChunk($chunk->getFile(), $chunk->getUploadSessionId(), $chunk->getVideoId(), $response['start_offset'], $response['end_offset']);
    }

    /**
     * Upload by chunks - finish phase.
     *
     * @param string $endpoint
     * @param string $uploadSessionId
     * @param array  $metadata        the metadata associated with the file
     *
     * @throws SDKException
     *
     * @return bool
     */
    public function finish($endpoint, $uploadSessionId, $metadata = [])
    {
        $params = array_merge($metadata, [
            'upload_phase' => 'finish',
            'upload_session_id' => $uploadSessionId,
        ]);
        $response = $this->sendUploadRequest($endpoint, $params);

        return $response['success'];
    }

    /**
     * Helper to make a Request and send it.
     *
     * @param string $endpoint the endpoint to POST to
     * @param array  $params   the params to send with the request
     *
     * @return array
     */
    private function sendUploadRequest($endpoint, $params = [])
    {
        $request = new Request($this->app, $this->accessToken, 'POST', $endpoint, $params, null, $this->graphVersion);

        return $this->client->sendRequest($request)->getDecodedBody();
    }
}
