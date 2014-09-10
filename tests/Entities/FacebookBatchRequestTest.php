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

use Facebook\Entities\FacebookApp;
use Facebook\Entities\FacebookRequest;
use Facebook\Entities\FacebookBatchRequest;

class FacebookBatchRequestTest extends \PHPUnit_Framework_TestCase
{

  protected $requestHeaders = [];

  public function setUp()
  {
    $this->requestHeaders = [];
    foreach (FacebookRequest::getDefaultHeaders() as $name => $value) {
      $this->requestHeaders[] = $name . ': ' . $value;
    }
  }

  public function testEmptyBatchRequestEntitiesCanBeInstantiated()
  {
    $batchRequest = new FacebookBatchRequest();
    $this->assertInstanceOf('\\Facebook\\Entities\\FacebookBatchRequest', $batchRequest);
  }

  public function testABatchRequestWillInstantiateWithTheProperProperties()
  {
    $app = new FacebookApp('123', 'foo_secret');
    $batchRequest = new FacebookBatchRequest($app, 'foo_token', [], 'v0.1337');

    $batchApp = $batchRequest->getApp();
    $accessToken = $batchRequest->getAccessToken();
    $method = $batchRequest->getMethod();
    $endpoint = $batchRequest->getEndpoint();
    $graphVersion = $batchRequest->getGraphVersion();

    $this->assertSame($app, $batchApp);
    $this->assertEquals('foo_token', $accessToken);
    $this->assertEquals('POST', $method);
    $this->assertEquals('', $endpoint);
    $this->assertEquals('v0.1337', $graphVersion);
  }

  public function testMissingAppOrAccessTokensOnRequestObjectsWillFallbackToBatchDefaults()
  {
    $app = new FacebookApp('123', 'foo_secret');
    $batchRequest = new FacebookBatchRequest($app, 'foo_token');

    $requestTotallyEmpty = new FacebookRequest();
    $batchRequest->addFallbackDefaults($requestTotallyEmpty);
    $appTotallyEmpty = $requestTotallyEmpty->getApp();
    $accessTokenTotallyEmpty = $requestTotallyEmpty->getAccessToken();

    $this->assertSame($app, $appTotallyEmpty);
    $this->assertEquals('foo_token', $accessTokenTotallyEmpty);

    $requestTokenOnly = new FacebookRequest(null, 'bar_token');
    $batchRequest->addFallbackDefaults($requestTokenOnly);
    $appTokenOnly = $requestTokenOnly->getApp();
    $accessTokenTokenOnly = $requestTokenOnly->getAccessToken();

    $this->assertSame($app, $appTokenOnly);
    $this->assertEquals('bar_token', $accessTokenTokenOnly);

    $myApp = new FacebookApp('1337', 'bar_secret');
    $requestAppOnly = new FacebookRequest($myApp);
    $batchRequest->addFallbackDefaults($requestAppOnly);
    $appAppOnly = $requestAppOnly->getApp();
    $accessTokenAppOnly = $requestAppOnly->getAccessToken();

    $this->assertSame($myApp, $appAppOnly);
    $this->assertEquals('foo_token', $accessTokenAppOnly);
  }

  /**
   * @expectedException \Facebook\Exceptions\FacebookSDKException
   */
  public function testWillThrowWhenNoThereIsNoAppFallback()
  {
    $batchRequest = new FacebookBatchRequest();

    $requestTotallyEmpty = new FacebookRequest(null, 'foo_token');
    $batchRequest->addFallbackDefaults($requestTotallyEmpty);
  }

  /**
   * @expectedException \Facebook\Exceptions\FacebookSDKException
   */
  public function testWillThrowWhenNoThereIsNoAccessTokenFallback()
  {
    $batchRequest = new FacebookBatchRequest();

    $app = new FacebookApp('123', 'foo_secret');
    $requestTotallyEmpty = new FacebookRequest($app);
    $batchRequest->addFallbackDefaults($requestTotallyEmpty);
  }

  /**
   * @expectedException \Facebook\Exceptions\FacebookSDKException
   */
  public function testAnInvalidTypeGivenToAddWillThrow()
  {
    $batchRequest = new FacebookBatchRequest();

    $batchRequest->add('foo');
  }

  public function testAddingRequestsWillBeFormattedInAnArrayProperly()
  {
    $app = new FacebookApp('123', 'foo_secret');
    $batchRequest = new FacebookBatchRequest($app, 'foo_token');

    $requestOne = new FacebookRequest(null, null, 'GET', '/foo');
    $requestTwo = new FacebookRequest(null, null, 'POST', '/bar', ['foo' => 'bar']);
    $requestThree = new FacebookRequest(null, null, 'DELETE', '/baz');

    $batchRequest->add($requestOne);
    $batchRequest->add($requestTwo, 'my-second-request');
    $batchRequest->add($requestThree, 'my-third-request');

    $requests = $batchRequest->getRequests();

    $expectedRequests = [
      [
        'name' => null,
        'request' => $requestOne,
      ],
      [
        'name' => 'my-second-request',
        'request' => $requestTwo,
      ],
      [
        'name' => 'my-third-request',
        'request' => $requestThree,
      ],
    ];
    $this->assertEquals($expectedRequests, $requests);
  }

  public function testANumericArrayOfRequestsCanBeAdded()
  {
    $app = new FacebookApp('123', 'foo_secret');
    $batchRequest = new FacebookBatchRequest($app, 'foo_token');

    $requests = [
      new FacebookRequest(null, null, 'GET', '/foo'),
      new FacebookRequest(null, null, 'POST', '/bar', ['foo' => 'bar']),
      new FacebookRequest(null, null, 'DELETE', '/baz'),
      ];

    $batchRequest->add($requests);
    $formattedRequests = $batchRequest->getRequests();

    $expectedRequests = [
      [
        'name' => 0,
        'request' => $requests[0],
      ],
      [
        'name' => 1,
        'request' => $requests[1],
      ],
      [
        'name' => 2,
        'request' => $requests[2],
      ],
    ];
    $this->assertEquals($expectedRequests, $formattedRequests);
  }

  public function testAnAssociativeArrayOfRequestsCanBeAdded()
  {
    $app = new FacebookApp('123', 'foo_secret');
    $batchRequest = new FacebookBatchRequest($app, 'foo_token');

    $requests = [
      'req-one' => new FacebookRequest(null, null, 'GET', '/foo'),
      'req-two' => new FacebookRequest(null, null, 'POST', '/bar', ['foo' => 'bar']),
      'req-three' => new FacebookRequest(null, null, 'DELETE', '/baz'),
      ];

    $batchRequest->add($requests);
    $formattedRequests = $batchRequest->getRequests();

    $expectedRequests = [
      [
        'name' => 'req-one',
        'request' => $requests['req-one'],
      ],
      [
        'name' => 'req-two',
        'request' => $requests['req-two'],
      ],
      [
        'name' => 'req-three',
        'request' => $requests['req-three'],
      ],
    ];
    $this->assertEquals($expectedRequests, $formattedRequests);
  }

  public function testRequestsCanBeInjectedIntoConstructor()
  {
    $requests = [
      new FacebookRequest(null, null, 'GET', '/foo'),
      new FacebookRequest(null, null, 'POST', '/bar', ['foo' => 'bar']),
      new FacebookRequest(null, null, 'DELETE', '/baz'),
    ];

    $app = new FacebookApp('123', 'foo_secret');
    $batchRequest = new FacebookBatchRequest($app, 'foo_token', $requests);

    $formattedRequests = $batchRequest->getRequests();

    $expectedRequests = [
      [
        'name' => 0,
        'request' => $requests[0],
      ],
      [
        'name' => 1,
        'request' => $requests[1],
      ],
      [
        'name' => 2,
        'request' => $requests[2],
      ],
    ];
    $this->assertEquals($expectedRequests, $formattedRequests);
  }

  /**
   * @expectedException \Facebook\Exceptions\FacebookSDKException
   */
  public function testAZeroRequestCountWithThrow()
  {
    $app = new FacebookApp('123', 'foo_secret');
    $batchRequest = new FacebookBatchRequest($app, 'foo_token');
    $batchRequest->validateBatchRequestCount();
  }

  /**
   * @expectedException \Facebook\Exceptions\FacebookSDKException
   */
  public function testMoreThanFiftyRequestsWillThrow()
  {
    $app = new FacebookApp('123', 'foo_secret');
    $batchRequest = new FacebookBatchRequest($app, 'foo_token');

    for ($i=0; $i<=50; $i++) {
      $batchRequest->add(new FacebookRequest());
    }
    $batchRequest->validateBatchRequestCount();
  }

  public function testLessThanFiftyRequestsWillNotThrow()
  {
    $app = new FacebookApp('123', 'foo_secret');
    $batchRequest = new FacebookBatchRequest($app, 'foo_token');

    for ($i=0; $i<50; $i++) {
      $batchRequest->add(new FacebookRequest());
    }
    $batchRequest->validateBatchRequestCount();
  }

  public function testBatchRequestEntitiesProperlyGetConvertedToAnArrayForJsonEncodingForEachMethod()
  {
    $app = new FacebookApp('123', 'foo_secret');

    // GET request
    $batchRequest = new FacebookBatchRequest($app, 'foo_token');
    $batchRequest->add(new FacebookRequest(null, null, 'GET', '/foo', ['foo' => 'bar']), 'foo_name');

    $requests = $batchRequest->getRequests();
    $batchRequestArray = FacebookBatchRequest::requestEntityToBatchArray($requests[0]['request'], $requests[0]['name']);

    $expectedArray = [
      'headers' => $this->requestHeaders,
      'method' => 'GET',
      'relative_url' => '/'.FacebookRequest::getDefaultGraphApiVersion().'/foo?foo=bar&access_token=foo_token&appsecret_proof=df4256903ba4e23636cc142117aa632133d75c642bd2a68955be1443bd14deb9',
      'name' => 'foo_name',
    ];

    $this->assertEquals($expectedArray, $batchRequestArray);

    // POST request
    $batchRequest = new FacebookBatchRequest($app, 'bar_token');
    $batchRequest->add(new FacebookRequest(null, null, 'POST', '/bar', ['bar' => 'baz']), 'bar_name');

    $requests = $batchRequest->getRequests();
    $batchRequestArray = FacebookBatchRequest::requestEntityToBatchArray($requests[0]['request'], $requests[0]['name']);

    $expectedArray = [
      'headers' => $this->requestHeaders,
      'method' => 'POST',
      'relative_url' => '/'.FacebookRequest::getDefaultGraphApiVersion().'/bar',
      'body' => 'bar=baz&access_token=bar_token&appsecret_proof=2ceec40b7b9fd7d38fff1767b766bcc6b1f9feb378febac4612c156e6a8354bd',
      'name' => 'bar_name',
    ];

    $this->assertEquals($expectedArray, $batchRequestArray);

    // DELETE request
    $batchRequest = new FacebookBatchRequest($app, 'bar_token');
    $batchRequest->add(new FacebookRequest(null, null, 'DELETE', '/bar'), 'bar_name');

    $requests = $batchRequest->getRequests();
    $batchRequestArray = FacebookBatchRequest::requestEntityToBatchArray($requests[0]['request'], $requests[0]['name']);

    $expectedArray = [
      'headers' => $this->requestHeaders,
      'method' => 'DELETE',
      'relative_url' => '/'.FacebookRequest::getDefaultGraphApiVersion().'/bar?access_token=bar_token&appsecret_proof=2ceec40b7b9fd7d38fff1767b766bcc6b1f9feb378febac4612c156e6a8354bd',
      'name' => 'bar_name',
    ];

    $this->assertEquals($expectedArray, $batchRequestArray);
  }

  public function testPreppingABatchRequestProperlySetsThePostParams()
  {
    $app = new FacebookApp('123', 'foo_secret');
    $batchRequest = new FacebookBatchRequest($app, 'foo_token');

    $batchRequest->add(new FacebookRequest(null, 'bar_token', 'GET', '/foo'), 'foo_name');
    $batchRequest->add(new FacebookRequest(null, null, 'POST', '/bar', ['foo' => 'bar']));

    $batchRequest->prepareRequestsForBatch();
    $params = $batchRequest->getParams();

    $expectedHeaders = json_encode($this->requestHeaders);
    $version = FacebookRequest::getDefaultGraphApiVersion();
    $expectedBatchParams = [
      'batch' => '[{"headers":'.$expectedHeaders.',"method":"GET","relative_url":"\\/' . $version . '\\/foo?access_token=bar_token&appsecret_proof=2ceec40b7b9fd7d38fff1767b766bcc6b1f9feb378febac4612c156e6a8354bd","name":"foo_name"},'
        .'{"headers":'.$expectedHeaders.',"method":"POST","relative_url":"\\/' . $version . '\\/bar","body":"foo=bar&access_token=foo_token&appsecret_proof=df4256903ba4e23636cc142117aa632133d75c642bd2a68955be1443bd14deb9"}]',
      'include_headers' => true,
      'access_token' => 'foo_token',
      'appsecret_proof' => 'df4256903ba4e23636cc142117aa632133d75c642bd2a68955be1443bd14deb9',
    ];
    $this->assertEquals($expectedBatchParams, $params);
  }

}
