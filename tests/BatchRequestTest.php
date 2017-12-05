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
 */
namespace Facebook\Tests;

use Facebook\Application;
use Facebook\Request;
use Facebook\BatchRequest;
use Facebook\FileUpload\File;
use PHPUnit\Framework\TestCase;

class BatchRequestTest extends TestCase
{
    /**
     * @var Application
     */
    private $app;

    protected function setUp()
    {
        $this->app = new Application('123', 'foo_secret');
    }

    public function testABatchRequestWillInstantiateWithTheProperProperties()
    {
        $batchRequest = new BatchRequest($this->app, [], 'foo_token', 'v0.1337');

        $this->assertSame($this->app, $batchRequest->getApplication());
        $this->assertEquals('foo_token', $batchRequest->getAccessToken());
        $this->assertEquals('POST', $batchRequest->getMethod());
        $this->assertEquals('', $batchRequest->getEndpoint());
        $this->assertEquals('v0.1337', $batchRequest->getGraphVersion());
    }

    public function testEmptyRequestWillFallbackToBatchDefaults()
    {
        $request = new Request();

        $this->createBatchRequest()->addFallbackDefaults($request);

        $this->assertRequestContainsAppAndToken($request, $this->app, 'foo_token');
    }

    public function testRequestWithTokenOnlyWillFallbackToBatchDefaults()
    {
        $request = new Request(null, 'bar_token');

        $this->createBatchRequest()->addFallbackDefaults($request);

        $this->assertRequestContainsAppAndToken($request, $this->app, 'bar_token');
    }

    public function testRequestWithAppOnlyWillFallbackToBatchDefaults()
    {
        $customApp = new Application('1337', 'bar_secret');
        $request = new Request($customApp);

        $this->createBatchRequest()->addFallbackDefaults($request);

        $this->assertRequestContainsAppAndToken($request, $customApp, 'foo_token');
    }

    /**
     * @expectedException \Facebook\Exception\SDKException
     */
    public function testWillThrowWhenNoThereIsNoAppFallback()
    {
        $batchRequest = new BatchRequest();

        $batchRequest->addFallbackDefaults(new Request(null, 'foo_token'));
    }

    /**
     * @expectedException \Facebook\Exception\SDKException
     */
    public function testWillThrowWhenNoThereIsNoAccessTokenFallback()
    {
        $request = new BatchRequest();

        $request->addFallbackDefaults(new Request($this->app));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testAnInvalidTypeGivenToAddWillThrow()
    {
        $request = new BatchRequest();

        $request->add('foo');
    }

    public function testAddingRequestsWillBeFormattedInAnArrayProperly()
    {
        $requests = [
            null => new Request(null, null, 'GET', '/foo'),
            'my-second-request' => new Request(null, null, 'POST', '/bar', ['foo' => 'bar']),
            'my-third-request' => new Request(null, null, 'DELETE', '/baz')
        ];

        $batchRequest = $this->createBatchRequest();
        $batchRequest->add($requests[null]);
        $batchRequest->add($requests['my-second-request'], 'my-second-request');
        $batchRequest->add($requests['my-third-request'], 'my-third-request');

        $formattedRequests = $batchRequest->getRequests();

        $this->assertRequestsMatch($requests, $formattedRequests);
    }

    public function testANumericArrayOfRequestsCanBeAdded()
    {
        $requests = [
            new Request(null, null, 'GET', '/foo'),
            new Request(null, null, 'POST', '/bar', ['foo' => 'bar']),
            new Request(null, null, 'DELETE', '/baz'),
        ];

        $formattedRequests = $this->createBatchRequestWithRequests($requests)->getRequests();

        $this->assertRequestsMatch($requests, $formattedRequests);
    }

    public function testAnAssociativeArrayOfRequestsCanBeAdded()
    {
        $requests = [
            'req-one' => new Request(null, null, 'GET', '/foo'),
            'req-two' => new Request(null, null, 'POST', '/bar', ['foo' => 'bar']),
            'req-three' => new Request(null, null, 'DELETE', '/baz'),
        ];

        $formattedRequests = $this->createBatchRequestWithRequests($requests)->getRequests();

        $this->assertRequestsMatch($requests, $formattedRequests);
    }

    public function testRequestsCanBeInjectedIntoConstructor()
    {
        $requests = [
            new Request(null, null, 'GET', '/foo'),
            new Request(null, null, 'POST', '/bar', ['foo' => 'bar']),
            new Request(null, null, 'DELETE', '/baz'),
        ];

        $batchRequest = new BatchRequest($this->app, $requests, 'foo_token');
        $formattedRequests = $batchRequest->getRequests();

        $this->assertRequestsMatch($requests, $formattedRequests);
    }

    /**
     * @expectedException \Facebook\Exception\SDKException
     */
    public function testAZeroRequestCountWithThrow()
    {
        $batchRequest = new BatchRequest($this->app, [], 'foo_token');

        $batchRequest->validateBatchRequestCount();
    }

    /**
     * @expectedException \Facebook\Exception\SDKException
     */
    public function testMoreThanFiftyRequestsWillThrow()
    {
        $batchRequest = $this->createBatchRequest();

        $this->createAndAppendRequestsTo($batchRequest, 51);

        $batchRequest->validateBatchRequestCount();
    }

    public function testLessOrEqualThanFiftyRequestsWillNotThrow()
    {
        $batchRequest = $this->createBatchRequest();

        $this->createAndAppendRequestsTo($batchRequest, 50);

        $batchRequest->validateBatchRequestCount();

        $this->assertTrue(true);
    }

    /**
     * @dataProvider requestsAndExpectedResponsesProvider
     *
     * @param mixed $request
     * @param mixed $expectedArray
     */
    public function testBatchRequestEntitiesProperlyGetConvertedToAnArray($request, $expectedArray)
    {
        $batchRequest = $this->createBatchRequest();
        $batchRequest->add($request, 'foo_name');

        $requests = $batchRequest->getRequests();
        $batchRequestArray = $batchRequest->requestEntityToBatchArray($requests[0]['request'], $requests[0]['name']);

        $this->assertEquals($expectedArray, $batchRequestArray);
    }

    public function requestsAndExpectedResponsesProvider()
    {
        $headers = $this->defaultHeaders();

        return [
            [
                new Request(null, null, 'GET', '/foo', ['foo' => 'bar']),
                [
                    'headers' => $headers,
                    'method' => 'GET',
                    'relative_url' => '/foo?foo=bar&access_token=foo_token&appsecret_proof=df4256903ba4e23636cc142117aa632133d75c642bd2a68955be1443bd14deb9',
                    'name' => 'foo_name',
                ],
            ],
            [
                new Request(null, null, 'POST', '/bar', ['bar' => 'baz']),
                [
                    'headers' => $headers,
                    'method' => 'POST',
                    'relative_url' => '/bar',
                    'body' => 'bar=baz&access_token=foo_token&appsecret_proof=df4256903ba4e23636cc142117aa632133d75c642bd2a68955be1443bd14deb9',
                    'name' => 'foo_name',
                ],
            ],
            [
                new Request(null, null, 'DELETE', '/bar'),
                [
                    'headers' => $headers,
                    'method' => 'DELETE',
                    'relative_url' => '/bar?access_token=foo_token&appsecret_proof=df4256903ba4e23636cc142117aa632133d75c642bd2a68955be1443bd14deb9',
                    'name' => 'foo_name',
                ],
            ],
        ];
    }

    public function testBatchRequestsWithFilesGetConvertedToAnArray()
    {
        $request = new Request(null, null, 'POST', '/bar', [
            'message' => 'foobar',
            'source' => new File(__DIR__ . '/foo.txt'),
        ]);

        $batchRequest = $this->createBatchRequest();
        $batchRequest->add($request, 'foo_name');

        $requests = $batchRequest->getRequests();

        $attachedFiles = $requests[0]['attached_files'];

        $batchRequestArray = $batchRequest->requestEntityToBatchArray(
            $requests[0]['request'],
            $requests[0]['name'],
            $attachedFiles
        );

        $this->assertEquals([
            'headers' => $this->defaultHeaders(),
            'method' => 'POST',
            'relative_url' => '/bar',
            'body' => 'message=foobar&access_token=foo_token&appsecret_proof=df4256903ba4e23636cc142117aa632133d75c642bd2a68955be1443bd14deb9',
            'name' => 'foo_name',
            'attached_files' => $attachedFiles,
        ], $batchRequestArray);
    }

    public function testBatchRequestsWithOptionsGetConvertedToAnArray()
    {
        $request = new Request(null, null, 'GET', '/bar');
        $batchRequest = $this->createBatchRequest();
        $batchRequest->add($request, [
            'name' => 'foo_name',
            'omit_response_on_success' => false,
        ]);

        $requests = $batchRequest->getRequests();

        $options = $requests[0]['options'];
        $options['name'] = $requests[0]['name'];

        $batchRequestArray = $batchRequest->requestEntityToBatchArray($requests[0]['request'], $options);

        $this->assertEquals([
            'headers' => $this->defaultHeaders(),
            'method' => 'GET',
            'relative_url' => '/bar?access_token=foo_token&appsecret_proof=df4256903ba4e23636cc142117aa632133d75c642bd2a68955be1443bd14deb9',
            'name' => 'foo_name',
            'omit_response_on_success' => false,
        ], $batchRequestArray);
    }

    public function testPreppingABatchRequestProperlySetsThePostParams()
    {
        $batchRequest = $this->createBatchRequest();
        $batchRequest->add(new Request(null, 'bar_token', 'GET', '/foo'), 'foo_name');
        $batchRequest->add(new Request(null, null, 'POST', '/bar', ['foo' => 'bar']));
        $batchRequest->prepareRequestsForBatch();

        $params = $batchRequest->getParams();

        $expectedHeaders = json_encode($this->defaultHeaders());
        $expectedBatchParams = [
            'batch' => '[{"headers":' . $expectedHeaders . ',"method":"GET","relative_url":"\\/foo?access_token=bar_token&appsecret_proof=2ceec40b7b9fd7d38fff1767b766bcc6b1f9feb378febac4612c156e6a8354bd","name":"foo_name"},'
                . '{"headers":' . $expectedHeaders . ',"method":"POST","relative_url":"\\/bar","body":"foo=bar&access_token=foo_token&appsecret_proof=df4256903ba4e23636cc142117aa632133d75c642bd2a68955be1443bd14deb9"}]',
            'include_headers' => true,
            'access_token' => 'foo_token',
            'appsecret_proof' => 'df4256903ba4e23636cc142117aa632133d75c642bd2a68955be1443bd14deb9',
        ];
        $this->assertEquals($expectedBatchParams, $params);
    }

    public function testPreppingABatchRequestProperlyMovesTheFiles()
    {
        $batchRequest = $this->createBatchRequest();
        $batchRequest->add(new Request(null, 'bar_token', 'GET', '/foo'), 'foo_name');
        $batchRequest->add(new Request(null, null, 'POST', '/me/photos', [
            'message' => 'foobar',
            'source' => new File(__DIR__ . '/foo.txt'),
        ]));
        $batchRequest->prepareRequestsForBatch();

        $params = $batchRequest->getParams();
        $files = $batchRequest->getFiles();

        $attachedFiles = implode(',', array_keys($files));

        $expectedHeaders = json_encode($this->defaultHeaders());
        $expectedBatchParams = [
            'batch' => '[{"headers":' . $expectedHeaders . ',"method":"GET","relative_url":"\\/foo?access_token=bar_token&appsecret_proof=2ceec40b7b9fd7d38fff1767b766bcc6b1f9feb378febac4612c156e6a8354bd","name":"foo_name"},'
                . '{"headers":' . $expectedHeaders . ',"method":"POST","relative_url":"\\/me\\/photos","body":"message=foobar&access_token=foo_token&appsecret_proof=df4256903ba4e23636cc142117aa632133d75c642bd2a68955be1443bd14deb9","attached_files":"' . $attachedFiles . '"}]',
            'include_headers' => true,
            'access_token' => 'foo_token',
            'appsecret_proof' => 'df4256903ba4e23636cc142117aa632133d75c642bd2a68955be1443bd14deb9',
        ];
        $this->assertEquals($expectedBatchParams, $params);
    }

    public function testPreppingABatchRequestWithOptionsProperlySetsThePostParams()
    {
        $batchRequest = $this->createBatchRequest();
        $batchRequest->add(new Request(null, null, 'GET', '/foo'), [
            'name' => 'foo_name',
            'omit_response_on_success' => false,
        ]);

        $batchRequest->prepareRequestsForBatch();
        $params = $batchRequest->getParams();

        $expectedHeaders = json_encode($this->defaultHeaders());

        $expectedBatchParams = [
            'batch' => '[{"headers":' . $expectedHeaders . ',"method":"GET","relative_url":"\\/foo?access_token=foo_token&appsecret_proof=df4256903ba4e23636cc142117aa632133d75c642bd2a68955be1443bd14deb9",'
                . '"name":"foo_name","omit_response_on_success":false}]',
            'include_headers' => true,
            'access_token' => 'foo_token',
            'appsecret_proof' => 'df4256903ba4e23636cc142117aa632133d75c642bd2a68955be1443bd14deb9',
        ];
        $this->assertEquals($expectedBatchParams, $params);
    }

    private function assertRequestContainsAppAndToken(Request $request, Application $expectedApp, $expectedToken)
    {
        $app = $request->getApplication();
        $token = $request->getAccessToken();

        $this->assertSame($expectedApp, $app);
        $this->assertEquals($expectedToken, $token);
    }

    private function defaultHeaders()
    {
        $headers = [];
        foreach (Request::getDefaultHeaders() as $name => $value) {
            $headers[] = $name . ': ' . $value;
        }

        return $headers;
    }

    private function createAndAppendRequestsTo(BatchRequest $batchRequest, $number)
    {
        for ($i = 0; $i < $number; $i++) {
            $batchRequest->add(new Request());
        }
    }

    private function createBatchRequest()
    {
        return new BatchRequest($this->app, [], 'foo_token');
    }

    private function createBatchRequestWithRequests(array $requests)
    {
        $batchRequest = $this->createBatchRequest();
        $batchRequest->add($requests);

        return $batchRequest;
    }

    private function assertRequestsMatch($requests, $formattedRequests)
    {
        $expectedRequests = [];
        foreach ($requests as $name => $request) {
            $expectedRequests[] = [
                'name' => $name,
                'request' => $request,
                'attached_files' => null,
                'options' => [],
            ];
        }
        $this->assertEquals($expectedRequests, $formattedRequests);
    }
}
