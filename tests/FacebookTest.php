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

use Facebook\Facebook;
use Facebook\Client;
use Facebook\Request;
use Facebook\Authentication\AccessToken;
use Facebook\GraphNode\GraphEdge;
use Facebook\Tests\Fixtures\FakeGraphApiForResumableUpload;
use Facebook\Tests\Fixtures\FooHttpClientInterface;
use Facebook\Tests\Fixtures\FooPersistentDataInterface;
use Facebook\Tests\Fixtures\FooUrlDetectionInterface;
use Facebook\HttpClients\CurlHttpClient;
use Facebook\HttpClients\StreamHttpClient;
use Facebook\HttpClients\GuzzleHttpClient;
use Facebook\PersistentData\InMemoryPersistentDataHandler;
use Facebook\Url\UrlDetectionHandler;
use Facebook\Response;
use Facebook\GraphNode\GraphUser;
use PHPUnit\Framework\Error\Error;
use PHPUnit\Framework\TestCase;

class Test extends TestCase
{
    protected $config = [
        'app_id' => '1337',
        'app_secret' => 'foo_secret',
        'default_graph_version' => 'v0.0',
    ];

    /**
     * @expectedException \Facebook\Exception\SDKException
     */
    public function testInstantiatingWithoutAppIdThrows()
    {
        // unset value so there is no fallback to test expected Exception
        putenv(Facebook::APP_ID_ENV_NAME.'=');
        $config = [
            'app_secret' => 'foo_secret',
            'default_graph_version' => 'v0.0',
        ];
        new Facebook($config);
    }

    /**
     * @expectedException \Facebook\Exception\SDKException
     */
    public function testInstantiatingWithoutAppSecretThrows()
    {
        // unset value so there is no fallback to test expected Exception
        putenv(Facebook::APP_SECRET_ENV_NAME.'=');
        $config = [
            'app_id' => 'foo_id',
            'default_graph_version' => 'v0.0',
        ];
        new Facebook($config);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInstantiatingWithoutDefaultGraphVersionThrows()
    {
        $config = [
            'app_id' => 'foo_id',
            'app_secret' => 'foo_secret',
        ];
        new Facebook($config);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSettingAnInvalidHttpClientTypeThrows()
    {
        $config = array_merge($this->config, [
            'http_client' => 'foo_client',
        ]);
        new Facebook($config);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSettingAnInvalidHttpClientClassThrows()
    {
        $config = array_merge($this->config, [
            'http_client' => new \stdClass(),
        ]);
        new Facebook($config);
    }
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSettingAnInvalidPersistentDataHandlerThrows()
    {
        $config = array_merge($this->config, [
            'persistent_data_handler' => 'foo_handler',
        ]);
        new Facebook($config);
    }

    public function testPersistentDataHandlerCanBeForced()
    {
        $config = array_merge($this->config, [
            'persistent_data_handler' => 'memory'
        ]);
        $fb = new Facebook($config);
        $this->assertInstanceOf(
            InMemoryPersistentDataHandler::class,
            $fb->getRedirectLoginHelper()->getPersistentDataHandler()
        );
    }

    /**
     * @expectedException Error
     */
    public function testSettingAnInvalidUrlHandlerThrows()
    {
        $config = array_merge($this->config, [
            'url_detection_handler' => 'foo_handler',
        ]);
        new Facebook($config);
    }

    public function testTheUrlHandlerWillDefaultToTheImplementation()
    {
        $fb = new Facebook($this->config);
        $this->assertInstanceOf(UrlDetectionHandler::class, $fb->getUrlDetectionHandler());
    }

    public function testAnAccessTokenCanBeSetAsAString()
    {
        $fb = new Facebook($this->config);
        $fb->setDefaultAccessToken('foo_token');
        $accessToken = $fb->getDefaultAccessToken();

        $this->assertInstanceOf(AccessToken::class, $accessToken);
        $this->assertEquals('foo_token', (string)$accessToken);
    }

    public function testAnAccessTokenCanBeSetAsAnAccessTokenEntity()
    {
        $fb = new Facebook($this->config);
        $fb->setDefaultAccessToken(new AccessToken('bar_token'));
        $accessToken = $fb->getDefaultAccessToken();

        $this->assertInstanceOf(AccessToken::class, $accessToken);
        $this->assertEquals('bar_token', (string)$accessToken);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSettingAnAccessThatIsNotStringOrAccessTokenThrows()
    {
        $config = array_merge($this->config, [
            'default_access_token' => 123,
        ]);
        new Facebook($config);
    }

    public function testCreatingANewRequestWillDefaultToTheProperConfig()
    {
        $config = array_merge($this->config, [
            'default_access_token' => 'foo_token',
            'enable_beta_mode' => true,
            'default_graph_version' => 'v1337',
        ]);
        $fb = new Facebook($config);

        $request = $fb->request('FOO_VERB', '/foo');
        $this->assertEquals('1337', $request->getApplication()->getId());
        $this->assertEquals('foo_secret', $request->getApplication()->getSecret());
        $this->assertEquals('foo_token', (string)$request->getAccessToken());
        $this->assertEquals('v1337', $request->getGraphVersion());
        $this->assertEquals(
            Client::BASE_GRAPH_URL_BETA,
            $fb->getClient()->getBaseGraphUrl()
        );
    }

    public function testCreatingANewBatchRequestWillDefaultToTheProperConfig()
    {
        $config = array_merge($this->config, [
            'default_access_token' => 'foo_token',
            'enable_beta_mode' => true,
            'default_graph_version' => 'v1337',
        ]);
        $fb = new Facebook($config);

        $batchRequest = $fb->newBatchRequest();
        $this->assertEquals('1337', $batchRequest->getApplication()->getId());
        $this->assertEquals('foo_secret', $batchRequest->getApplication()->getSecret());
        $this->assertEquals('foo_token', (string)$batchRequest->getAccessToken());
        $this->assertEquals('v1337', $batchRequest->getGraphVersion());
        $this->assertEquals(
            Client::BASE_GRAPH_URL_BETA,
            $fb->getClient()->getBaseGraphUrl()
        );
        $this->assertInstanceOf('Facebook\BatchRequest', $batchRequest);
        $this->assertCount(0, $batchRequest->getRequests());
    }

    public function testCanInjectCustomHandlers()
    {
        $config = array_merge($this->config, [
            'http_client' => new FooHttpClientInterface(),
            'persistent_data_handler' => new FooPersistentDataInterface(),
            'url_detection_handler' => new FooUrlDetectionInterface(),
        ]);
        $fb = new Facebook($config);

        $this->assertInstanceOf(
            FooHttpClientInterface::class,
            $fb->getClient()->getHttpClient()
        );
        $this->assertInstanceOf(
            FooPersistentDataInterface::class,
            $fb->getRedirectLoginHelper()->getPersistentDataHandler()
        );
        $this->assertInstanceOf(
            FooUrlDetectionInterface::class,
            $fb->getRedirectLoginHelper()->getUrlDetectionHandler()
        );
    }

    public function testPaginationReturnsProperResponse()
    {
        $config = array_merge($this->config, [
            'http_client' => new FooHttpClientInterface(),
        ]);
        $fb = new Facebook($config);

        $request = new Request($fb->getApplication(), 'foo_token', 'GET');
        $graphEdge = new GraphEdge(
            $request,
            [],
            [
                'paging' => [
                    'cursors' => [
                        'after' => 'bar_after_cursor',
                        'before' => 'bar_before_cursor',
                    ],
                    'previous' => 'previous_url',
                    'next' => 'next_url',
                ]
            ],
            '/1337/photos',
            GraphUser::class
        );

        $nextPage = $fb->next($graphEdge);
        $this->assertInstanceOf(GraphEdge::class, $nextPage);
        $this->assertInstanceOf(GraphUser::class, $nextPage[0]);
        $this->assertEquals('Foo', $nextPage[0]->getField('name'));

        $lastResponse = $fb->getLastResponse();
        $this->assertInstanceOf(Response::class, $lastResponse);
        $this->assertEquals(1337, $lastResponse->getHttpStatusCode());
    }

    public function testCanGetSuccessfulTransferWithMaxTries()
    {
        $config = array_merge($this->config, [
          'http_client' => new FakeGraphApiForResumableUpload(),
        ]);
        $fb = new Facebook($config);
        $response = $fb->uploadVideo('me', __DIR__.'/foo.txt', [], 'foo-token', 3);
        $this->assertEquals([
          'video_id' => '1337',
          'success' => true,
        ], $response);
    }

    /**
     * @expectedException \Facebook\Exception\ResponseException
     */
    public function testMaxingOutRetriesWillThrow()
    {
        $client = new FakeGraphApiForResumableUpload();
        $client->failOnTransfer();

        $config = array_merge($this->config, [
          'http_client' => $client,
        ]);
        $fb = new Facebook($config);
        $fb->uploadVideo('4', __DIR__.'/foo.txt', [], 'foo-token', 3);
    }
}
