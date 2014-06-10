<?php

require_once __DIR__ . '/AbstractTestHttpClient.php';

use Mockery as m;
use Facebook\HttpClients\FacebookGuzzleHttpClient;

class FacebookGuzzleHttpClientTest extends AbstractTestHttpClient
{

  protected $guzzleMock;
  protected $guzzleClient;

  public function setUp()
  {
    $this->guzzleMock = m::mock('GuzzleHttp\Client');
    $this->guzzleClient = new FacebookGuzzleHttpClient($this->guzzleMock);
  }

  public function tearDown()
  {
    m::close();
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
      ->with('GET', 'http://foo.com/', array())
      ->andReturn($requestMock);
    $this->guzzleMock
      ->shouldReceive('send')
      ->once()
      ->with($requestMock)
      ->andReturn($responseMock);

    $this->guzzleClient->addRequestHeader('X-foo', 'bar');
    $responseBody = $this->guzzleClient->send('http://foo.com/');

    $this->assertEquals($responseBody, $this->fakeRawBody);
    $this->assertEquals($this->guzzleClient->getResponseHeaders(), $this->fakeHeadersAsArray);
    $this->assertEquals(200, $this->guzzleClient->getResponseHttpStatusCode());
  }

  /**
   * @expectedException \Facebook\FacebookSDKException
   */
  public function testThrowsExceptionOnClientError()
  {
    $requestMock = m::mock('GuzzleHttp\Message\RequestInterface');
    $exceptionMock = m::mock(
                      'GuzzleHttp\Exception\RequestException',
                        array(
                          'Foo Error',
                          $requestMock,
                          null,
                          m::mock('GuzzleHttp\Exception\AdapterException'),
                        ));

    $this->guzzleMock
      ->shouldReceive('createRequest')
      ->once()
      ->with('GET', 'http://foo.com/', array())
      ->andReturn($requestMock);
    $this->guzzleMock
      ->shouldReceive('send')
      ->once()
      ->with($requestMock)
      ->andThrow($exceptionMock);

    $this->guzzleClient->send('http://foo.com/');
  }

}
