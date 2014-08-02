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
use Facebook\Entities\FacebookResponse;

class FacebookResponseTest extends \PHPUnit_Framework_TestCase
{
  protected $fakeRequest;
  protected $emptyResponse;

  protected function setUp()
  {
    $this->fakeRequest = m::mock('Facebook\Entities\FacebookRequest');
    $this->emptyResponse = new FacebookResponse($this->fakeRequest, '{}');
  }

  public function testGetRequest()
  {
    $this->assertSame($this->fakeRequest, $this->emptyResponse->getRequest());
  }

  public function testGetRaw()
  {
    $response = new FacebookResponse($this->fakeRequest, '{"foo":"bar"}');

    $this->assertSame('{"foo":"bar"}', $response->getRaw());
  }

  public function testGetStatusCode()
  {
    $response = new FacebookResponse($this->fakeRequest, '{}', 1234);

    $this->assertSame(1234, $response->getStatusCode());
  }

  public function testGetBody()
  {
    $response = new FacebookResponse($this->fakeRequest, '[]');

    $this->assertEquals([], $response->getBody());
  }

  public function testGetHeaders()
  {
    $response = new FacebookResponse($this->fakeRequest, '{}', null, ['foo' => 'bar']);

    $this->assertEquals(['foo' => 'bar'], $response->getHeaders());
  }

  public function testGetETag()
  {
    $responseWithETag = new FacebookResponse($this->fakeRequest, '{}', null, ['ETag' => 'test']);

    $this->assertNull($this->emptyResponse->getETag());
    $this->assertEquals('test', $responseWithETag->getETag());
  }

  public function testIsETagHit()
  {
    $responseHit = new FacebookResponse($this->fakeRequest, '{}', 304);

    $this->assertFalse($this->emptyResponse->isETagHit());
    $this->assertTrue($responseHit->isETagHit());
  }

  public function testGetGraphVersion()
  {
    $responseWithVersion = new FacebookResponse($this->fakeRequest, '{}', null, ['Facebook-API-Version' => 'test']);

    $this->assertNull($this->emptyResponse->getGraphVersion());
    $this->assertEquals('test', $responseWithVersion->getGraphVersion());
  }

  public function testIsError()
  {
    $responseWithError = new FacebookResponse($this->fakeRequest, '{"error":{}}');

    $this->assertFalse($this->emptyResponse->isError());
    $this->assertTrue($responseWithError->isError());
  }

  public function testThatConstructorConvertObjectsToArrays()
  {
    $response = new FacebookResponse($this->fakeRequest, '{"foo":{"bar":"baz"}}');

    $this->assertEquals(['foo' => ['bar' => 'baz']], $response->getBody());
  }

  public function testThatConstructorConvertQueryStringToArrays()
  {
    $response = new FacebookResponse($this->fakeRequest, 'foo=bar&baz=foo');

    $this->assertEquals(['foo' => 'bar', 'baz' => 'foo'], $response->getBody());
  }

  public function testThatGetGraphObjectReturnsAGraphObject()
  {
    $this->assertInstanceOf('Facebook\GraphNodes\GraphObject', $this->emptyResponse->getGraphObject());
  }

  public function testThatGetGraphObjectCanCast()
  {
    $this->assertInstanceOf(
      'Facebook\GraphNodes\GraphUser',
      $this->emptyResponse->getGraphObject('Facebook\GraphNodes\GraphUser')
    );
  }

  public function testThatGetGraphObjectListReturnsAnArray()
  {
    $response = new FacebookResponse($this->fakeRequest, '{"data":[]}');

    $this->assertTrue(is_array($response->getGraphObjectList()));
  }

  public function testThatGetGraphObjectListCanCast()
  {
    $response = new FacebookResponse($this->fakeRequest, '{"data":[{"name":"Foo"},{"name":"Bar"}]}');
    $list = $response->getGraphObjectList('Facebook\GraphNodes\GraphUser');

    $this->assertCount(2, $list);
    foreach ($list as $object) {
      if ('Facebook\GraphNodes\GraphUser' !== get_class($object)) {
        $this->fail('getGraphObjectList don\'t cast all elements');
      }
    }
    $this->assertEquals('Foo', $list[0]->getName());
    $this->assertEquals('Bar', $list[1]->getName());
  }

  public function testThatCanReadResponseAsArray()
  {
    $response = new FacebookResponse($this->fakeRequest, '{"foo":"bar"}');

    $this->assertTrue(isset($response['foo']));
    $this->assertFalse(isset($response['bar']));
    $this->assertEquals('bar', $response['foo']);
  }

  /**
   * @expectedException Facebook\Exceptions\FacebookSDKException
   * @expectedExceptionMessage FacebookResponse object can't be modified
   */
  public function testThatCannotSetResponseDataAsArray()
  {
    $response = new FacebookResponse($this->fakeRequest, '{"foo":"bar"}');
    $response['foo'] = 'baz';
  }

  /**
   * @expectedException Facebook\Exceptions\FacebookSDKException
   * @expectedExceptionMessage FacebookResponse object can't be modified
   */
  public function testThatCannotUnsetResponseDataAsArray()
  {
    $response = new FacebookResponse($this->fakeRequest, '{"foo":"bar"}');
    unset($response['foo']);
  }

  /** @todo Add pagination tests */

}
