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

use Facebook\BatchResponse;
use Facebook\Exceptions\FacebookSDKException;
use Facebook\Application;
use Facebook\GraphNodes\GraphNode;
use Facebook\Request;
use Facebook\BatchRequest;
use Facebook\Client;
use Facebook\FileUpload\File;
use Facebook\FileUpload\Video;
// These are needed when you uncomment the HTTP clients below.
use Facebook\Response;
use Facebook\Tests\Fixtures\MyFooBatchClientHandler;
use Facebook\Tests\Fixtures\MyFooClientHandler;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Symfony\Component\HttpClient\HttpClient;

/**
 * Class ClientTest
 */
class ClientTest extends TestCase
{
    /**
     * @var Application
     */
    public Application $fbApp;

    /**
     * @var Client
     */
    public Client $fbClient;

    /**
     * @var Application
     */
    public static Application $testFacebookApp;

    /**
     * @var Client
     */
    public static Client $testFacebookClient;

    protected function setUp(): void
    {
        $this->fbApp = new Application('id', 'shhhh!');
        $this->fbClient = new Client(new MyFooClientHandler());
    }

    public function testACustomHttpClientCanBeInjected(): void
    {
        $handler = new MyFooClientHandler();
        $client = new Client($handler);
        $httpHandler = $client->getHttpCllient();

        static::assertInstanceOf(MyFooClientHandler::class, $httpHandler);
    }

    public function testTheHttpClientWillFallbackToDefault(): void
    {
        $client = new Client();
        $httpHandler = $client->getHttpCllient();

        static::assertInstanceOf(ClientInterface::class, $httpHandler);
    }

    public function testBetaModeCanBeDisabledOrEnabledViaConstructor(): void
    {
        $client = new Client(null, false);
        $url = $client->getBaseGraphUrl();
        static::assertEquals(Client::BASE_GRAPH_URL, $url);

        $client = new Client(null, true);
        $url = $client->getBaseGraphUrl();
        static::assertEquals(Client::BASE_GRAPH_URL_BETA, $url);
    }

    public function testBetaModeCanBeDisabledOrEnabledViaMethod(): void
    {
        $client = new Client();
        $client->enableBetaMode(false);
        $url = $client->getBaseGraphUrl();
        static::assertEquals(Client::BASE_GRAPH_URL, $url);

        $client->enableBetaMode(true);
        $url = $client->getBaseGraphUrl();
        static::assertEquals(Client::BASE_GRAPH_URL_BETA, $url);
    }

    public function testGraphVideoUrlCanBeSet(): void
    {
        $client = new Client();
        $client->enableBetaMode(false);
        $url = $client->getBaseGraphUrl(true);
        static::assertEquals(Client::BASE_GRAPH_VIDEO_URL, $url);

        $client->enableBetaMode(true);
        $url = $client->getBaseGraphUrl(true);
        static::assertEquals(Client::BASE_GRAPH_VIDEO_URL_BETA, $url);
    }

    public function testAFacebookRequestEntityCanBeUsedToSendARequestToGraph(): void
    {
        $fbRequest = new Request($this->fbApp, 'token', 'GET', '/foo', ['verify' => false]);
        $response = $this->fbClient->sendRequest($fbRequest);

        static::assertInstanceOf(Response::class, $response);
        static::assertEquals(200, $response->getHttpStatusCode());
        static::assertEquals('{"data":[{"id":"123","name":"Foo"},{"id":"1337","name":"Bar"}]}', $response->getBody());
    }

    public function testAFacebookBatchRequestEntityCanBeUsedToSendABatchRequestToGraph(): void
    {
        $fbRequests = [
            new Request($this->fbApp, 'token', 'GET', '/foo'),
            new Request($this->fbApp, 'token', 'POST', '/bar'),
        ];
        $fbBatchRequest = new BatchRequest($this->fbApp, $fbRequests);

        $fbBatchClient = new Client(new MyFooBatchClientHandler());
        $response = $fbBatchClient->sendBatchRequest($fbBatchRequest);

        static::assertInstanceOf(BatchResponse::class, $response);
        static::assertEquals('GET', $response[0]->getRequest()->getMethod());
        static::assertEquals('POST', $response[1]->getRequest()->getMethod());
    }

    public function testAFacebookBatchRequestWillProperlyBatchFiles(): void
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

        static::assertEquals(Client::BASE_GRAPH_VIDEO_URL, $url);
        static::assertEquals('POST', $method);
        static::assertStringContainsString('multipart/form-data; boundary=', $headers['Content-Type']);
        static::assertStringContainsString('Content-Disposition: form-data; name="batch"', $body);
        static::assertStringContainsString('Content-Disposition: form-data; name="include_headers"', $body);
        static::assertStringContainsString('"name":0,"attached_files":', $body);
        static::assertStringContainsString('"name":1,"attached_files":', $body);
        static::assertStringContainsString('"; filename="foo.txt"', $body);
    }

    public function testARequestOfParamsWillBeUrlEncoded(): void
    {
        $fbRequest = new Request($this->fbApp, 'token', 'POST', '/foo', ['foo' => 'bar']);
        $response = $this->fbClient->sendRequest($fbRequest);

        $headersSent = $response->getRequest()->getHeaders();

        static::assertEquals('application/x-www-form-urlencoded', $headersSent['Content-Type']);
    }

    public function testARequestWithFilesWillBeMultipart(): void
    {
        $myFile = new File(__DIR__ . '/foo.txt');
        $fbRequest = new Request($this->fbApp, 'token', 'POST', '/foo', ['file' => $myFile]);
        $response = $this->fbClient->sendRequest($fbRequest);

        $headersSent = $response->getRequest()->getHeaders();

        static::assertStringContainsString('multipart/form-data; boundary=', $headersSent['Content-Type']);
    }

    public function testAFacebookRequestValidatesTheAccessTokenWhenOneIsNotProvided(): void
    {
        $this->expectException(FacebookSDKException::class);

        $fbRequest = new Request($this->fbApp, null, 'GET', '/foo');
        $this->fbClient->sendRequest($fbRequest);
    }

    /**
     * @group integration
     */
    public function testCanCreateATestUserAndGetTheProfileAndThenDeleteTheTestUser(): void
    {
        $this->initializeTestApp();

        // Create a test user
        $testUserPath = '/' . FacebookTestCredential::$appId . '/accounts/test-users';
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

        static::assertInstanceOf(GraphNode::class, $graphNode);
        static::assertNotNull($graphNode->getField('id'));
        static::assertEquals('Foo Phpunit User', $graphNode->getField('name'));

        // Delete test user
        $request = new Request(
            static::$testFacebookApp,
            static::$testFacebookApp->getAccessToken(),
            'DELETE',
            '/' . $testUserId
        );
        $graphNode = static::$testFacebookClient->sendRequest($request)->getGraphNode();

        static::assertTrue($graphNode->getField('success'));
    }

    public function initializeTestApp()
    {
        if (!file_exists(__DIR__ . '/FacebookTestCredentials.php')) {
            throw new FacebookSDKException(
                'You must create a FacebookTestCredentials.php file from FacebookTestCredentials.php.dist'
            );
        }

        if (!strlen(FacebookTestCredentials::$appId) ||
            !strlen(FacebookTestCredentials::$appSecret)
        ) {
            throw new FacebookSDKException(
                'You must fill out FacebookTestCredentials.php'
            );
        }
        static::$testFacebookApp = new Application(
            FacebookTestCredentials::$appId,
            FacebookTestCredentials::$appSecret
        );

        // Use default client
        $client = null;

        // Uncomment to enable curl implementation.
        //$client = new FacebookCurlHttpClient();

        // Uncomment to enable stream wrapper implementation.
        //$client = new FacebookStreamHttpClient();

        // Uncomment to enable Guzzle implementation.
        //$client = new FacebookGuzzleHttpClient();

        static::$testFacebookClient = new Client($client);
    }
}
