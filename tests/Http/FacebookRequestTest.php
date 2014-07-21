<?php

use Mockery as m;
use Facebook\Facebook;
use Facebook\Http\BaseRequest;
use Facebook\Http\FacebookRequest;

class FacebookRequestTest extends PHPUnit_Framework_TestCase
{

  protected $httpClientMock;
  protected $facebookRequest;

  public function setUp()
  {
    $this->httpClientMock = m::mock('Facebook\Http\Clients\FacebookHttpClientInterface');
    $this->facebookRequest = new FacebookRequest($this->httpClientMock);
  }

  public function tearDown()
  {
    m::close();
  }

  public function testAGetRequestCanBePreparedAndSent()
  {
    Facebook::setDefaultApplication('123', 'foo_app_secret');

    $params = [
      'access_token' => 'foo_access_token',
      'appsecret_proof' => '12f5dcbb7557d24b1d37fd180c45991c5999f325ece0af331c00a85d762f2b95',
    ];
    $url = BaseRequest::BASE_GRAPH_URL
      .'/'.Facebook::getDefaultGraphApiVersion()
      .'/foo?'
      .http_build_query($params, null, '&');

    foreach (Facebook::getDefaultHeaders() as $name => $value) {
      $this->httpClientMock
        ->shouldReceive('addRequestHeader')
        ->once()
        ->with($name, $value)
        ->andReturn(null);
    }
    $this->httpClientMock
      ->shouldReceive('send')
      ->once()
      ->with($url, 'GET', null)
      ->andReturn('foo_data');
    $this->httpClientMock
      ->shouldReceive('getResponseHttpStatusCode')
      ->once()
      ->andReturn(200);
    $this->httpClientMock
      ->shouldReceive('getResponseHeaders')
      ->once()
      ->andReturn(['response_foo' => 'response_bar']);

    $this->facebookRequest->newRequest('foo_access_token');
    $response = $this->facebookRequest->get('/foo');

    $this->assertInstanceOf('Facebook\GraphNodes\GraphObject', $response);
  }

  public function testAPostRequestCanBePreparedAndSent()
  {
    Facebook::setDefaultApplication('123', 'foo_app_secret');

    $params = [
      'foo' => 'bar',
      'access_token' => 'foo_access_token',
      'appsecret_proof' => '12f5dcbb7557d24b1d37fd180c45991c5999f325ece0af331c00a85d762f2b95',
    ];
    $url = BaseRequest::BASE_GRAPH_URL
      .'/'.Facebook::getDefaultGraphApiVersion()
      .'/foo';

    foreach (Facebook::getDefaultHeaders() as $name => $value) {
      $this->httpClientMock
        ->shouldReceive('addRequestHeader')
        ->once()
        ->with($name, $value)
        ->andReturn(null);
    }
    $this->httpClientMock
      ->shouldReceive('send')
      ->once()
      ->with($url, 'POST', $params)
      ->andReturn('foo_data');
    $this->httpClientMock
      ->shouldReceive('getResponseHttpStatusCode')
      ->once()
      ->andReturn(200);
    $this->httpClientMock
      ->shouldReceive('getResponseHeaders')
      ->once()
      ->andReturn(['response_foo' => 'response_bar']);

    $this->facebookRequest->newRequest('foo_access_token');
    $response = $this->facebookRequest->post('/foo', ['foo' => 'bar']);

    $this->assertInstanceOf('Facebook\GraphNodes\GraphObject', $response);
  }

  public function testADeleteRequestCanBePreparedAndSent()
  {
    Facebook::setDefaultApplication('123', 'foo_app_secret');

    $params = [
      'access_token' => 'foo_access_token',
      'appsecret_proof' => '12f5dcbb7557d24b1d37fd180c45991c5999f325ece0af331c00a85d762f2b95',
    ];
    $url = BaseRequest::BASE_GRAPH_URL
      .'/'.Facebook::getDefaultGraphApiVersion()
      .'/foo?'
      .http_build_query($params, null, '&');

    foreach (Facebook::getDefaultHeaders() as $name => $value) {
      $this->httpClientMock
        ->shouldReceive('addRequestHeader')
        ->once()
        ->with($name, $value)
        ->andReturn(null);
    }
    $this->httpClientMock
      ->shouldReceive('send')
      ->once()
      ->with($url, 'DELETE', null)
      ->andReturn('foo_data');
    $this->httpClientMock
      ->shouldReceive('getResponseHttpStatusCode')
      ->once()
      ->andReturn(200);
    $this->httpClientMock
      ->shouldReceive('getResponseHeaders')
      ->once()
      ->andReturn(['response_foo' => 'response_bar']);

    $this->facebookRequest->newRequest('foo_access_token');
    $response = $this->facebookRequest->delete('/foo');

    $this->assertInstanceOf('Facebook\GraphNodes\GraphObject', $response);
  }

}
