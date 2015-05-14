<?php
/**
 * Copyright 2014 Facebook, Inc.
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
namespace Facebook\FileUpload;

use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;
use Facebook\Exceptions\FacebookUploadException;
use Facebook\Exceptions\FacebookUploadTransferException;
use Facebook\FacebookApp;
use Facebook\FacebookClient;
use Facebook\FacebookRequest;

/**
 * Class FacebookResumableUploader
 *
 * @package Facebook
 */
class FacebookResumableUploader
{
    /**
     * @var FacebookApp
     */
    protected $app;

    /**
     * @var string
     */
    protected $accessToken;

    /**
     * @var FacebookClient The Facebook client service.
     */
    protected $client;

    /**
     * @var int Max retry times
     */
    protected $maxTransferTries;

    /**
     * @param FacebookApp $app
     * @param string $accessToken
     * @param int $maxTransferTries
     */
    public function __construct(FacebookApp $app, $accessToken, $maxTransferTries = 5)
    {
        $this->accessToken = $accessToken;
        $this->app = $app;
        $this->client = new FacebookClient();
        $this->maxTransferTries = $maxTransferTries;
    }

    /**
     * Upload - phase start
     *
     * @param $endpoint
     * @param $filePath
     * @return FacebookTransferChunk
     *
     * @throws FacebookUploadException
     * @throws FacebookSDKException
     */
    public function start($endpoint, $filePath)
    {
        $fileSize = filesize($filePath);

        $startReqParams = [
            'upload_phase' => 'start',
            'file_size' => $fileSize,
        ];

        $request = new FacebookRequest(
            $this->app,
            $this->accessToken,
            'POST',
            $endpoint,
            $startReqParams
        );

        try {
            $res= $this->client->sendRequest($request)->getDecodedBody();

            $firstTransferChunk = new FacebookTransferChunk(
                $filePath,
                $res['upload_session_id'],
                $res['video_id'],
                $res['start_offset'],
                $res['end_offset'] - $res['start_offset']
            );

            return $firstTransferChunk;
        } catch (FacebookResponseException $e) {
            throw new FacebookUploadException($e->getResponse());
        }
    }

    /**
     * Upload - phase transfer
     *
     * @param $endpoint
     * @param FacebookTransferChunk $chunk
     * @return FacebookTransferChunk
     *
     * @throws FacebookUploadTransferException
     * @throws FacebookSDKException
     */
    public function transfer($endpoint, FacebookTransferChunk $chunk)
    {
        $transReqParams = [
            'upload_phase' => 'transfer',
            'upload_session_id' => $chunk->getUploadSessionId(),
            'start_offset' => $chunk->getStartOffset(),
            'video_file_chunk' => $chunk,
        ];

        $maxTransferTries = $this->maxTransferTries;

        $request = new FacebookRequest(
            $this->app,
            $this->accessToken,
            'POST',
            $endpoint,
            $transReqParams
        );

        while ($maxTransferTries > 0) {
            try {
                $res= $this->client->sendRequest($request)->getDecodedBody();

                $nextTransferChunk = new FacebookTransferChunk(
                    $chunk->getFilePath(),
                    $chunk->getUploadSessionId(),
                    $chunk->getVideoId(),
                    $res['start_offset'],
                    $res['end_offset'] - $res['start_offset']
                );

                return $nextTransferChunk;
            } catch (FacebookResponseException $e) {
                if (--$maxTransferTries <= 0) {
                    $resumeContext = new FacebookResumeContext(
                        $endpoint,
                        $this->accessToken,
                        $chunk
                    );
                    throw new FacebookUploadTransferException($e->getResponse(), $resumeContext);
                }
                usleep(500000);
                continue;
            }
        }
    }

    /**
     * Upload - phase finish
     *
     * @param $endpoint
     * @param $uploadSessionId
     * @return boolean
     *
     * @throws FacebookUploadException
     * @throws FacebookSDKException
     * @throws null
     */
    public function finish($endpoint, $uploadSessionId)
    {
        $finishReqParams = [
            'upload_phase' => 'finish',
            'upload_session_id' => $uploadSessionId,
        ];

        try {
            $request = new FacebookRequest(
                $this->app,
                $this->accessToken,
                'POST',
                $endpoint,
                $finishReqParams
            );

            $res = $this->client->sendRequest($request)->getDecodedBody();
            return $res['success'];
        } catch (FacebookResponseException $e) {
            throw new FacebookUploadException($e->getResponse());
        }
    }
}
