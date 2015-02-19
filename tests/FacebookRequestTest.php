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

use Facebook\Facebook;
use Facebook\FacebookApp;
use Facebook\FacebookRequest;
use Facebook\FileUpload\FacebookFile;
use Facebook\FileUpload\FacebookVideo;

class FacebookRequestTest extends \PHPUnit_Framework_TestCase
{
    public function testAnEmptyRequestEntityCanInstantiate()
    {
        $app = new FacebookApp('123', 'foo_secret');
        $request = new FacebookRequest($app);

        $this->assertInstanceOf('Facebook\FacebookRequest', $request);
    }

    /**
     * @expectedException \Facebook\Exceptions\FacebookSDKException
     */
    public function testAMissingAccessTokenWillThrow()
    {
        $app = new FacebookApp('123', 'foo_secret');
        $request = new FacebookRequest($app);

        $request->validateAccessToken();
    }

    /**
     * @expectedException \Facebook\Exceptions\FacebookSDKException
     */
    public function testAMissingMethodWillThrow()
    {
        $app = new FacebookApp('123', 'foo_secret');
        $request = new FacebookRequest($app);

        $request->validateMethod();
    }

    /**
     * @expectedException \Facebook\Exceptions\FacebookSDKException
     */
    public function testAnInvalidMethodWillThrow()
    {
        $app = new FacebookApp('123', 'foo_secret');
        $request = new FacebookRequest($app, 'foo_token', 'FOO');

        $request->validateMethod();
    }

    public function testGetHeadersWillAutoAppendETag()
    {
        $app = new FacebookApp('123', 'foo_secret');
        $request = new FacebookRequest($app, null, 'GET', '/foo', [], 'fooETag');

        $headers = $request->getHeaders();

        $expectedHeaders = FacebookRequest::getDefaultHeaders();
        $expectedHeaders['If-None-Match'] = 'fooETag';

        $this->assertEquals($expectedHeaders, $headers);
    }

    public function testGetParamsWillAutoAppendAccessTokenAndAppSecretProof()
    {
        $app = new FacebookApp('123', 'foo_secret');
        $request = new FacebookRequest($app, 'foo_token', 'POST', '/foo', ['foo' => 'bar']);

        $params = $request->getParams();

        $this->assertEquals([
            'foo' => 'bar',
            'access_token' => 'foo_token',
            'appsecret_proof' => 'df4256903ba4e23636cc142117aa632133d75c642bd2a68955be1443bd14deb9',
        ], $params);
    }

    public function testAnAccessTokenCanBeSetFromTheParams()
    {
        $app = new FacebookApp('123', 'foo_secret');
        $request = new FacebookRequest($app, null, 'POST', '/me', ['access_token' => 'bar_token']);

        $accessToken = $request->getAccessToken();

        $this->assertEquals('bar_token', $accessToken);
    }

    /**
     * @expectedException \Facebook\Exceptions\FacebookSDKException
     */
    public function testAccessTokenConflictsWillThrow()
    {
        $app = new FacebookApp('123', 'foo_secret');
        new FacebookRequest($app, 'foo_token', 'POST', '/me', ['access_token' => 'bar_token']);
    }

    public function testAProperUrlWillBeGenerated()
    {
        $app = new FacebookApp('123', 'foo_secret');
        $getRequest = new FacebookRequest($app, 'foo_token', 'GET', '/foo', ['foo' => 'bar']);

        $getUrl = $getRequest->getUrl();
        $expectedParams = 'foo=bar&access_token=foo_token&appsecret_proof=df4256903ba4e23636cc142117aa632133d75c642bd2a68955be1443bd14deb9';
        $expectedUrl = '/' . Facebook::DEFAULT_GRAPH_VERSION . '/foo?' . $expectedParams;

        $this->assertEquals($expectedUrl, $getUrl);

        $postRequest = new FacebookRequest($app, 'foo_token', 'POST', '/bar', ['foo' => 'bar']);

        $postUrl = $postRequest->getUrl();
        $expectedUrl = '/' . Facebook::DEFAULT_GRAPH_VERSION . '/bar';

        $this->assertEquals($expectedUrl, $postUrl);
    }

    public function testAuthenticationParamsAreStrippedAndReapplied()
    {
        $app = new FacebookApp('123', 'foo_secret');

        $request = new FacebookRequest(
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
        $expectedUrl = '/' . Facebook::DEFAULT_GRAPH_VERSION . '/foo?' . $expectedParams;
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
        $myFile = new FacebookFile(__DIR__ . '/foo.txt');
        $params = [
            'name' => 'Foo Bar',
            'source' => $myFile,
        ];
        $app = new FacebookApp('123', 'foo_secret');
        $request = new FacebookRequest($app, 'foo_token', 'POST', '/foo/photos', $params);

        $actualParams = $request->getParams();

        $this->assertTrue($request->containsFileUploads());
        $this->assertFalse($request->containsVideoUploads());
        $this->assertTrue(!isset($actualParams['source']));
        $this->assertEquals('Foo Bar', $actualParams['name']);
    }

    public function testAVideoCanBeAddedToParams()
    {
        $myFile = new FacebookVideo(__DIR__ . '/foo.txt');
        $params = [
            'name' => 'Foo Bar',
            'source' => $myFile,
        ];
        $app = new FacebookApp('123', 'foo_secret');
        $request = new FacebookRequest($app, 'foo_token', 'POST', '/foo/videos', $params);

        $actualParams = $request->getParams();

        $this->assertTrue($request->containsFileUploads());
        $this->assertTrue($request->containsVideoUploads());
        $this->assertTrue(!isset($actualParams['source']));
        $this->assertEquals('Foo Bar', $actualParams['name']);
    }
}
