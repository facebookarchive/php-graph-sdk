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

namespace Facebook\Tests\FileUpload;

use Facebook\Exceptions\FacebookResponseException;
use Facebook\FileUpload\File;
use Facebook\Application;
use Facebook\Client;
use Facebook\FileUpload\ResumableUploader;
use Facebook\FileUpload\TransferChunk;
use Facebook\Tests\Fixtures\FakeGraphApiForResumableUpload;
use PHPUnit\Framework\TestCase;

/**
 * Class ResumableUploaderTest
 */
class ResumableUploaderTest extends TestCase
{
    /**
     * @var Application
     */
    private Application $fbApp;

    /**
     * @var Client
     */
    private Client $client;

    /**
     * @var FakeGraphApiForResumableUpload
     */
    private FakeGraphApiForResumableUpload $graphApi;

    /**
     * @var File
     */
    private File $file;

    protected function setUp(): void
    {
        $this->fbApp = new Application('app_id', 'app_secret');
        $this->graphApi = new FakeGraphApiForResumableUpload();
        $this->client = new Client($this->graphApi);
        $this->file = new File(__DIR__ . '/../foo.txt');
    }

    public function testResumableUploadCanStartTransferAndFinish()
    {
        $uploader = new ResumableUploader($this->fbApp, $this->client, 'access_token', 'v2.4');
        $endpoint = '/me/videos';
        $chunk = $uploader->start($endpoint, $this->file);
        static::assertInstanceOf(TransferChunk::class, $chunk);
        static::assertEquals('42', $chunk->getUploadSessionId());
        static::assertEquals('1337', $chunk->getVideoId());

        $newChunk = $uploader->transfer($endpoint, $chunk);
        static::assertEquals(20, $newChunk->getStartOffset());
        static::assertNotSame($newChunk, $chunk);

        $finalResponse = $uploader->finish($endpoint, $chunk->getUploadSessionId(), []);
        static::assertTrue($finalResponse);
    }

    public function testStartWillLetErrorResponsesThrow()
    {
        $this->expectException(FacebookResponseException::class);
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
        static::assertSame($newChunk, $chunk);
    }

    public function testFailedResumableTransferWillNotThrowAndReturnNewChunk()
    {
        $this->graphApi->failOnTransferAndUploadNewChunk();
        $uploader = new ResumableUploader($this->fbApp, $this->client, 'access_token', 'v2.4');

        $chunk = new TransferChunk($this->file, '1', '2', '3', '4');
        $newChunk = $uploader->transfer('/me/videos', $chunk);
        static::assertEquals(40, $newChunk->getStartOffset());
        static::assertEquals(50, $newChunk->getEndOffset());
    }
}
