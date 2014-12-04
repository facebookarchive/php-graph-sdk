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

  public function testCanSendNormalRequest()
  {
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

    $options = [
      'headers' => ['X-foo' => 'bar'],
      'body' => 'foo_body',
      'timeout' => 123,
      'connect_timeout' => 10,
      'verify' => true,
    ];

    $requestMock = m::mock('GuzzleHttp\Message\RequestInterface');
    $this->guzzleMock
      ->shouldReceive('createRequest')
      ->once()
      ->with('GET', 'http://foo.com/', $options)
      ->andReturn($requestMock);
    $this->guzzleMock
      ->shouldReceive('send')
      ->once()
      ->with($requestMock)
      ->andReturn($responseMock);

    $response = $this->guzzleClient->send('http://foo.com/', 'GET', 'foo_body', ['X-foo' => 'bar'], 123);

    $this->assertInstanceOf('Facebook\Http\GraphRawResponse', $response);
    $this->assertEquals($this->fakeRawBody, $response->getBody());
    $this->assertEquals($this->fakeHeadersAsArray, $response->getHeaders());
    $this->assertEquals(200, $response->getHttpResponseCode());
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
                          m::mock('GuzzleHttp\Ring\Exception\RingException'),
                        ]);

    $options = [
      'headers' => [],
      'body' => 'foo_body',
      'timeout' => 60,
      'connect_timeout' => 10,
      'verify' => true,
    ];

    $this->guzzleMock
      ->shouldReceive('createRequest')
      ->once()
      ->with('GET', 'http://foo.com/', $options)
      ->andReturn($requestMock);
    $this->guzzleMock
      ->shouldReceive('send')
      ->once()
      ->with($requestMock)
      ->andThrow($exceptionMock);

    $this->guzzleClient->send('http://foo.com/', 'GET', 'foo_body', [], 60);
  }

}
