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

use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookResumableUploadException;
use Facebook\Exceptions\FacebookSDKException;
use Facebook\FacebookApp;
use Facebook\FacebookClient;
use Facebook\FileUpload\FacebookResumableUploader;
use Facebook\Http\GraphRawResponse;
use Facebook\HttpClients\FacebookHttpClientInterface;

class VideoUploadClientHandlerSuccess implements FacebookHttpClientInterface
{
    public function send($url, $method, $body, array $headers, $timeOut)
    {
        if (preg_split("/=/", explode('&', $body)[0])[1] == 'start') {
            return $this->mockStart();
        }

        if (preg_split("/=/", explode('&', $body)[0])[1] == 'finish') {
            return $this->mockFinish();
        }

        return $this->mockTransfer();
    }

    public function mockStart()
    {
        $header = [
            "Content-Type" => "application/json; charset=UTF-8",
            "Date" => "Mon, 10 Aug 2015 07:14:04 GMT",
            "facebook-api-version" => "v2.3",
        ];

        $body = '{"video_id":"162719974059555","start_offset":"0","end_offset":"2087",' .
            '"upload_session_id":"162719977392888"}';

        return new GraphRawResponse($header, $body, 200);
    }

    public function mockTransfer()
    {
        $header = [
            "Content-Type" => "application/json; charset=UTF-8",
            "Date" => "Mon, 10 Aug 2015 07:14:04 GMT",
            "facebook-api-version" => "v2.3",
        ];

        $body = '{"start_offset":"2087","end_offset":"2087"}';

        return new GraphRawResponse($header, $body, 200);
    }

    public function mockFinish()
    {
        $header = [
            "Content-Type" => "application/json; charset=UTF-8",
            "Date" => "Mon, 10 Aug 2015 07:14:04 GMT",
            "facebook-api-version" => "v2.3",
        ];

        $body = '{"success":true}';

        return new GraphRawResponse($header, $body, 200);
    }
}

class VideoUploadClientHandlerFail implements FacebookHttpClientInterface
{
    public function send($url, $method, $body, array $headers, $timeOut)
    {
        if (preg_split("/=/", explode('&', $body)[0])[1] == 'start') {
            return $this->mockStart();
        }

        if (preg_split("/=/", explode('&', $body)[0])[1] == 'finish') {
            return $this->mockFinish();
        }

        return $this->mockTransfer();
    }

    public function mockStart()
    {
        $header = [
            "Content-Type" => "application/json; charset=UTF-8",
            "Date" => "Mon, 10 Aug 2015 07:14:04 GMT",
            "facebook-api-version" => "v2.3",
            "WWW-Authenticate" => 'OAuth "Facebook Platform" "invalid_token" "Error validating access token',
        ];

        $body = '{"error":{"message":"Error validating access token: Session has expired on Monday, ' .
            '10-Aug-15 01:00:00 PDT. The current time is Monday, 10-Aug-15 01:14:23 PDT.",' .
            '"type":"OAuthException","code":190,"error_subcode":463}}';

        return new GraphRawResponse($header, $body, 400);
    }

    public function mockTransfer()
    {
        $header = [
            "Content-Type" => "application/json; charset=UTF-8",
            "Date" => "Mon, 10 Aug 2015 07:14:04 GMT",
            "facebook-api-version" => "v2.3",
            "WWW-Authenticate" => 'OAuth "Facebook Platform" "invalid_token" "Error validating access token',
        ];

        $body = '{"error":{"message":"Error validating access token: Session has expired on Monday, ' .
            '10-Aug-15 01:00:00 PDT. The current time is Monday, 10-Aug-15 01:14:23 PDT.",' .
            '"type":"OAuthException","code":190,"error_subcode":1363033}}';

        return new GraphRawResponse($header, $body, 400);
    }

    public function mockFinish()
    {
        $header = [
            "Content-Type" => "application/json; charset=UTF-8",
            "Date" => "Mon, 10 Aug 2015 07:14:04 GMT",
            "facebook-api-version" => "v2.3",
        ];

        $body = '{"success":true}';

        return new GraphRawResponse($header, $body, 200);
    }
}


class FacebookResumableUploaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FacebookApp
     */
    private $fbApp;
    /**
     * @var FacebookClient
     */
    private $fbSuccessClient;
    /**
     * @var FacebookClient
     */
    private $fbFailClient;

    public function setUp()
    {
        $this->fbApp = new FacebookApp('app_id', 'app_secret');
        $this->fbSuccessClient = new FacebookClient(new VideoUploadClientHandlerSuccess());
        $this->fbFailClient = new FacebookClient(new VideoUploadClientHandlerFail());
    }

    public function testSuccess()
    {
        $fileUploader = new FacebookResumableUploader($this->fbApp, $this->fbSuccessClient, 'access_token');
        $endpoint = '/113582528973300/videos';
        $filePath = __DIR__ . '/../foo.mp4';
        $chunk = $fileUploader->start($endpoint, $filePath);
        $this->assertInstanceOf('Facebook\FileUpload\FacebookTransferChunk', $chunk);
        $this->assertEquals('162719977392888', $chunk->getUploadSessionId());
        $this->assertEquals('162719974059555', $chunk->getVideoId());

        $chunk = $fileUploader->transfer($endpoint, $chunk);
        $this->assertEquals(2087, $chunk->getStartOffset());

        $this->assertTrue($fileUploader->finish($endpoint, $chunk->getUploadSessionId()));
    }

    public function testFail()
    {
        $fileUploader = new FacebookResumableUploader($this->fbApp, $this->fbFailClient, 'access_token');
        $endpoint = '/113582528973300/videos';
        $filePath = __DIR__ . '/../foo.mp4';

        $catchResException = false;
        try {
            $chunk = $fileUploader->start($endpoint, $filePath);
        } catch (FacebookSDKException $e) {
            $catchResException = true;
        }
        $this->assertTrue($catchResException);

        // get file chunk
        $catchResumableException = false;
        $successUploader = new FacebookResumableUploader($this->fbApp, $this->fbSuccessClient, 'access_token');
        $chunk = $successUploader->start($endpoint, $filePath);
        try {
            $chunk = $fileUploader->transfer($endpoint, $chunk);
        } catch (FacebookSDKException $e) {
            if ($e->getPrevious() instanceof FacebookResumableUploadException) {
                $catchResumableException = true;
                $this->assertInstanceOf('Facebook\FileUpload\FacebookResumeContext',
                    $e->getPrevious()->getResumeContext());
            }
        }
        $this->assertTrue($catchResumableException);
    }

}