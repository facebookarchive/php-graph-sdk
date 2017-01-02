<?php
/**
 * Copyright 2017 Facebook, Inc.
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

use Facebook\FacebookApp;
use Facebook\FacebookRequest;
use Facebook\FacebookResponse;
use Facebook\FacebookBatchRequest;
use Facebook\FacebookBatchResponse;

class FacebookBatchResponseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Facebook\FacebookApp
     */
    protected $app;

    /**
     * @var \Facebook\FacebookRequest
     */
    protected $request;

    protected function setUp()
    {
        $this->app = new FacebookApp('123', 'foo_secret');
        $this->request = new FacebookRequest(
            $this->app,
            'foo_token',
            'POST',
            '/',
            ['batch' => 'foo'],
            'foo_eTag',
            'v1337'
        );
    }

    public function testASuccessfulJsonBatchResponseWillBeDecoded()
    {
        $graphResponseJson = '[';
        // Single Graph object.
        $graphResponseJson .= '{"code":200,"headers":[{"name":"Connection","value":"close"},{"name":"Last-Modified","value":"2013-12-24T00:34:20+0000"},{"name":"Facebook-API-Version","value":"v2.0"},{"name":"ETag","value":"\"fooTag\""},{"name":"Content-Type","value":"text\/javascript; charset=UTF-8"},{"name":"Pragma","value":"no-cache"},{"name":"Access-Control-Allow-Origin","value":"*"},{"name":"Cache-Control","value":"private, no-cache, no-store, must-revalidate"},{"name":"Expires","value":"Sat, 01 Jan 2000 00:00:00 GMT"}],"body":"{\"id\":\"123\",\"name\":\"Foo McBar\",\"updated_time\":\"2013-12-24T00:34:20+0000\",\"verified\":true}"}';
        // Paginated list of Graph objects.
        $graphResponseJson .= ',{"code":200,"headers":[{"name":"Connection","value":"close"},{"name":"Facebook-API-Version","value":"v1.0"},{"name":"ETag","value":"\"barTag\""},{"name":"Content-Type","value":"text\/javascript; charset=UTF-8"},{"name":"Pragma","value":"no-cache"},{"name":"Access-Control-Allow-Origin","value":"*"},{"name":"Cache-Control","value":"private, no-cache, no-store, must-revalidate"},{"name":"Expires","value":"Sat, 01 Jan 2000 00:00:00 GMT"}],"body":"{\"data\":[{\"id\":\"1337\",\"story\":\"Foo story.\"},{\"id\":\"1338\",\"story\":\"Bar story.\"}],\"paging\":{\"previous\":\"previous_url\",\"next\":\"next_url\"}}"}';
        // After POST operation.
        $graphResponseJson .= ',{"code":200,"headers":[{"name":"Connection","value":"close"},{"name":"Expires","value":"Sat, 01 Jan 2000 00:00:00 GMT"},{"name":"Cache-Control","value":"private, no-cache, no-store, must-revalidate"},{"name":"Access-Control-Allow-Origin","value":"*"},{"name":"Pragma","value":"no-cache"},{"name":"Content-Type","value":"text\/javascript; charset=UTF-8"},{"name":"Facebook-API-Version","value":"v2.0"}],"body":"{\"id\":\"123_1337\"}"}';
        // After DELETE operation.
        $graphResponseJson .= ',{"code":200,"headers":[{"name":"Connection","value":"close"},{"name":"Expires","value":"Sat, 01 Jan 2000 00:00:00 GMT"},{"name":"Cache-Control","value":"private, no-cache, no-store, must-revalidate"},{"name":"Access-Control-Allow-Origin","value":"*"},{"name":"Pragma","value":"no-cache"},{"name":"Content-Type","value":"text\/javascript; charset=UTF-8"},{"name":"Facebook-API-Version","value":"v2.0"}],"body":"true"}';
        $graphResponseJson .= ']';
        $response = new FacebookResponse($this->request, $graphResponseJson, 200);
        $batchRequest = new FacebookBatchRequest($this->app, [
            new FacebookRequest($this->app, 'token'),
            new FacebookRequest($this->app, 'token'),
            new FacebookRequest($this->app, 'token'),
            new FacebookRequest($this->app, 'token'),
        ]);
        $batchResponse = new FacebookBatchResponse($batchRequest, $response);

        $decodedResponses = $batchResponse->getResponses();

        // Single Graph object.
        $this->assertFalse($decodedResponses[0]->isError(), 'Did not expect Response to return an error for single Graph object.');
        $this->assertInstanceOf('Facebook\GraphNodes\GraphNode', $decodedResponses[0]->getGraphNode());
        // Paginated list of Graph objects.
        $this->assertFalse($decodedResponses[1]->isError(), 'Did not expect Response to return an error for paginated list of Graph objects.');
        $graphEdge = $decodedResponses[1]->getGraphEdge();
        $this->assertInstanceOf('Facebook\GraphNodes\GraphNode', $graphEdge[0]);
        $this->assertInstanceOf('Facebook\GraphNodes\GraphNode', $graphEdge[1]);
    }

    public function testABatchResponseCanBeIteratedOver()
    {
        $graphResponseJson = '[';
        $graphResponseJson .= '{"code":200,"headers":[],"body":"{\"foo\":\"bar\"}"}';
        $graphResponseJson .= ',{"code":200,"headers":[],"body":"{\"foo\":\"bar\"}"}';
        $graphResponseJson .= ',{"code":200,"headers":[],"body":"{\"foo\":\"bar\"}"}';
        $graphResponseJson .= ']';
        $response = new FacebookResponse($this->request, $graphResponseJson, 200);
        $batchRequest = new FacebookBatchRequest($this->app, [
            'req_one' => new FacebookRequest($this->app, 'token'),
            'req_two' => new FacebookRequest($this->app, 'token'),
            'req_three' => new FacebookRequest($this->app, 'token'),
        ]);
        $batchResponse = new FacebookBatchResponse($batchRequest, $response);

        $this->assertInstanceOf('IteratorAggregate', $batchResponse);

        foreach ($batchResponse as $key => $responseEntity) {
            $this->assertTrue(in_array($key, ['req_one', 'req_two', 'req_three']));
            $this->assertInstanceOf('Facebook\FacebookResponse', $responseEntity);
        }
    }

    public function testTheOriginalRequestCanBeObtainedForEachRequest()
    {
        $graphResponseJson = '[';
        $graphResponseJson .= '{"code":200,"headers":[],"body":"{\"foo\":\"bar\"}"}';
        $graphResponseJson .= ',{"code":200,"headers":[],"body":"{\"foo\":\"bar\"}"}';
        $graphResponseJson .= ',{"code":200,"headers":[],"body":"{\"foo\":\"bar\"}"}';
        $graphResponseJson .= ']';
        $response = new FacebookResponse($this->request, $graphResponseJson, 200);

        $requests = [
            new FacebookRequest($this->app, 'foo_token_one', 'GET', '/me'),
            new FacebookRequest($this->app, 'foo_token_two', 'POST', '/you'),
            new FacebookRequest($this->app, 'foo_token_three', 'DELETE', '/123456'),
        ];

        $batchRequest = new FacebookBatchRequest($this->app, $requests);
        $batchResponse = new FacebookBatchResponse($batchRequest, $response);

        $this->assertInstanceOf('Facebook\FacebookResponse', $batchResponse[0]);
        $this->assertInstanceOf('Facebook\FacebookRequest', $batchResponse[0]->getRequest());
        $this->assertEquals('foo_token_one', $batchResponse[0]->getAccessToken());
        $this->assertEquals('foo_token_two', $batchResponse[1]->getAccessToken());
        $this->assertEquals('foo_token_three', $batchResponse[2]->getAccessToken());
    }

    public function testHeadersFromBatchRequestCanBeAccessed()
    {
        $graphResponseJson = '[';
        $graphResponseJson .= '{"code":200,"headers":[{"name":"Facebook-API-Version","value":"v2.0"},{"name":"ETag","value":"\"fooTag\""}],"body":"{\"foo\":\"bar\"}"}';
        $graphResponseJson .= ',{"code":200,"headers":[{"name":"Facebook-API-Version","value":"v2.5"},{"name":"ETag","value":"\"barTag\""}],"body":"{\"foo\":\"bar\"}"}';
        $graphResponseJson .= ']';
        $response = new FacebookResponse($this->request, $graphResponseJson, 200);

        $requests = [
            new FacebookRequest($this->app, 'foo_token_one', 'GET', '/me'),
            new FacebookRequest($this->app, 'foo_token_two', 'GET', '/you'),
        ];

        $batchRequest = new FacebookBatchRequest($this->app, $requests);
        $batchResponse = new FacebookBatchResponse($batchRequest, $response);

        $this->assertEquals('v2.0', $batchResponse[0]->getGraphVersion());
        $this->assertEquals('"fooTag"', $batchResponse[0]->getETag());
        $this->assertEquals('v2.5', $batchResponse[1]->getGraphVersion());
        $this->assertEquals('"barTag"', $batchResponse[1]->getETag());
        $this->assertEquals([
          'Facebook-API-Version' => 'v2.5',
          'ETag' => '"barTag"',
        ], $batchResponse[1]->getHeaders());
    }
}
