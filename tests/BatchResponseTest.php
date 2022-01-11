<?php

declare(strict_types=1);
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

use Facebook\Application;
use Facebook\GraphNodes\GraphNode;
use Facebook\Request;
use Facebook\Response;
use Facebook\BatchRequest;
use Facebook\BatchResponse;
use PHPUnit\Framework\TestCase;

/**
 * Class BatchResponseTest
 */
class BatchResponseTest extends TestCase
{
    /**
     * @var \Facebook\Application
     */
    protected Application $app;

    /**
     * @var \Facebook\Request
     */
    protected Request $request;

    protected function setUp(): void
    {
        $this->app = new Application('123', 'foo_secret');
        $this->request = new Request(
            $this->app,
            'foo_token',
            'POST',
            '/',
            ['batch' => 'foo'],
            'foo_eTag',
            'v1337'
        );
    }

    public function testASuccessfulJsonBatchResponseWillBeDecoded(): void
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
        $response = new Response($this->request, $graphResponseJson, 200);
        $batchRequest = new BatchRequest($this->app, [
            new Request($this->app, 'token'),
            new Request($this->app, 'token'),
            new Request($this->app, 'token'),
            new Request($this->app, 'token'),
        ]);
        $batchResponse = new BatchResponse($batchRequest, $response);

        $decodedResponses = $batchResponse->getResponses();

        // Single Graph object.
        static::assertFalse($decodedResponses[0]->isError(), 'Did not expect Response to return an error for single Graph object.');
        static::assertInstanceOf(GraphNode::class, $decodedResponses[0]->getGraphNode());
        // Paginated list of Graph objects.
        static::assertFalse($decodedResponses[1]->isError(), 'Did not expect Response to return an error for paginated list of Graph objects.');
        $graphEdge = $decodedResponses[1]->getGraphEdge();
        static::assertInstanceOf(GraphNode::class, $graphEdge[0]);
        static::assertInstanceOf(GraphNode::class, $graphEdge[1]);
    }

    public function testABatchResponseCanBeIteratedOver(): void
    {
        $graphResponseJson = '[';
        $graphResponseJson .= '{"code":200,"headers":[],"body":"{\"foo\":\"bar\"}"}';
        $graphResponseJson .= ',{"code":200,"headers":[],"body":"{\"foo\":\"bar\"}"}';
        $graphResponseJson .= ',{"code":200,"headers":[],"body":"{\"foo\":\"bar\"}"}';
        $graphResponseJson .= ']';
        $response = new Response($this->request, $graphResponseJson, 200);
        $batchRequest = new BatchRequest($this->app, [
            'req_one' => new Request($this->app, 'token'),
            'req_two' => new Request($this->app, 'token'),
            'req_three' => new Request($this->app, 'token'),
        ]);
        $batchResponse = new BatchResponse($batchRequest, $response);

        static::assertInstanceOf(\IteratorAggregate::class, $batchResponse);

        foreach ($batchResponse as $key => $responseEntity) {
            static::assertTrue(in_array($key, ['req_one', 'req_two', 'req_three']));
            static::assertInstanceOf(Response::class, $responseEntity);
        }
    }

    public function testTheOriginalRequestCanBeObtainedForEachRequest(): void
    {
        $graphResponseJson = '[';
        $graphResponseJson .= '{"code":200,"headers":[],"body":"{\"foo\":\"bar\"}"}';
        $graphResponseJson .= ',{"code":200,"headers":[],"body":"{\"foo\":\"bar\"}"}';
        $graphResponseJson .= ',{"code":200,"headers":[],"body":"{\"foo\":\"bar\"}"}';
        $graphResponseJson .= ']';
        $response = new Response($this->request, $graphResponseJson, 200);

        $requests = [
            new Request($this->app, 'foo_token_one', 'GET', '/me'),
            new Request($this->app, 'foo_token_two', 'POST', '/you'),
            new Request($this->app, 'foo_token_three', 'DELETE', '/123456'),
        ];

        $batchRequest = new BatchRequest($this->app, $requests);
        $batchResponse = new BatchResponse($batchRequest, $response);

        static::assertInstanceOf(Response::class, $batchResponse[0]);
        static::assertInstanceOf(Request::class, $batchResponse[0]->getRequest());
        static::assertEquals('foo_token_one', $batchResponse[0]->getAccessToken());
        static::assertEquals('foo_token_two', $batchResponse[1]->getAccessToken());
        static::assertEquals('foo_token_three', $batchResponse[2]->getAccessToken());
    }

    public function testHeadersFromBatchRequestCanBeAccessed(): void
    {
        $graphResponseJson = '[';
        $graphResponseJson .= '{"code":200,"headers":[{"name":"Facebook-API-Version","value":"v2.0"},{"name":"ETag","value":"\"fooTag\""}],"body":"{\"foo\":\"bar\"}"}';
        $graphResponseJson .= ',{"code":200,"headers":[{"name":"Facebook-API-Version","value":"v2.5"},{"name":"ETag","value":"\"barTag\""}],"body":"{\"foo\":\"bar\"}"}';
        $graphResponseJson .= ']';
        $response = new Response($this->request, $graphResponseJson, 200);

        $requests = [
            new Request($this->app, 'foo_token_one', 'GET', '/me'),
            new Request($this->app, 'foo_token_two', 'GET', '/you'),
        ];

        $batchRequest = new BatchRequest($this->app, $requests);
        $batchResponse = new BatchResponse($batchRequest, $response);

        static::assertEquals('v2.0', $batchResponse[0]->getGraphVersion());
        static::assertEquals('"fooTag"', $batchResponse[0]->getETag());
        static::assertEquals('v2.5', $batchResponse[1]->getGraphVersion());
        static::assertEquals('"barTag"', $batchResponse[1]->getETag());
        static::assertEquals([
            'Facebook-API-Version' => 'v2.5',
            'ETag' => '"barTag"',
        ], $batchResponse[1]->getHeaders());
    }
}
