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

use Facebook\Exceptions\FacebookSDKException;
use Facebook\FileUpload\File;
use PHPUnit\Framework\TestCase;

/**
 * Class FileTest
 */
class FileTest extends TestCase
{
    protected string $testFile = '';

    protected function setUp(): void
    {
        $this->testFile = __DIR__ . '/../foo.txt';
    }

    public function testCanOpenAndReadAndCloseAFile()
    {
        $file = new File($this->testFile);
        $fileContents = $file->getContents();

        static::assertEquals('This is a text file used for testing. Let\'s dance.', $fileContents);
    }

    public function testPartialFilesCanBeCreated()
    {
        $file = new File($this->testFile, 14, 5);
        $fileContents = $file->getContents();

        static::assertEquals('is a text file', $fileContents);
    }

    public function testTryingToOpenAFileThatDoesntExistsThrows()
    {
        $this->expectException(FacebookSDKException::class);
        new File('does_not_exist.file');
    }
}
