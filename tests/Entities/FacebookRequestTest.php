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

use Mockery as m;
use Facebook\Entities\FacebookRequest;
use Facebook\Exceptions\FacebookSDKException;

class FacebookRequestTest extends \PHPUnit_Framework_TestCase
{
  public function testConstructorDefaults()
  {
    $request = new FacebookRequest('/me');

    $this->assertEquals('GET', $request->getMethod());
    $this->assertEquals([], $request->getParameters());
    $this->assertNull($request->getAccessToken());
    $this->assertEquals('', $request->getETag());
  }

  public function testGetEndpoint()
  {
    $request = new FacebookRequest('/endpoint');

    $this->assertEquals('/endpoint', $request->getEndpoint());
  }

  public function testGetMethod()
  {
    $request = new FacebookRequest('/me/feed', 'POST');

    $this->assertEquals('POST', $request->getMethod());
  }

  public function testGetParameters()
  {
    $request = new FacebookRequest('/me', 'GET', ['foo' => 'bar']);

    $this->assertEquals(['foo' => 'bar'], $request->getParameters());
  }

  public function testGetHeaders()
  {
    $request = new FacebookRequest('/me');

    $this->assertTrue(is_array($request->getHeaders()));
  }

  public function testGetAccessToken()
  {
    $fakeApp = m::mock('Facebook\Entities\FacebookApp', ['01234', 'S3cr3t'])->makePartial();
    $fakeAccessToken = m::mock('Facebook\Entities\AccessToken', [$fakeApp, 'AbCd'])->makePartial();
    $request = new FacebookRequest('/me', 'GET', [], $fakeAccessToken, 'etag');

    $this->assertSame($fakeAccessToken, $request->getAccessToken());
  }

  public function testGetETag()
  {
    $request = new FacebookRequest('/me', 'GET', [], null, 'etag');

    $this->assertEquals('etag', $request->getETag());
  }

  /**
   * @expectedException Facebook\Exceptions\FacebookSDKException
   * @expectedExceptionMessage Endpoint have to be a string
   */
  public function testThatConstructorThrowsExceptionOnNonStringEndpoint()
  {
    new FacebookRequest(null);
  }

  /**
   * @expectedException Facebook\Exceptions\FacebookSDKException
   * @expectedExceptionMessage Endpoint have to start with "/"
   */
  public function testThatConstructorThrowsExceptionOnInvalidEndpoint()
  {
    new FacebookRequest('invalid');
  }

  /**
   * @expectedException Facebook\Exceptions\FacebookSDKException
   * @expectedExceptionMessage Invalid method
   */
  public function testThatConstructorThrowsExceptionOnInvalidMethod()
  {
    new FacebookRequest('/me', 'invalid');
  }

  public function testThatConstructorAcceptLowercaseMethod()
  {
    try {
      new FacebookRequest('/me', 'delete');
    } catch(FacebookSDKException $e) {
      $this->fail('Lower case method should be accepted');
    }
  }

  public function testThatConstructorUppercaseMethod()
  {
    $request = new FacebookRequest('/me', 'delete');

    $this->assertEquals('DELETE', $request->getMethod());
  }

  public function testThatConstructorExtractParamatersFromEndpoint()
  {
    $request = new FacebookRequest('/me?fields=name&access_token=token');

    $this->assertEquals('/me', $request->getEndpoint());
    $this->assertEquals(
      ['fields' => 'name', 'access_token' => 'token'],
      $request->getParameters()
    );
  }

  public function testThatConstructorOverridesParametersByEndpointParameters()
  {
    $request = new FacebookRequest('/me?fields=name', 'GET', ['fields' => 'id']);

    $this->assertEquals(['fields' => 'name'], $request->getParameters());
  }

  public function testGetHeadersReturnsDefaults()
  {
    $request = new FacebookRequest('/me');
    $headers = $request->getHeaders();

    $this->assertArrayHasKey('User-Agent', $headers);
    $this->assertArrayHasKey('Accept-Encoding', $headers);
  }

  public function testGetHeadersWithETag()
  {
    $request = new FacebookRequest('/me', 'GET', [], null, 'etag');
    $headers = $request->getHeaders();

    $this->assertArrayHasKey('If-None-Match', $headers);
    $this->assertEquals('etag', $headers['If-None-Match']);
  }
}
