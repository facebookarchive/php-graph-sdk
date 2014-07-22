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

use Mockery as m;
use Facebook\Entities\FacebookBatchRequest;
use Facebook\Entities\FacebookRequest;
use Facebook\Entities\FacebookBatchedRequest;
use Facebook\Exceptions\FacebookSDKException;

class FacebookBatchRequestTest extends \PHPUnit_Framework_TestCase
{
  protected $fakeAccessToken;

  protected function setUp()
  {
    $fakeApp = m::mock('Facebook\Entities\FacebookApp', ['1234', 'S3cr3T'])->makePartial();
    $this->fakeAccessToken = m::mock('Facebook\Entities\AccessToken', [$fakeApp, 'AbCd'])->makePartial();
  }

  public function testThatCanCreateEmptyBatchRequest()
  {
    new FacebookBatchRequest();
  }

  public function testThatConstructorSetBatchRequestInfos()
  {
    $batchRequest = new FacebookBatchRequest();
    $params = $batchRequest->getParameters();

    $this->assertEquals('/', $batchRequest->getEndpoint());
    $this->assertEquals('POST', $batchRequest->getMethod());
    $this->assertArrayHasKey('batch', $params);
    $this->assertArrayHasKey('include_headers', $params);
    $this->assertTrue($params['include_headers']);
  }

  /**
   * @expectedException Facebook\Exceptions\FacebookSDKException
   * @expectedExceptionMessage There is a request without access token in a batch request that do not have a default one
   */
  public function testThatThrowAnExceptionWhenARequestDontHaveAnAccessTokenAndBatchRequestToo()
  {
    new FacebookBatchRequest([
      new FacebookRequest('/me'),
    ]);
  }

  public function testThatWithFallbackAccessTokenCanAddRequestWithoutOne()
  {
    try {
      new FacebookBatchRequest([new FacebookRequest('/me')], $this->fakeAccessToken);
    } catch (FacebookSDKException $e) {
      $this->fail('Should be possible to have a request without access token when the batch request have one');
    }
  }

  public function testThatWhithoutFallbackAccessTokenCanAddRequestWithOne()
  {
    try {
      new FacebookBatchRequest([
        new FacebookRequest('/me', 'GET', [], $this->fakeAccessToken),
      ]);
    } catch (FacebookSDKException $e) {
      $this->fail('Should be possible to have a request with access token when the batch request don\'t have one');
    }
  }

  public function testThatParametersAreFilled()
  {
    $request = new FacebookBatchRequest([new FacebookRequest('/me')], $this->fakeAccessToken);
    $params = $request->getParameters();

    $this->assertCount(1, $params['batch']);
    $this->assertTrue(is_array($params['batch'][0]));
    $this->assertArrayHasKey('method', $params['batch'][0]);
    $this->assertArrayHasKey('relative_url', $params['batch'][0]);

    if (!empty($request->getHeaders())) {
      $this->assertArrayHasKey('headers', $params['batch'][0]);
    }
  }

  public function testThatTrailingSlashIsRemovedInRelativeUrl()
  {
    $request = new FacebookBatchRequest([new FacebookRequest('/me')], $this->fakeAccessToken);
    $params = $request->getParameters();

    $this->assertEquals('me', $params['batch'][0]['relative_url']);
  }

  public function testThatRequestAccessTokenIsAddedToParameters()
  {
    $request = new FacebookBatchRequest([
      new FacebookRequest('/me', 'GET', [], $this->fakeAccessToken),
    ]);
    $params = $request->getParameters();

    $this->assertArrayHasKey('access_token', $params['batch'][0]);
    $this->assertEquals($this->fakeAccessToken->getValue(), $params['batch'][0]['access_token']);
  }

  public function testThatGetRequestParametersAreAppendedToRelativeUrl()
  {
    $request = new FacebookBatchRequest([new FacebookRequest('/me', 'GET', ['foo' => 'bar'])], $this->fakeAccessToken);
    $params = $request->getParameters();

    $this->assertEquals('me?foo=bar', $params['batch'][0]['relative_url']);
  }

  public function testThatPostRequestParametersAreAppendedToBody()
  {
    $request = new FacebookBatchRequest([new FacebookRequest('/me', 'POST', ['foo' => 'bar'])], $this->fakeAccessToken);
    $params = $request->getParameters();

    $this->assertEquals('me', $params['batch'][0]['relative_url']);
    $this->assertArrayHasKey('body', $params['batch'][0]);
    $this->assertEquals('foo=bar', $params['batch'][0]['body']);
  }

  public function testThatAcceptBatchedRequest()
  {
    try {
      new FacebookBatchRequest([new FacebookBatchedRequest(new FacebookRequest('/me'))], $this->fakeAccessToken);
    } catch (FacebookSDKException $e) {
      $this->fail('Should accept FacebookBatchedRequest');
    }
  }

  public function testThatANamedBatchedRequestAddsAParameter() {
    $request = new FacebookBatchRequest([
      new FacebookBatchedRequest(new FacebookRequest('/me'), 'req_name'),
    ], $this->fakeAccessToken);
    $params = $request->getParameters();

    $this->assertArrayHasKey('name', $params['batch'][0]);
    $this->assertEquals('req_name', $params['batch'][0]['name']);
  }

  /**
   * @expectedException Facebook\Exceptions\FacebookSDKException
   * @expectedExceptionMessage FacebookBatchedRequest named "parent_name" don't exists
   */
  public function testThatThrowAnExceptionWhenARequestDependsOnANonExistsRequest()
  {
    new FacebookBatchRequest([
      new FacebookBatchedRequest(new FacebookRequest('/me'), '', 'parent_name'),
    ], $this->fakeAccessToken);
  }

  public function testThatABatchedRequestThatDependsOnAnOtherAddsAParameter() {
    $request = new FacebookBatchRequest([
      new FacebookBatchedRequest(new FacebookRequest('/me'), 'parent_name'),
      new FacebookBatchedRequest(new FacebookRequest('/me'), '', 'parent_name'),
    ], $this->fakeAccessToken);
    $params = $request->getParameters();

    $this->assertArrayHasKey('depends_on', $params['batch'][1]);
    $this->assertEquals('parent_name', $params['batch'][1]['depends_on']);
  }

  public function testThatANonOmittedBatchedRequestAddsAParameter() {
    $request = new FacebookBatchRequest([
      new FacebookBatchedRequest(new FacebookRequest('/me'), '', '', false),
    ], $this->fakeAccessToken);
    $params = $request->getParameters();

    $this->assertArrayHasKey('omit_response_on_success', $params['batch'][0]);
    $this->assertFalse($params['batch'][0]['omit_response_on_success']);
  }

  /** @todo Add more tests */

}
