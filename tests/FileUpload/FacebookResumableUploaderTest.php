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

namespace Facebook\Tests;

use Facebook\FileUpload\FacebookFile;
use Facebook\FacebookApp;
use Facebook\FacebookClient;
use Facebook\FileUpload\FacebookResumableUploader;
use Facebook\FileUpload\FacebookTransferChunk;
use Facebook\FacebookRequest;
use Facebook\FacebookResponse;

class FooFacebookClientForResumableUpload extends FacebookClient
{
    protected $response = '';

    // Successful responses
    public function setSuccessfulStartResponse()
    {
        $this->response = '{"video_id":"1337","start_offset":"0","end_offset":"123","upload_session_id":"42"}';
    }

    public function setSuccessfulTransferResponse()
    {
        $this->response = '{"start_offset":"124","end_offset":"223"}';
    }

    public function setSuccessfulFinishResponse()
    {
        $this->response = '{"success":true}';
    }

    // Error responses
    public function setFailedStartResponse()
    {
        $this->response = '{"error":{"message":"Error validating access token: Session has expired on Monday, ' .
          '10-Aug-15 01:00:00 PDT. The current time is Monday, 10-Aug-15 01:14:23 PDT.",' .
          '"type":"OAuthException","code":190,"error_subcode":463}}';
    }

    public function setFailedTransferResponse()
    {
        $this->response = '{"error":{"message":"There was a problem uploading your video. Please try uploading it again.",' .
          '"type":"FacebookApiException","code":6000,"error_subcode":1363019}}';
    }

    public function sendRequest(FacebookRequest $request)
    {
        $returnResponse = new FacebookResponse($request, $this->response, 0, []);

        if ($returnResponse->isError()) {
            throw $returnResponse->getThrownException();
        }

        return $returnResponse;
    }
}

class FacebookResumableUploaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FacebookApp
     */
    private $fbApp;

    /**
     * @var FooFacebookClientForResumableUpload
     */
    private $client;

    /**
     * @var FacebookFile
     */
    private $file;

    public function setUp()
    {
        $this->fbApp = new FacebookApp('app_id', 'app_secret');
        $this->client = new FooFacebookClientForResumableUpload();
        $this->file = new FacebookFile(__DIR__.'/../foo.txt');
    }

    public function testResumableUploadCanStartTransferAndFinish()
    {
        $this->client->setSuccessfulStartResponse();
        $uploader = new FacebookResumableUploader($this->fbApp, $this->client, 'access_token', 'v2.4');
        $endpoint = '/me/videos';
        $chunk = $uploader->start($endpoint, $this->file);
        $this->assertInstanceOf('Facebook\FileUpload\FacebookTransferChunk', $chunk);
        $this->assertEquals('42', $chunk->getUploadSessionId());
        $this->assertEquals('1337', $chunk->getVideoId());

        $this->client->setSuccessfulTransferResponse();
        $newChunk = $uploader->transfer($endpoint, $chunk);
        $this->assertEquals('124', $newChunk->getStartOffset());
        $this->assertNotSame($newChunk, $chunk);

        $this->client->setSuccessfulFinishResponse();
        $finalResponse = $uploader->finish($endpoint, $chunk->getUploadSessionId(), []);
        $this->assertTrue($finalResponse);
    }

    /**
     * @expectedException \Facebook\Exceptions\FacebookResponseException
     */
    public function testStartWillLetErrorResponsesThrow()
    {
        $this->client->setFailedStartResponse();
        $uploader = new FacebookResumableUploader($this->fbApp, $this->client, 'access_token', 'v2.4');

        $chunk = $uploader->start('/me/videos', $this->file);
    }

    public function testFailedResumableTransferWillNotThrowAndReturnSameChunk()
    {
        $this->client->setFailedTransferResponse();
        $uploader = new FacebookResumableUploader($this->fbApp, $this->client, 'access_token', 'v2.4');

        $chunk = new FacebookTransferChunk($this->file, '1', '2', '3', '4');
        $newChunk = $uploader->transfer('/me/videos', $chunk);
        $this->assertSame($newChunk, $chunk);
    }

    public function testCanGetSuccessfulTransferWithMaxTries()
    {
        $this->client->setSuccessfulTransferResponse();
        $uploader = new FacebookResumableUploader($this->fbApp, $this->client, 'access_token', 'v2.4');

        $chunk = new FacebookTransferChunk($this->file, '1', '2', '3', '4');
        $newChunk = $uploader->maxTriesTransfer('/me/videos', $chunk, 3);
        $this->assertNotSame($newChunk, $chunk);
    }

    /**
     * @expectedException \Facebook\Exceptions\FacebookResponseException
     */
    public function testMaxingOutRetriesWillThrow()
    {
        $this->client->setFailedTransferResponse();
        $uploader = new FacebookResumableUploader($this->fbApp, $this->client, 'access_token', 'v2.4');

        $chunk = new FacebookTransferChunk($this->file, '1', '2', '3', '4');
        $newChunk = $uploader->maxTriesTransfer('/me/videos', $chunk, 3);
    }
}
