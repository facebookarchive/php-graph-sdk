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
namespace Facebook\Tests\Entities;

use Facebook\Facebook;
use Facebook\Entities\FacebookApp;
use Facebook\Entities\FacebookRequest;

class FacebookRequestTest extends \PHPUnit_Framework_TestCase
{

  public function testAnEmptyRequestEntityCanInstantiate()
  {
    $app = new FacebookApp('123', 'foo_secret');
    $request = new FacebookRequest($app);

    $this->assertInstanceOf('Facebook\Entities\FacebookRequest', $request);
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

  public function testParamsAreNotOverwritten()
  {
    $app = new FacebookApp('123', 'foo_secret');

    $request = new FacebookRequest(
      $app,
      $accessToken = 'foo_token',
      $method = 'GET',
      $endpoint = '/foo',
      $params = [
        'access_token' => 'bar_access_token',
        'appsecret_proof' => 'bar_app_secret',
      ]
    );

    $url = $request->getUrl();

    $expectedParams = 'access_token=bar_access_token&appsecret_proof=bar_app_secret';
    $expectedUrl = '/' . Facebook::DEFAULT_GRAPH_VERSION . '/foo?' . $expectedParams;
    $this->assertEquals($expectedUrl, $url);

    $params = $request->getParams();

    $expectedParams = [
      'access_token' => 'bar_access_token',
      'appsecret_proof' => 'bar_app_secret',
    ];
    $this->assertEquals($expectedParams, $params);
  }

  public function testGracefullyHandlesUrlAppending()
  {
    $params = [];
    $url = 'https://www.foo.com/';
    $processed_url = FacebookRequest::appendParamsToUrl($url, $params);
    $this->assertEquals('https://www.foo.com/', $processed_url);

    $params = [
      'access_token' => 'foo',
    ];
    $url = 'https://www.foo.com/';
    $processed_url = FacebookRequest::appendParamsToUrl($url, $params);
    $this->assertEquals('https://www.foo.com/?access_token=foo', $processed_url);

    $params = [
      'access_token' => 'foo',
      'bar' => 'baz',
    ];
    $url = 'https://www.foo.com/?foo=bar';
    $processed_url = FacebookRequest::appendParamsToUrl($url, $params);
    $this->assertEquals('https://www.foo.com/?access_token=foo&bar=baz&foo=bar', $processed_url);

    $params = [
      'access_token' => 'foo',
    ];
    $url = 'https://www.foo.com/?foo=bar&access_token=bar';
    $processed_url = FacebookRequest::appendParamsToUrl($url, $params);
    $this->assertEquals('https://www.foo.com/?access_token=bar&foo=bar', $processed_url);
  }

  public function testSlashesAreProperlyPrepended()
  {
    $slashTestOne = FacebookRequest::forceSlashPrefix('foo');
    $slashTestTwo = FacebookRequest::forceSlashPrefix('/foo');
    $slashTestThree = FacebookRequest::forceSlashPrefix('foo/bar');
    $slashTestFour = FacebookRequest::forceSlashPrefix('/foo/bar');
    $slashTestFive = FacebookRequest::forceSlashPrefix(null);
    $slashTestSix = FacebookRequest::forceSlashPrefix('');

    $this->assertEquals('/foo', $slashTestOne);
    $this->assertEquals('/foo', $slashTestTwo);
    $this->assertEquals('/foo/bar', $slashTestThree);
    $this->assertEquals('/foo/bar', $slashTestFour);
    $this->assertEquals(null, $slashTestFive);
    $this->assertEquals('', $slashTestSix);
  }

}
