<?php

require_once __DIR__ . '/AbstractTestHttpClient.php';

use Mockery as m;
use Facebook\HttpClients\FacebookStreamHttpClient;

class FacebookStreamHttpClientTest extends AbstractTestHttpClient
{

  protected $streamMock;
  protected $streamClient;

  public function setUp()
  {
    $this->streamMock = m::mock('Facebook\HttpClients\FacebookStream');
    $this->streamClient = new FacebookStreamHttpClient($this->streamMock);
  }

  public function tearDown()
  {
    m::close();
    (new FacebookStreamHttpClient()); // Resets the static dependency injection
  }

  public function testCanCompileHeader()
  {
    $this->streamClient->addRequestHeader('X-foo', 'bar');
    $this->streamClient->addRequestHeader('X-bar', 'faz');
    $header = $this->streamClient->compileHeader();
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

            if ($arg['http'] !== array(
                'method' => 'GET',
                'timeout' => 60,
                'ignore_errors' => true,
                'header' => 'X-foo: bar',
              )) {
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

    $this->streamClient->addRequestHeader('X-foo', 'bar');
    $responseBody = $this->streamClient->send('http://foo.com/');

    $this->assertEquals($responseBody, $this->fakeRawBody);
    $this->assertEquals($this->streamClient->getResponseHeaders(), $this->fakeHeadersAsArray);
    $this->assertEquals(200, $this->streamClient->getResponseHttpStatusCode());
  }

  /**
   * @expectedException \Facebook\FacebookSDKException
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
