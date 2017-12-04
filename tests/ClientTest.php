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

use Facebook\Exception\SDKException;
use Facebook\Application;
use Facebook\Request;
use Facebook\BatchRequest;
use Facebook\Client;
use Facebook\FileUpload\File;
use Facebook\FileUpload\Video;
// These are needed when you uncomment the HTTP clients below.
use Facebook\HttpClients\FacebookCurlHttpClient;
use Facebook\HttpClients\FacebookGuzzleHttpClient;
use Facebook\HttpClients\FacebookStreamHttpClient;
use Facebook\Tests\Fixtures\MyFooBatchHttpClient;
use Facebook\Tests\Fixtures\MyFooHttpClient;
use Facebook\Response;
use Facebook\BatchResponse;
use Facebook\GraphNode\GraphNode;
use Http\Client\HttpClient;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    /**
     * @var Application
     */
    public $fbApp;

    /**
     * @var Client
     */
    public $fbClient;

    /**
     * @var Application
     */
    public static $testFacebookApp;

    /**
     * @var Client
     */
    public static $testFacebookClient;

    protected function setUp()
    {
        $this->fbApp = new Application('id', 'shhhh!');
        $this->fbClient = new Client(new MyFooHttpClient());
    }

    public function testACustomHttpClientCanBeInjected()
    {
        $handler = new MyFooHttpClient();
        $client = new Client($handler);
        $httpClient = $client->getHttpClient();

        $this->assertInstanceOf(MyFooHttpClient::class, $httpClient);
    }

    public function testTheHttpClientWillFallbackToDefault()
    {
        $client = new Client();
        $httpClient = $client->getHttpClient();

        $this->assertInstanceOf(HttpClient::class, $httpClient);
    }

    public function testBetaModeCanBeDisabledOrEnabledViaConstructor()
    {
        $client = new Client(null, false);
        $url = $client->getBaseGraphUrl();
        $this->assertEquals(Client::BASE_GRAPH_URL, $url);

        $client = new Client(null, true);
        $url = $client->getBaseGraphUrl();
        $this->assertEquals(Client::BASE_GRAPH_URL_BETA, $url);
    }

    public function testBetaModeCanBeDisabledOrEnabledViaMethod()
    {
        $client = new Client();
        $client->enableBetaMode(false);
        $url = $client->getBaseGraphUrl();
        $this->assertEquals(Client::BASE_GRAPH_URL, $url);

        $client->enableBetaMode(true);
        $url = $client->getBaseGraphUrl();
        $this->assertEquals(Client::BASE_GRAPH_URL_BETA, $url);
    }

    public function testGraphVideoUrlCanBeSet()
    {
        $client = new Client();
        $client->enableBetaMode(false);
        $url = $client->getBaseGraphUrl($postToVideoUrl = true);
        $this->assertEquals(Client::BASE_GRAPH_VIDEO_URL, $url);

        $client->enableBetaMode(true);
        $url = $client->getBaseGraphUrl($postToVideoUrl = true);
        $this->assertEquals(Client::BASE_GRAPH_VIDEO_URL_BETA, $url);
    }

    public function testAFacebookRequestEntityCanBeUsedToSendARequestToGraph()
    {
        $fbRequest = new Request($this->fbApp, 'token', 'GET', '/foo');
        $response = $this->fbClient->sendRequest($fbRequest);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->getHttpStatusCode());
        $this->assertEquals('{"data":[{"id":"123","name":"Foo"},{"id":"1337","name":"Bar"}]}', $response->getBody());
    }

    public function testAFacebookBatchRequestEntityCanBeUsedToSendABatchRequestToGraph()
    {
        $fbRequests = [
            new Request($this->fbApp, 'token', 'GET', '/foo'),
            new Request($this->fbApp, 'token', 'POST', '/bar'),
        ];
        $fbBatchRequest = new BatchRequest($this->fbApp, $fbRequests);

        $fbBatchClient = new Client(new MyFooBatchHttpClient());
        $response = $fbBatchClient->sendBatchRequest($fbBatchRequest);

        $this->assertInstanceOf(BatchResponse::class, $response);
        $this->assertEquals('GET', $response[0]->getRequest()->getMethod());
        $this->assertEquals('POST', $response[1]->getRequest()->getMethod());
    }

    public function testAFacebookBatchRequestWillProperlyBatchFiles()
    {
        $fbRequests = [
            new Request($this->fbApp, 'token', 'POST', '/photo', [
                'message' => 'foobar',
                'source' => new File(__DIR__ . '/foo.txt'),
            ]),
            new Request($this->fbApp, 'token', 'POST', '/video', [
                'message' => 'foobar',
                'source' => new Video(__DIR__ . '/foo.txt'),
            ]),
        ];
        $fbBatchRequest = new BatchRequest($this->fbApp, $fbRequests);
        $fbBatchRequest->prepareRequestsForBatch();

        list($url, $method, $headers, $body) = $this->fbClient->prepareRequestMessage($fbBatchRequest);

        $this->assertEquals(Client::BASE_GRAPH_VIDEO_URL, $url);
        $this->assertEquals('POST', $method);
        $this->assertContains('multipart/form-data; boundary=', $headers['Content-Type']);
        $this->assertContains('Content-Disposition: form-data; name="batch"', $body);
        $this->assertContains('Content-Disposition: form-data; name="include_headers"', $body);
        $this->assertContains('"name":0,"attached_files":', $body);
        $this->assertContains('"name":1,"attached_files":', $body);
        $this->assertContains('"; filename="foo.txt"', $body);
    }

    public function testARequestOfParamsWillBeUrlEncoded()
    {
        $fbRequest = new Request($this->fbApp, 'token', 'POST', '/foo', ['foo' => 'bar']);
        $response = $this->fbClient->sendRequest($fbRequest);

        $headersSent = $response->getRequest()->getHeaders();

        $this->assertEquals('application/x-www-form-urlencoded', $headersSent['Content-Type']);
    }

    public function testARequestWithFilesWillBeMultipart()
    {
        $myFile = new File(__DIR__ . '/foo.txt');
        $fbRequest = new Request($this->fbApp, 'token', 'POST', '/foo', ['file' => $myFile]);
        $response = $this->fbClient->sendRequest($fbRequest);

        $headersSent = $response->getRequest()->getHeaders();

        $this->assertContains('multipart/form-data; boundary=', $headersSent['Content-Type']);
    }

    /**
     * @expectedException \Facebook\Exception\SDKException
     */
    public function testAFacebookRequestValidatesTheAccessTokenWhenOneIsNotProvided()
    {
        $fbRequest = new Request($this->fbApp, null, 'GET', '/foo');
        $this->fbClient->sendRequest($fbRequest);
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

        $request = new Request(
            static::$testFacebookApp,
            static::$testFacebookApp->getAccessToken(),
            'POST',
            $testUserPath,
            $params
        );
        $response = static::$testFacebookClient->sendRequest($request)->getGraphNode();

        $testUserId = $response->getField('id');
        $testUserAccessToken = $response->getField('access_token');

        // Get the test user's profile
        $request = new Request(
            static::$testFacebookApp,
            $testUserAccessToken,
            'GET',
            '/me'
        );
        $graphNode = static::$testFacebookClient->sendRequest($request)->getGraphNode();

        $this->assertInstanceOf(GraphNode::class, $graphNode);
        $this->assertNotNull($graphNode->getField('id'));
        $this->assertEquals('Foo Phpunit User', $graphNode->getField('name'));

        // Delete test user
        $request = new Request(
            static::$testFacebookApp,
            static::$testFacebookApp->getAccessToken(),
            'DELETE',
            '/' . $testUserId
        );
        $graphNode = static::$testFacebookClient->sendRequest($request)->getGraphNode();

        $this->assertTrue($graphNode->getField('success'));
    }

    public function initializeTestApp()
    {
        if (!file_exists(__DIR__ . '/FacebookTestCredentials.php')) {
            throw new SDKException(
                'You must create a FacebookTestCredentials.php file from FacebookTestCredentials.php.dist'
            );
        }

        if (!strlen(FacebookTestCredentials::$appId) ||
            !strlen(FacebookTestCredentials::$appSecret)
        ) {
            throw new SDKException(
                'You must fill out FacebookTestCredentials.php'
            );
        }
        static::$testFacebookApp = new Application(
            FacebookTestCredentials::$appId,
            FacebookTestCredentials::$appSecret
        );

        static::$testFacebookClient = new Client();
    }
}
