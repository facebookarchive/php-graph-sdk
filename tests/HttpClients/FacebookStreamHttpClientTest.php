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
namespace Facebook\Tests\HttpClients;

use Mockery as m;
use Facebook\HttpClients\FacebookStreamHttpClient;

class FacebookStreamHttpClientTest extends AbstractTestHttpClient
{

  /**
   * @var \Facebook\HttpClients\FacebookStream
   */
  protected $streamMock;

  /**
   * @var FacebookStreamHttpClient
   */
  protected $streamClient;

  public function setUp()
  {
    $this->streamMock = m::mock('Facebook\HttpClients\FacebookStream');
    $this->streamClient = new FacebookStreamHttpClient($this->streamMock);
  }

  public function tearDown()
  {
    (new FacebookStreamHttpClient()); // Resets the static dependency injection
  }

  public function testCanCompileHeader()
  {
    $headers = [
      'X-foo' => 'bar',
      'X-bar' => 'faz',
    ];
    $header = $this->streamClient->compileHeader($headers);
    $this->assertEquals("X-foo: bar\r\nX-bar: faz", $header);
  }

  public function testCanFormatHeadersToArray()
  {
    $raw_header_array = explode("\n", trim($this->fakeRawHeader));
    $header_array = FacebookStreamHttpClient::formatHeadersToArray($raw_header_array);
    $this->assertEquals($this->fakeHeadersAsArray, $header_array);
  }

  public function testCanGetHttpStatusCodeFromResponseHeader()
  {
    $http_code = FacebookStreamHttpClient::getStatusCodeFromHeader('HTTP/1.1 123 Foo Response');
    $this->assertEquals('123', $http_code);
  }

  public function testCanSendNormalRequest()
  {
    $this->streamMock
      ->shouldReceive('streamContextCreate')
      ->once()
      ->with(\Mockery::on(function($arg) {
            if (!isset($arg['http']) || !isset($arg['ssl'])) {
              return false;
            }

            if ($arg['http'] !== [
                'method' => 'GET',
                'timeout' => 60,
                'ignore_errors' => true,
                'header' => 'X-foo: bar',
              ]) {
              return false;
            }

            if ($arg['ssl']['verify_peer'] !== true) {
              return false;
            }

            if (false === preg_match('/.fb_ca_chain_bundle\.crt$/', $arg['ssl']['cafile'])) {
              return false;
            }

            return true;
          }))
      ->andReturn(null);
    $this->streamMock
      ->shouldReceive('getResponseHeaders')
      ->once()
      ->andReturn(explode("\n", trim($this->fakeRawHeader)));
    $this->streamMock
      ->shouldReceive('fileGetContents')
      ->once()
      ->with('http://foo.com/')
      ->andReturn($this->fakeRawBody);

    $responseBody = $this->streamClient->send('http://foo.com/', 'GET', [], ['X-foo' => 'bar']);

    $this->assertEquals($responseBody, $this->fakeRawBody);
    $this->assertEquals($this->streamClient->getResponseHeaders(), $this->fakeHeadersAsArray);
    $this->assertEquals(200, $this->streamClient->getResponseHttpStatusCode());
  }

  /**
   * @expectedException \Facebook\Exceptions\FacebookSDKException
   */
  public function testThrowsExceptionOnClientError()
  {
    $this->streamMock
      ->shouldReceive('streamContextCreate')
      ->once()
      ->andReturn(null);
    $this->streamMock
      ->shouldReceive('getResponseHeaders')
      ->once()
      ->andReturn(null);
    $this->streamMock
      ->shouldReceive('fileGetContents')
      ->once()
      ->with('http://foo.com/')
      ->andReturn(false);

    $this->streamClient->send('http://foo.com/');
  }

}
