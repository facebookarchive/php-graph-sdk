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
use Facebook\Entities\FacebookBatchResponse;

class FacebookBatchResponseTest extends \PHPUnit_Framework_TestCase
{

  public function testASuccessfulJsonBatchResponseWillBeDecoded()
  {
    $graphResponseJson = '[';
    // Single Graph object.
    $graphResponseJson .= '{"code":200,"headers":[{"name":"Connection","value":"close"},{"name":"Last-Modified","value":"2013-12-24T00:34:20+0000"},{"name":"Facebook-API-Version","value":"v2.0"},{"name":"ETag","value":"\"fooTag\""},{"name":"Content-Type","value":"text\/javascript; charset=UTF-8"},{"name":"Pragma","value":"no-cache"},{"name":"Access-Control-Allow-Origin","value":"*"},{"name":"Cache-Control","value":"private, no-cache, no-store, must-revalidate"},{"name":"Expires","value":"Sat, 01 Jan 2000 00:00:00 GMT"}],"body":"{\"id\":\"123\",\"name\":\"Foo McBar\",\"updated_time\":\"2013-12-24T00:34:20+0000\",\"verified\":true}"}';
    // Paginated list of Graph objects.
    $graphResponseJson .= ',{"code":200,"headers":[{"name":"Connection","value":"close"},{"name":"Facebook-API-Version","value":"v1.0"},{"name":"ETag","value":"\"barTag\""},{"name":"Content-Type","value":"text\/javascript; charset=UTF-8"},{"name":"Pragma","value":"no-cache"},{"name":"Access-Control-Allow-Origin","value":"*"},{"name":"Cache-Control","value":"private, no-cache, no-store, must-revalidate"},{"name":"Expires","value":"Sat, 01 Jan 2000 00:00:00 GMT"}],"body":"{\"data\":[{\"id\":\"1337\",\"story\":\"Foo story.\"},{\"id\":\"1338\",\"story\":\"Bar story.\"}],\"paging\":{\"previous\":\"previous_url\",\"next\":\"next_url\"}}"}';
    // Endpoint not found.
    //$graphResponseJson .= ',{"code":404,"headers":[{"name":"Connection","value":"close"},{"name":"WWW-Authenticate","value":"OAuth \"Facebook Platform\" \"not_found\" \"(#803) Cannot query users by their username (foo)\""},{"name":"Facebook-API-Version","value":"v2.0"},{"name":"Content-Type","value":"text\/javascript; charset=UTF-8"},{"name":"Pragma","value":"no-cache"},{"name":"Access-Control-Allow-Origin","value":"*"},{"name":"Cache-Control","value":"no-store"},{"name":"Expires","value":"Sat, 01 Jan 2000 00:00:00 GMT"}],"body":"{\"error\":{\"message\":\"(#803) Cannot query users by their username (foo)\",\"type\":\"OAuthException\",\"code\":803}}"}';
    // Invalid access token.
    //$graphResponseJson .= ',{"code":400,"headers":[{"name":"Connection","value":"close"},{"name":"Expires","value":"Sat, 01 Jan 2000 00:00:00 GMT"},{"name":"Cache-Control","value":"no-store"},{"name":"Access-Control-Allow-Origin","value":"*"},{"name":"Pragma","value":"no-cache"},{"name":"Content-Type","value":"text\/javascript; charset=UTF-8"},{"name":"WWW-Authenticate","value":"OAuth \"Facebook Platform\" \"invalid_token\" \"Invalid OAuth access token.\""}],"body":"{\"error\":{\"message\":\"Invalid OAuth access token.\",\"type\":\"OAuthException\",\"code\":190}}"}';
    // After POST operation.
    $graphResponseJson .= ',{"code":200,"headers":[{"name":"Connection","value":"close"},{"name":"Expires","value":"Sat, 01 Jan 2000 00:00:00 GMT"},{"name":"Cache-Control","value":"private, no-cache, no-store, must-revalidate"},{"name":"Access-Control-Allow-Origin","value":"*"},{"name":"Pragma","value":"no-cache"},{"name":"Content-Type","value":"text\/javascript; charset=UTF-8"},{"name":"Facebook-API-Version","value":"v2.0"}],"body":"{\"id\":\"123_1337\"}"}';
    // After DELETE operation.
    $graphResponseJson .= ',{"code":200,"headers":[{"name":"Connection","value":"close"},{"name":"Expires","value":"Sat, 01 Jan 2000 00:00:00 GMT"},{"name":"Cache-Control","value":"private, no-cache, no-store, must-revalidate"},{"name":"Access-Control-Allow-Origin","value":"*"},{"name":"Pragma","value":"no-cache"},{"name":"Content-Type","value":"text\/javascript; charset=UTF-8"},{"name":"Facebook-API-Version","value":"v2.0"}],"body":"true"}';
    $graphResponseJson .= ']';
    $app = new FacebookApp('123', 'foo_secret');
    $response = new FacebookResponse($app, 200, [], $graphResponseJson);
    $batchResponse = new FacebookBatchResponse($response);

    $decodedResponses = $batchResponse->getResponses();

    // Single Graph object.
    $this->assertFalse($decodedResponses[0]->isError(), 'Did not expect Response to return an error for single Graph object.');
    $this->assertInstanceOf('Facebook\GraphNodes\GraphObject', $decodedResponses[0]->getGraphObject());
    // Paginated list of Graph objects.
    $this->assertFalse($decodedResponses[1]->isError(), 'Did not expect Response to return an error for paginated list of Graph objects.');
    $graphList = $decodedResponses[1]->getGraphList();
    $this->assertInstanceOf('Facebook\GraphNodes\GraphObject', $graphList[0]);
    $this->assertInstanceOf('Facebook\GraphNodes\GraphObject', $graphList[1]);
    /*
    // Endpoint not found.
    $this->assertTrue($decodedResponses[2]->isError(), 'Expected Response to return an error for endpoint not found.');
    $this->assertInstanceOf('Facebook\Exceptions\FacebookResponseException', $decodedResponses[2]->getThrownException());
    // Invalid access token.
    $this->assertTrue($decodedResponses[3]->isError(), 'Expected Response to return an error for invalid access token.');
    $this->assertInstanceOf('Facebook\Exceptions\FacebookResponseException', $decodedResponses[3]->getThrownException());
    // After POST operation.
    $this->assertFalse($decodedResponses[4]->isError(), 'Did not expect Response to return an error for POST operation.');
    $this->assertInstanceOf('Facebook\GraphNodes\GraphObject', $decodedResponses[4]->getGraphObject());
    // After DELETE operation.
    $this->assertFalse($decodedResponses[5]->isError(), 'Did not expect Response to return an error for DELETE operation.');
    $this->assertInstanceOf('Facebook\GraphNodes\GraphObject', $decodedResponses[5]->getGraphObject());
    */
  }


  public function testABatchResponseCanBeIteratedOver()
  {
    $graphResponseJson = '[';
    $graphResponseJson .= '{"code":200,"headers":[],"body":"{\"foo\":\"bar\"}"}';
    $graphResponseJson .= ',{"code":200,"headers":[],"body":"{\"foo\":\"bar\"}"}';
    $graphResponseJson .= ',{"code":200,"headers":[],"body":"{\"foo\":\"bar\"}"}';
    $graphResponseJson .= ']';
    $app = new FacebookApp('123', 'foo_secret');
    $response = new FacebookResponse($app, 200, [], $graphResponseJson);
    $batchResponse = new FacebookBatchResponse($response);

    $this->assertInstanceOf('IteratorAggregate', $batchResponse);

    foreach ($batchResponse as $responseEntity) {
      $this->assertInstanceOf('Facebook\Entities\FacebookResponse', $responseEntity);
    }
  }

}
