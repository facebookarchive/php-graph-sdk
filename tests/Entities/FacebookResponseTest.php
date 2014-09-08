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
use Facebook\Entities\FacebookResponse;

class FacebookResponseTest extends \PHPUnit_Framework_TestCase
{

  public function testAnEmptyResponseEntityCanInstantiate()
  {
    $app = new FacebookApp('123', 'foo_secret');
    $response = new FacebookResponse($app);

    $this->assertInstanceOf('Facebook\Entities\FacebookResponse', $response);
  }

  public function testAnETagCanBeProperlyAccessed()
  {
    $app = new FacebookApp('123', 'foo_secret');
    $response = new FacebookResponse($app, 200, ['ETag' => 'foo_tag']);

    $eTag = $response->getETag();

    $this->assertEquals('foo_tag', $eTag);
  }

  public function testAProperAppSecretProofCanBeGenerated()
  {
    $app = new FacebookApp('123', 'foo_secret');
    $response = new FacebookResponse($app, 200, [], '', 'foo_token');

    $appSecretProof = $response->getAppSecretProof();

    $this->assertEquals('df4256903ba4e23636cc142117aa632133d75c642bd2a68955be1443bd14deb9', $appSecretProof);
  }

  public function testASuccessfulJsonResponseWillBeDecodedToAGraphObject()
  {
    $app = new FacebookApp('123', 'foo_secret');
    $graphResponseJson = '{"id":"123","name":"Foo"}';
    $response = new FacebookResponse($app, 200, [], $graphResponseJson);

    $decodedResponse = $response->getDecodedBody();
    $graphObject = $response->getGraphObject();

    $this->assertFalse($response->isError(), 'Did not expect Response to return an error.');
    $this->assertEquals([
      'id' => '123',
      'name' => 'Foo',
    ], $decodedResponse);
    $this->assertInstanceOf('Facebook\GraphNodes\GraphObject', $graphObject);
  }

  public function testASuccessfulJsonResponseWillBeDecodedToAGraphList()
  {
    $app = new FacebookApp('123', 'foo_secret');
    $graphResponseJson = '{"data":[{"id":"123","name":"Foo"},{"id":"1337","name":"Bar"}]}';
    $response = new FacebookResponse($app, 200, [], $graphResponseJson);

    $graphObjectList = $response->getGraphList();

    $this->assertFalse($response->isError(), 'Did not expect Response to return an error.');
    $this->assertInstanceOf('Facebook\GraphNodes\GraphObject', $graphObjectList[0]);
    $this->assertInstanceOf('Facebook\GraphNodes\GraphObject', $graphObjectList[1]);
  }

  public function testASuccessfulUrlEncodedKeyValuePairResponseWillBeDecoded()
  {
    $app = new FacebookApp('123', 'foo_secret');
    $graphResponseKeyValuePairs = 'id=123&name=Foo';
    $response = new FacebookResponse($app, 200, [], $graphResponseKeyValuePairs);

    $decodedResponse = $response->getDecodedBody();

    $this->assertFalse($response->isError(), 'Did not expect Response to return an error.');
    $this->assertEquals([
        'id' => '123',
        'name' => 'Foo',
      ], $decodedResponse);
  }

  public function testErrorStatusCanBeCheckedWhenAnErrorResponseIsReturned()
  {
    $app = new FacebookApp('123', 'foo_secret');
    $graphResponse = '{"error":{"message":"Foo error.","type":"OAuthException","code":190,"error_subcode":463}}';
    $response = new FacebookResponse($app, 401, [], $graphResponse);

    $exception = $response->getThrownException();

    $this->assertTrue($response->isError(), 'Expected Response to return an error.');
    $this->assertInstanceOf('Facebook\Exceptions\FacebookResponseException', $exception);
  }

}
