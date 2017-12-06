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
namespace Facebook\Tests;

use Facebook\Application;
use Facebook\Request;
use Facebook\FileUpload\File;
use Facebook\FileUpload\Video;
use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase
{
    public function testAnEmptyRequestEntityCanInstantiate()
    {
        $app = new Application('123', 'foo_secret');
        $request = new Request($app);

        $this->assertInstanceOf(Request::class, $request);
    }

    /**
     * @expectedException \Facebook\Exception\SDKException
     */
    public function testAMissingAccessTokenWillThrow()
    {
        $app = new Application('123', 'foo_secret');
        $request = new Request($app);

        $request->validateAccessToken();
    }

    /**
     * @expectedException \Facebook\Exception\SDKException
     */
    public function testAMissingMethodWillThrow()
    {
        $app = new Application('123', 'foo_secret');
        $request = new Request($app);

        $request->validateMethod();
    }

    /**
     * @expectedException \Facebook\Exception\SDKException
     */
    public function testAnInvalidMethodWillThrow()
    {
        $app = new Application('123', 'foo_secret');
        $request = new Request($app, 'foo_token', 'FOO');

        $request->validateMethod();
    }

    public function testGetHeadersWillAutoAppendETag()
    {
        $app = new Application('123', 'foo_secret');
        $request = new Request($app, null, 'GET', '/foo', [], 'fooETag');

        $headers = $request->getHeaders();

        $expectedHeaders = Request::getDefaultHeaders();
        $expectedHeaders['If-None-Match'] = 'fooETag';

        $this->assertEquals($expectedHeaders, $headers);
    }

    public function testGetParamsWillAutoAppendAccessTokenAndAppSecretProof()
    {
        $app = new Application('123', 'foo_secret');
        $request = new Request($app, 'foo_token', 'POST', '/foo', ['foo' => 'bar']);

        $params = $request->getParams();

        $this->assertEquals([
            'foo' => 'bar',
            'access_token' => 'foo_token',
            'appsecret_proof' => 'df4256903ba4e23636cc142117aa632133d75c642bd2a68955be1443bd14deb9',
        ], $params);
    }

    public function testAnAccessTokenCanBeSetFromTheParams()
    {
        $app = new Application('123', 'foo_secret');
        $request = new Request($app, null, 'POST', '/me', ['access_token' => 'bar_token']);

        $accessToken = $request->getAccessToken();

        $this->assertEquals('bar_token', $accessToken);
    }

    /**
     * @expectedException \Facebook\Exception\SDKException
     */
    public function testAccessTokenConflictsWillThrow()
    {
        $app = new Application('123', 'foo_secret');
        new Request($app, 'foo_token', 'POST', '/me', ['access_token' => 'bar_token']);
    }

    public function testAProperUrlWillBeGenerated()
    {
        $app = new Application('123', 'foo_secret');
        $getRequest = new Request($app, 'foo_token', 'GET', '/foo', ['foo' => 'bar']);

        $getUrl = $getRequest->getUrl();
        $expectedParams = 'foo=bar&access_token=foo_token&appsecret_proof=df4256903ba4e23636cc142117aa632133d75c642bd2a68955be1443bd14deb9';
        $expectedUrl = '/foo?' . $expectedParams;

        $this->assertEquals($expectedUrl, $getUrl);

        $postRequest = new Request($app, 'foo_token', 'POST', '/bar', ['foo' => 'bar'], null, 'v0.0');

        $postUrl = $postRequest->getUrl();
        $expectedUrl = '/v0.0/bar';

        $this->assertEquals($expectedUrl, $postUrl);
    }

    public function testAuthenticationParamsAreStrippedAndReapplied()
    {
        $app = new Application('123', 'foo_secret');

        $request = new Request(
            $app,
            $accessToken = 'foo_token',
            $method = 'GET',
            $endpoint = '/foo',
            $params = [
                'access_token' => 'foo_token',
                'appsecret_proof' => 'bar_app_secret',
                'bar' => 'baz',
            ]
        );

        $url = $request->getUrl();

        $expectedParams = 'bar=baz&access_token=foo_token&appsecret_proof=df4256903ba4e23636cc142117aa632133d75c642bd2a68955be1443bd14deb9';
        $expectedUrl = '/foo?' . $expectedParams;
        $this->assertEquals($expectedUrl, $url);

        $params = $request->getParams();

        $expectedParams = [
            'access_token' => 'foo_token',
            'appsecret_proof' => 'df4256903ba4e23636cc142117aa632133d75c642bd2a68955be1443bd14deb9',
            'bar' => 'baz',
        ];
        $this->assertEquals($expectedParams, $params);
    }

    public function testAFileCanBeAddedToParams()
    {
        $myFile = new File(__DIR__ . '/foo.txt');
        $params = [
            'name' => 'Foo Bar',
            'source' => $myFile,
        ];
        $app = new Application('123', 'foo_secret');
        $request = new Request($app, 'foo_token', 'POST', '/foo/photos', $params);

        $actualParams = $request->getParams();

        $this->assertTrue($request->containsFileUploads());
        $this->assertFalse($request->containsVideoUploads());
        $this->assertArrayNotHasKey('source', $actualParams);
        $this->assertEquals('Foo Bar', $actualParams['name']);
    }

    public function testAVideoCanBeAddedToParams()
    {
        $myFile = new Video(__DIR__ . '/foo.txt');
        $params = [
            'name' => 'Foo Bar',
            'source' => $myFile,
        ];
        $app = new Application('123', 'foo_secret');
        $request = new Request($app, 'foo_token', 'POST', '/foo/videos', $params);

        $actualParams = $request->getParams();

        $this->assertTrue($request->containsFileUploads());
        $this->assertTrue($request->containsVideoUploads());
        $this->assertArrayNotHasKey('source', $actualParams);
        $this->assertEquals('Foo Bar', $actualParams['name']);
    }
}
