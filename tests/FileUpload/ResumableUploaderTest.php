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

namespace Facebook\Tests\FileUpload;

use Facebook\FileUpload\File;
use Facebook\Application;
use Facebook\Client;
use Facebook\FileUpload\ResumableUploader;
use Facebook\FileUpload\TransferChunk;
use Facebook\Tests\Fixtures\FakeGraphApiForResumableUpload;
use PHPUnit\Framework\TestCase;

class ResumableUploaderTest extends TestCase
{
    /**
     * @var Application
     */
    private $fbApp;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var FakeGraphApiForResumableUpload
     */
    private $graphApi;

    /**
     * @var File
     */
    private $file;

    protected function setUp()
    {
        $this->fbApp = new Application('app_id', 'app_secret');
        $this->graphApi = new FakeGraphApiForResumableUpload();
        $this->client = new Client($this->graphApi);
        $this->file = new File(__DIR__.'/../foo.txt');
    }

    public function testResumableUploadCanStartTransferAndFinish()
    {
        $uploader = new ResumableUploader($this->fbApp, $this->client, 'access_token', 'v2.4');
        $endpoint = '/me/videos';
        $chunk = $uploader->start($endpoint, $this->file);
        $this->assertInstanceOf(TransferChunk::class, $chunk);
        $this->assertEquals('42', $chunk->getUploadSessionId());
        $this->assertEquals('1337', $chunk->getVideoId());

        $newChunk = $uploader->transfer($endpoint, $chunk);
        $this->assertEquals(20, $newChunk->getStartOffset());
        $this->assertNotSame($newChunk, $chunk);

        $finalResponse = $uploader->finish($endpoint, $chunk->getUploadSessionId(), []);
        $this->assertTrue($finalResponse);
    }

    /**
     * @expectedException \Facebook\Exception\ResponseException
     */
    public function testStartWillLetErrorResponsesThrow()
    {
        $this->graphApi->failOnStart();
        $uploader = new ResumableUploader($this->fbApp, $this->client, 'access_token', 'v2.4');

        $uploader->start('/me/videos', $this->file);
    }

    public function testFailedResumableTransferWillNotThrowAndReturnSameChunk()
    {
        $this->graphApi->failOnTransfer();
        $uploader = new ResumableUploader($this->fbApp, $this->client, 'access_token', 'v2.4');

        $chunk = new TransferChunk($this->file, '1', '2', '3', '4');
        $newChunk = $uploader->transfer('/me/videos', $chunk);
        $this->assertSame($newChunk, $chunk);
    }
}
