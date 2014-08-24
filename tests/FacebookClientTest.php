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
namespace Facebook\Tests;

use Facebook\Exceptions\FacebookSDKException;
use Mockery as m;
use Facebook\Entities\FacebookApp;
use Facebook\Entities\FacebookRequest;
use Facebook\FacebookClient;
// These are needed when you uncomment the HTTP clients below.
use Facebook\HttpClients\FacebookCurlHttpClient;
use Facebook\HttpClients\FacebookGuzzleHttpClient;
use Facebook\HttpClients\FacebookStreamHttpClient;

class FacebookClientTest extends \PHPUnit_Framework_TestCase
{

  /**
   * @var FacebookApp
   */
  public static $testFacebookApp;

  /**
   * @var FacebookClient
   */
  public static $testFacebookClient;

  /**
   * @var \Facebook\HttpClients\FacebookHttpClientInterface
   */
  protected $httpClientMock;

  public function setUp()
  {
    $this->httpClientMock = m::mock('Facebook\HttpClients\FacebookHttpClientInterface');
  }

  public function testACustomHttpClientCanBeInjected()
  {
    $client = new FacebookClient($this->httpClientMock);
    $httpHandler = $client->getHttpClientHandler();

    $this->assertInstanceOf('Mockery\MockInterface', $httpHandler);
    $this->assertSame($this->httpClientMock, $httpHandler);
  }

  public function testTheHttpClientWillFallbackToDefault()
  {
    $client = new FacebookClient();
    $httpHandler = $client->getHttpClientHandler();

    if (function_exists('curl_init')) {
      $this->assertInstanceOf('Facebook\HttpClients\FacebookCurlHttpClient', $httpHandler);
    } else {
      $this->assertInstanceOf('Facebook\HttpClients\FacebookStreamHttpClient', $httpHandler);
    }
  }

  public function testBetaModeCanBeDisabledOrEnabledViaConstructor()
  {
    $client = new FacebookClient(null, false);
    $url = $client->getBaseGraphUrl();
    $this->assertEquals(FacebookClient::BASE_GRAPH_URL, $url);

    $client = new FacebookClient(null, true);
    $url = $client->getBaseGraphUrl();
    $this->assertEquals(FacebookClient::BASE_GRAPH_URL_BETA, $url);
  }

  public function testBetaModeCanBeDisabledOrEnabledViaMethod()
  {
    $client = new FacebookClient();
    $client->enableBetaMode(false);
    $url = $client->getBaseGraphUrl();
    $this->assertEquals(FacebookClient::BASE_GRAPH_URL, $url);

    $client->enableBetaMode(true);
    $url = $client->getBaseGraphUrl();
    $this->assertEquals(FacebookClient::BASE_GRAPH_URL_BETA, $url);
  }

  public function testAFacebookRequestEntityCanBeUsedToSendARequestToGraph()
  {
    $facebookApp = new FacebookApp('123', 'foo_secret');
    $facebookRequest = m::mock('Facebook\Entities\FacebookRequest');
    $facebookRequest
      ->shouldReceive('getUrl')
      ->once()
      ->andReturn('/foo');
    $facebookRequest
      ->shouldReceive('getMethod')
      ->once()
      ->andReturn('GET');
    $facebookRequest
      ->shouldReceive('getPostParams')
      ->once()
      ->andReturn([]);
    $facebookRequest
      ->shouldReceive('getHeaders')
      ->once()
      ->andReturn(['request_header' => 'foo']);
    $facebookRequest
      ->shouldReceive('getAccessToken')
      ->once()
      ->andReturn('foo_token');
    $facebookRequest
      ->shouldReceive('getApp')
      ->once()
      ->andReturn($facebookApp);

    $this->httpClientMock
      ->shouldReceive('send')
      ->with(FacebookClient::BASE_GRAPH_URL . '/foo', 'GET', [], ['request_header' => 'foo'])
      ->once()
      ->andReturn('foo_response');
    $this->httpClientMock
      ->shouldReceive('getResponseHttpStatusCode')
      ->once()
      ->andReturn(200);
    $this->httpClientMock
      ->shouldReceive('getResponseHeaders')
      ->once()
      ->andReturn(['response_header' => 'bar']);

    $client = new FacebookClient($this->httpClientMock);
    $response = $client->sendRequest($facebookRequest);

    $this->assertInstanceOf('Facebook\Entities\FacebookResponse', $response);
  }

  public function testAFacebookBatchRequestEntityCanBeUsedToSendABatchRequestToGraph()
  {
    $facebookApp = new FacebookApp('123', 'foo_secret');
    $facebookBatchRequest = m::mock('Facebook\Entities\FacebookBatchRequest');
    $facebookBatchRequest
      ->shouldReceive('prepareRequestsForBatch')
      ->once()
      ->andReturn(null);
    $facebookBatchRequest
      ->shouldReceive('getUrl')
      ->once()
      ->andReturn('');
    $facebookBatchRequest
      ->shouldReceive('getMethod')
      ->once()
      ->andReturn('POST');
    $facebookBatchRequest
      ->shouldReceive('getPostParams')
      ->once()
      ->andReturn([]);
    $facebookBatchRequest
      ->shouldReceive('getHeaders')
      ->once()
      ->andReturn(['request_header' => 'foo']);
    $facebookBatchRequest
      ->shouldReceive('getAccessToken')
      ->once()
      ->andReturn('foo_token');
    $facebookBatchRequest
      ->shouldReceive('getApp')
      ->once()
      ->andReturn($facebookApp);

    $this->httpClientMock
      ->shouldReceive('send')
      ->with(FacebookClient::BASE_GRAPH_URL, 'POST', [], ['request_header' => 'foo'])
      ->once()
      ->andReturn('[]');
    $this->httpClientMock
      ->shouldReceive('getResponseHttpStatusCode')
      ->once()
      ->andReturn(200);
    $this->httpClientMock
      ->shouldReceive('getResponseHeaders')
      ->once()
      ->andReturn(['response_header' => 'bar']);

    $client = new FacebookClient($this->httpClientMock);
    $response = $client->sendBatchRequest($facebookBatchRequest);

    $this->assertInstanceOf('Facebook\Entities\FacebookBatchResponse', $response);
  }

  /**
   * @group integration
   */
  public function testCanCreateATestUserAndGetTheProfileAndThenDeleteTheTestUser()
  {
    $this->initializeTestApp();

    // Create a test user
    $testUserPath = '/' . FacebookTestCredentials::$appId . '/accounts/test-users';
    $params = [
      'installed' => true,
      'name' => 'Foo Phpunit User',
      'locale' => 'en_US',
      'permissions' => implode(',', ['read_stream', 'user_photos']),
    ];

    $request = new FacebookRequest(
      static::$testFacebookApp,
      static::$testFacebookApp->getAccessToken(),
      'POST',
      $testUserPath,
      $params);
    $response = static::$testFacebookClient->sendRequest($request)->getGraphObject();

    $testUserId = $response->getProperty('id');
    $testUserAccessToken = $response->getProperty('access_token');

    // Get the test user's profile
    $request = new FacebookRequest(
      static::$testFacebookApp,
      $testUserAccessToken,
      'GET',
      '/me');
    $graphObject = static::$testFacebookClient->sendRequest($request)->getGraphObject();

    $this->assertInstanceOf('Facebook\GraphNodes\GraphObject', $graphObject);
    $this->assertNotNull($graphObject->getProperty('id'));
    $this->assertEquals('Foo Phpunit User', $graphObject->getProperty('name'));

    // Delete test user
    $request = new FacebookRequest(
      static::$testFacebookApp,
      static::$testFacebookApp->getAccessToken(),
      'DELETE',
      '/' . $testUserId);
    $graphObject = static::$testFacebookClient->sendRequest($request)->getGraphObject();

    $this->assertTrue($graphObject->getProperty('success'));
  }

  public function initializeTestApp()
  {
    if (!file_exists(__DIR__ . '/FacebookTestCredentials.php')) {
      throw new FacebookSDKException(
        'You must create a FacebookTestCredentials.php file from FacebookTestCredentials.php.dist'
      );
    }

    if (!strlen(FacebookTestCredentials::$appId) ||
      !strlen(FacebookTestCredentials::$appSecret)) {
      throw new FacebookSDKException(
        'You must fill out FacebookTestCredentials.php'
      );
    }
    static::$testFacebookApp = new FacebookApp(
      FacebookTestCredentials::$appId, FacebookTestCredentials::$appSecret
    );

    // Use default client
    $client = null;

    // Uncomment to enable curl implementation.
    //$client = new FacebookCurlHttpClient();

    // Uncomment to enable stream wrapper implementation.
    //$client = new FacebookStreamHttpClient();

    // Uncomment to enable Guzzle implementation.
    //$client = new FacebookGuzzleHttpClient();

    static::$testFacebookClient = new FacebookClient($client);
  }

}
