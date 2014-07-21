<?php

use Mockery as m;
use Facebook\Facebook;
use Facebook\Http\BaseRequest;

class FooRequest extends BaseRequest
{
  protected function prepareRequest() { return 'foo'; }

  public function makeResponseEntity($httpStatusCode, array $headers, $body, $accessToken = null)
  {
    $this->lastResponse = m::mock('Facebook\Entities\Response');
    $this->lastResponse
      ->shouldReceive('isError')
      ->once()
      ->andReturn(false);
    return [$httpStatusCode, $headers, $body, $accessToken];
  }
}

class BaseRequestTest extends PHPUnit_Framework_TestCase
{

  protected $httpClientMock;
  protected $fooRequest;

  public function setUp()
  {
    $this->httpClientMock = m::mock('Facebook\Http\Clients\FacebookHttpClientInterface');
    $this->fooRequest = new FooRequest($this->httpClientMock);
  }

  public function tearDown()
  {
    m::close();
  }

  public function testANewRequestEntityCanBeCreated()
  {
    $this->fooRequest->newRequest('foo_access_token');

    $request = $this->fooRequest->getCurrentRequest();

    $this->assertInstanceOf('Facebook\Entities\Request', $request);
  }

  public function testNewRequestEntitiesCanBeCreatedInSeries()
  {
    $this->fooRequest->newRequest('foo_access_token');
    $request = $this->fooRequest->getCurrentRequest();
    $this->assertEquals('foo_access_token', $request->getAccessToken());

    $this->fooRequest->newRequest('bar_access_token');
    $request = $this->fooRequest->getCurrentRequest();
    $this->assertEquals('bar_access_token', $request->getAccessToken());
  }

  public function testARequestCanBeSentToGraph()
  {
    $this->httpClientMock
      ->shouldReceive('addRequestHeader')
      ->once()
      ->with('baz', 'faz')
      ->andReturn(null);
    $this->httpClientMock
      ->shouldReceive('send')
      ->once()
      ->with(BaseRequest::BASE_GRAPH_URL . '/foo/bar', 'POST', ['foo' => 'bar'])
      ->andReturn('foo_data');
    $this->httpClientMock
      ->shouldReceive('getResponseHttpStatusCode')
      ->once()
      ->andReturn(200);
    $this->httpClientMock
      ->shouldReceive('getResponseHeaders')
      ->once()
      ->andReturn(['response_foo' => 'response_bar']);

    $response = $this->fooRequest->sendRequest(
     'POST',
     '/foo/bar',
     ['foo' => 'bar'],
     ['baz' => 'faz']
    );

    $this->assertEquals([200, ['response_foo' => 'response_bar'], 'foo_data', null], $response);
  }

  public function testAGetRequestCanBeFinalized()
  {
    $this->fooRequest->newRequest('foo_access_token');
    $this->fooRequest->get('/foo');
    $request = $this->fooRequest->getCurrentRequest();

    $this->assertEquals('foo_access_token', $request->getAccessToken());
    $this->assertEquals('GET', $request->getMethod());
    $this->assertEquals('/foo', $request->getEndpoint());
  }

  public function testAPostRequestCanBeFinalized()
  {
    $this->fooRequest->newRequest('foo_access_token');
    $this->fooRequest->post('/foo', ['foo' => 'bar']);
    $request = $this->fooRequest->getCurrentRequest();

    $this->assertEquals('foo_access_token', $request->getAccessToken());
    $this->assertEquals('POST', $request->getMethod());
    $this->assertEquals('/foo', $request->getEndpoint());

    $params = $request->getParams();
    unset($params['access_token'], $params['appsecret_proof']);
    $this->assertEquals(['foo' => 'bar'], $params);
  }

  public function testADeleteRequestCanBeFinalized()
  {
    $this->fooRequest->newRequest('foo_access_token');
    $this->fooRequest->delete('/foo');
    $request = $this->fooRequest->getCurrentRequest();

    $this->assertEquals('foo_access_token', $request->getAccessToken());
    $this->assertEquals('DELETE', $request->getMethod());
    $this->assertEquals('/foo', $request->getEndpoint());
  }

  public function testARequestWillUseDefaultAccessTokenIfNoneSpecified()
  {
    Facebook::setDefaultAccessToken('bar_access_token');

    $this->fooRequest->newRequest();
    $request = $this->fooRequest->getCurrentRequest();

    $this->assertEquals('bar_access_token', $request->getAccessToken());
  }

  public function testProperUrlIsReturnedInBetaMode()
  {
    $url = FooRequest::getBaseGraphUrl();
    $this->assertEquals(FooRequest::BASE_GRAPH_URL, $url);

    FooRequest::enableBetaMode();

    $url = FooRequest::getBaseGraphUrl();
    $this->assertEquals(FooRequest::BASE_GRAPH_URL_BETA, $url);

    // Reset
    FooRequest::enableBetaMode(false);
  }

}
