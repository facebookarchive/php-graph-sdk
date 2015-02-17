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
namespace Facebook\Tests\Http;

use Facebook\Http\RequestBodyMultipart;
use Facebook\FileUpload\FacebookFile;

class RequestBodyMultipartTest extends \PHPUnit_Framework_TestCase
{
    public function testCanProperlyEncodeAnArrayOfParams()
    {
        $message = new RequestBodyMultipart([
            'foo' => 'bar',
            'scawy_vawues' => '@FooBar is a real twitter handle.',
        ], [], 'foo_boundary');
        $body = $message->getBody();

        $expectedBody = "--foo_boundary\r\n";
        $expectedBody .= "Content-Disposition: form-data; name=\"foo\"\r\n\r\nbar\r\n";
        $expectedBody .= "--foo_boundary\r\n";
        $expectedBody .= "Content-Disposition: form-data; name=\"scawy_vawues\"\r\n\r\n@FooBar is a real twitter handle.\r\n";
        $expectedBody .= "--foo_boundary--\r\n";

        $this->assertEquals($expectedBody, $body);
    }

    public function testCanProperlyEncodeFilesAndParams()
    {
        $file = new FacebookFile(__DIR__ . '/../foo.txt');
        $message = new RequestBodyMultipart([
            'foo' => 'bar',
        ], [
            'foo_file' => $file,
        ], 'foo_boundary');
        $body = $message->getBody();

        $expectedBody = "--foo_boundary\r\n";
        $expectedBody .= "Content-Disposition: form-data; name=\"foo\"\r\n\r\nbar\r\n";
        $expectedBody .= "--foo_boundary\r\n";
        $expectedBody .= "Content-Disposition: form-data; name=\"foo_file\"; filename=\"foo.txt\"\r\n";
        $expectedBody .= "Content-Type: text/plain\r\n\r\nThis is a text file used for testing. Let's dance.\r\n";
        $expectedBody .= "--foo_boundary--\r\n";

        $this->assertEquals($expectedBody, $body);
    }
}
