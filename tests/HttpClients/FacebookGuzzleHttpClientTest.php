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
use Facebook\HttpClients\FacebookGuzzleHttpClient;

class FacebookGuzzleHttpClientTest extends AbstractTestHttpClient
{

  /**
   * @var \GuzzleHttp\Client
   */
  protected $guzzleMock;

  /**
   * @var FacebookGuzzleHttpClient
   */
  protected $guzzleClient;

  public function setUp()
  {
    $this->guzzleMock = m::mock('GuzzleHttp\Client');
    $this->guzzleClient = new FacebookGuzzleHttpClient($this->guzzleMock);
  }

  public function tearDown()
  {
    (new FacebookGuzzleHttpClient()); // Resets the static dependency injection
  }

  public function testCanSendNormalRequest()
  {
    $requestMock = m::mock('GuzzleHttp\Message\RequestInterface');
    $requestMock
      ->shouldReceive('setHeader')
      ->once()
      ->with('X-foo', 'bar')
      ->andReturn(null);

    $responseMock = m::mock('GuzzleHttp\Message\ResponseInterface');
    $responseMock
      ->shouldReceive('getStatusCode')
      ->once()
      ->andReturn(200);
    $responseMock
      ->shouldReceive('getHeaders')
      ->once()
      ->andReturn($this->fakeHeadersAsArray);
    $responseMock
      ->shouldReceive('getBody')
      ->once()
      ->andReturn($this->fakeRawBody);

    $this->guzzleMock
      ->shouldReceive('createRequest')
      ->once()
      ->with('GET', 'http://foo.com/', [])
      ->andReturn($requestMock);
    $this->guzzleMock
      ->shouldReceive('send')
      ->once()
      ->with($requestMock)
      ->andReturn($responseMock);

    $responseBody = $this->guzzleClient->send('http://foo.com/', 'GET', [], ['X-foo' => 'bar']);

    $this->assertEquals($responseBody, $this->fakeRawBody);
    $this->assertEquals($this->guzzleClient->getResponseHeaders(), $this->fakeHeadersAsArray);
    $this->assertEquals(200, $this->guzzleClient->getResponseHttpStatusCode());
  }

  /**
   * @expectedException \Facebook\Exceptions\FacebookSDKException
   */
  public function testThrowsExceptionOnClientError()
  {
    $requestMock = m::mock('GuzzleHttp\Message\RequestInterface');
    $exceptionMock = m::mock(
                      'GuzzleHttp\Exception\RequestException',
                        [
                          'Foo Error',
                          $requestMock,
                          null,
                          m::mock('GuzzleHttp\Exception\AdapterException'),
                        ]);

    $this->guzzleMock
      ->shouldReceive('createRequest')
      ->once()
      ->with('GET', 'http://foo.com/', [])
      ->andReturn($requestMock);
    $this->guzzleMock
      ->shouldReceive('send')
      ->once()
      ->with($requestMock)
      ->andThrow($exceptionMock);

    $this->guzzleClient->send('http://foo.com/');
  }

}
