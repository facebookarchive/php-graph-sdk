<?php

use Mockery as m;
use Facebook\Facebook;
use Facebook\Http\BaseRequest;
use Facebook\Http\FacebookBatchRequest;

class FacebookBatchRequestTest extends PHPUnit_Framework_TestCase
{

  protected $httpClientMock;
  protected $facebookBatchRequest;

  public function setUp()
  {
    Facebook::setDefaultApplication('123', 'foo_app_secret');
    Facebook::setDefaultAccessToken(null);

    $this->httpClientMock = m::mock('Facebook\Http\Clients\FacebookHttpClientInterface');
    $this->facebookBatchRequest = new FacebookBatchRequest($this->httpClientMock);
  }

  public function tearDown()
  {
    m::close();
  }

  public function testNewBatchRequestEntitiesCanBeInstantiated()
  {
    $batchRequest = $this->facebookBatchRequest->newRequest();
    $batchRequestEntity = $batchRequest->getCurrentRequest();

    $this->assertInstanceOf('Facebook\Http\FacebookBatchRequest', $batchRequest);
    $this->assertInstanceOf('Facebook\Entities\BatchRequest', $batchRequestEntity);
  }

  /**
   * @expectedException \Facebook\Exceptions\FacebookSDKException
   */
  public function testAMissingAccessTokenWithThrow()
  {
    $batchRequest = $this->facebookBatchRequest->newRequest();
    $batchRequest->validateBatchAccessToken();
  }

  public function testAnAccessTokenWillFallbackToDefault()
  {
    Facebook::setDefaultAccessToken('foo_token');

    $batchRequest = $this->facebookBatchRequest->newRequest();
    $accessToken = $batchRequest->getBatchRequestAccessToken();

    $this->assertEquals('foo_token', $accessToken);
  }

  public function testAccessTokensSetOnEachIndividualRequestEntityCanBeValid()
  {
    $this->facebookBatchRequest->newRequest('foo_one');
    $this->facebookBatchRequest->newRequest('foo_two');
    $this->facebookBatchRequest->newRequest('foo_three');

    $allRequestHaveAnAccessToken = $this->facebookBatchRequest->allRequestHaveAnAccessToken();

    $this->assertTrue($allRequestHaveAnAccessToken, 'Expected access token check to return true.');
  }

  public function testAccessTokensSetOnEachIndividualRequestEntityCanBeInvalid()
  {
    $this->facebookBatchRequest->newRequest('foo_one');
    $this->facebookBatchRequest->newRequest();
    $this->facebookBatchRequest->newRequest('foo_three');

    $allRequestHaveAnAccessToken = $this->facebookBatchRequest->allRequestHaveAnAccessToken();

    $this->assertFalse($allRequestHaveAnAccessToken, 'Expected access token check to return false.');
  }

  /**
   * @expectedException \Facebook\Exceptions\FacebookSDKException
   */
  public function testAZeroRequestCountWithThrow()
  {
    $this->facebookBatchRequest->validateBatchRequestCount();
  }

  /**
   * @expectedException \Facebook\Exceptions\FacebookSDKException
   */
  public function testMoreThanFiftyRequestsWillThrow()
  {
    for ($i=0; $i<=50; $i++) {
      $this->facebookBatchRequest->newRequest($i);
    }
    $this->facebookBatchRequest->validateBatchRequestCount();
  }

  public function testLessThanFiftyRequestsWillNotThrow()
  {
    for ($i=0; $i<50; $i++) {
      $this->facebookBatchRequest->newRequest($i);
    }
    $this->facebookBatchRequest->validateBatchRequestCount();
  }

  public function testBatchRequestEntitiesProperlyGetConvertedToAnArrayForJsonEncodingForGetMethod()
  {
    $this->facebookBatchRequest
      ->newRequest('foo_token')
      ->withRequestName('foo_name')
      ->withFields(['foo' => 'bar'])
      ->get('/foo');

    $batchRequestEntity = $this->facebookBatchRequest->getCurrentRequest();
    $batchRequestArray = FacebookBatchRequest::requestEntityToBatchArray($batchRequestEntity);

    $expectedHeaders = [];
    foreach (Facebook::getDefaultHeaders() as $name => $value) {
      $expectedHeaders[] = $name.': '.$value;
    }

    $expectedArray = [
      'headers' => $expectedHeaders,
      'method' => 'GET',
      'relative_url' => '/'.Facebook::getDefaultGraphApiVersion().'/foo?foo=bar&access_token=foo_token&appsecret_proof=857d5f035a894f16b4180f19966e055cdeab92d4d53017b13dccd6d43b6497af',
      'name' => 'foo_name',
    ];

    $this->assertEquals($expectedArray, $batchRequestArray);
  }

  public function testBatchRequestEntitiesProperlyGetConvertedToAnArrayForJsonEncodingForPostMethod()
  {
    Facebook::setDefaultApplication('123', 'foo_app_secret');

    $this->facebookBatchRequest
      ->newRequest('foo_token')
      ->withAccessToken('bar_token')
      ->withRequestName('foo_name')
      ->withFields(['foo' => 'bar'])
      ->post('/foo');

    $batchRequestEntity = $this->facebookBatchRequest->getCurrentRequest();
    $batchRequestArray = FacebookBatchRequest::requestEntityToBatchArray($batchRequestEntity);

    $expectedHeaders = [];
    foreach (Facebook::getDefaultHeaders() as $name => $value) {
      $expectedHeaders[] = $name.': '.$value;
    }

    $expectedArray = [
      'headers' => $expectedHeaders,
      'method' => 'POST',
      'relative_url' => '/'.Facebook::getDefaultGraphApiVersion().'/foo',
      'body' => 'foo=bar&access_token=bar_token&appsecret_proof=354255b4bf1a911fdf24c7dd90f5e542865f5c22563ad029cfa7a1848c7b3a39',
      'name' => 'foo_name',
    ];

    $this->assertEquals($expectedArray, $batchRequestArray);
  }

  public function testBatchRequestEntitiesProperlyGetConvertedToAnArrayForJsonEncodingForDeleteMethod()
  {
    $this->facebookBatchRequest
      ->newRequest('foo_token')
      ->withRequestName('foo_name')
      ->withFields(['foo' => 'bar'])
      ->delete('/foo');

    $batchRequestEntity = $this->facebookBatchRequest->getCurrentRequest();
    $batchRequestArray = FacebookBatchRequest::requestEntityToBatchArray($batchRequestEntity);

    $expectedHeaders = [];
    foreach (Facebook::getDefaultHeaders() as $name => $value) {
      $expectedHeaders[] = $name.': '.$value;
    }

    $expectedArray = [
      'headers' => $expectedHeaders,
      'method' => 'DELETE',
      'relative_url' => '/'.Facebook::getDefaultGraphApiVersion().'/foo?foo=bar&access_token=foo_token&appsecret_proof=857d5f035a894f16b4180f19966e055cdeab92d4d53017b13dccd6d43b6497af',
      'name' => 'foo_name',
    ];

    $this->assertEquals($expectedArray, $batchRequestArray);
  }

  public function testPostParamsCanBeInjectedAsAnArgument()
  {
    $this->facebookBatchRequest
      ->newRequest('foo_token')
      ->post('/foo', ['foo' => 'bar']);

    $batchRequestEntity = $this->facebookBatchRequest->getCurrentRequest();
    $batchRequestArray = FacebookBatchRequest::requestEntityToBatchArray($batchRequestEntity);

    $expectedHeaders = [];
    foreach (Facebook::getDefaultHeaders() as $name => $value) {
      $expectedHeaders[] = $name.': '.$value;
    }

    $expectedArray = [
      'headers' => $expectedHeaders,
      'method' => 'POST',
      'relative_url' => '/'.Facebook::getDefaultGraphApiVersion().'/foo',
      'body' => 'foo=bar&access_token=foo_token&appsecret_proof=857d5f035a894f16b4180f19966e055cdeab92d4d53017b13dccd6d43b6497af',
    ];

    $this->assertEquals($expectedArray, $batchRequestArray);
  }

  public function testABatchRequestCanBeSentToGraphWithUniqueAccessTokens()
  {
    $expectedHeaders = [];
    foreach (Facebook::getDefaultHeaders() as $name => $value) {
      $expectedHeaders[] = $name.': '.$value;
    }
    $expectedHeaders = json_encode($expectedHeaders);

    $expectedBatchParams = [
      'batch' => '[{"headers":'.$expectedHeaders.',"method":"POST","relative_url":"\\/'.Facebook::getDefaultGraphApiVersion().'\\/foo","body":"foo=bar&access_token=foo_token_one&appsecret_proof=f73c2ccfee5b2b157bb4c528d2c9f49c16c50e162ea7f8e37b7ab221ae0fc37d","name":"bar_one"},'
        .'{"headers":'.$expectedHeaders.',"method":"GET","relative_url":"\\/'.Facebook::getDefaultGraphApiVersion().'\\/bar?access_token=foo_token_two&appsecret_proof=2fd143dbdd8142fec2b8ffbfe2efd34f71ab23f4747fd1b3fd3bca7b4ceb31d1","name":"bar_two"},'
        .'{"headers":'.$expectedHeaders.',"method":"DELETE","relative_url":"\\/'.Facebook::getDefaultGraphApiVersion().'\\/baz?access_token=foo_token_three&appsecret_proof=a4cb00931c8849fafed059a5ea2f364909ef79c0c14652a4c53001544a3c47d0","name":"bar_three"}]',
      'include_headers' => true,
    ];

    $expectedUrl = BaseRequest::BASE_GRAPH_URL.'/'.Facebook::getDefaultGraphApiVersion();

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
      ->with($expectedUrl, 'POST', $expectedBatchParams)
      ->andReturn('foo_data');
    $this->httpClientMock
      ->shouldReceive('getResponseHttpStatusCode')
      ->once()
      ->andReturn(200);
    $this->httpClientMock
      ->shouldReceive('getResponseHeaders')
      ->once()
      ->andReturn(['response_foo' => 'response_bar']);

    $this->facebookBatchRequest
      ->newRequest('foo_token_one')
      ->withRequestName('bar_one')
      ->withFields(['foo' => 'bar'])
      ->post('/foo');
    $this->facebookBatchRequest
      ->newRequest('foo_token_two')
      ->withRequestName('bar_two')
      ->get('/bar');
    $this->facebookBatchRequest
      ->newRequest('foo_token_three')
      ->withRequestName('bar_three')
      ->delete('/baz');

    $response = $this->facebookBatchRequest->send();

    $this->assertInstanceOf('Facebook\Entities\BatchResponse', $response);
  }

  public function testABatchRequestCanBeSentToGraphWithACommonAccessToken()
  {
    $expectedHeaders = [];
    foreach (Facebook::getDefaultHeaders() as $name => $value) {
      $expectedHeaders[] = $name.': '.$value;
    }
    $expectedHeaders = json_encode($expectedHeaders);

    $expectedBatchParams = [
      'batch' => '[{"headers":'.$expectedHeaders.',"method":"POST","relative_url":"\\/'.Facebook::getDefaultGraphApiVersion().'\\/foo","body":"foo=bar","name":"bar_one"},'
        .'{"headers":'.$expectedHeaders.',"method":"GET","relative_url":"\\/'.Facebook::getDefaultGraphApiVersion().'\\/bar","name":"bar_two"},'
        .'{"headers":'.$expectedHeaders.',"method":"DELETE","relative_url":"\\/'.Facebook::getDefaultGraphApiVersion().'\\/baz","name":"bar_three"}]',
      'include_headers' => true,
      'access_token' => 'foo_token',
      'appsecret_proof' => '857d5f035a894f16b4180f19966e055cdeab92d4d53017b13dccd6d43b6497af',
    ];

    $expectedUrl = BaseRequest::BASE_GRAPH_URL.'/'.Facebook::getDefaultGraphApiVersion();

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
      ->with($expectedUrl, 'POST', $expectedBatchParams)
      ->andReturn('foo_data');
    $this->httpClientMock
      ->shouldReceive('getResponseHttpStatusCode')
      ->once()
      ->andReturn(200);
    $this->httpClientMock
      ->shouldReceive('getResponseHeaders')
      ->once()
      ->andReturn(['response_foo' => 'response_bar']);

    $this->facebookBatchRequest
      ->setBatchRequestAccessToken('foo_token');

    $this->facebookBatchRequest
      ->newRequest()
      ->withRequestName('bar_one')
      ->withFields(['foo' => 'bar'])
      ->post('/foo');
    $this->facebookBatchRequest
      ->newRequest()
      ->withRequestName('bar_two')
      ->get('/bar');
    $this->facebookBatchRequest
      ->newRequest()
      ->withRequestName('bar_three')
      ->delete('/baz');

    $response = $this->facebookBatchRequest->send();

    $this->assertInstanceOf('Facebook\Entities\BatchResponse', $response);
  }

}
