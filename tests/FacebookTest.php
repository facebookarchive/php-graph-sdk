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

use Facebook\BatchRequest;
use Facebook\Exceptions\FacebookSDKException as FacebookSDKExceptionAlias;
use Facebook\Facebook;
use Facebook\Client;
use Facebook\GraphNodes\GraphUser;
use Facebook\PersistentData\FacebookMemoryPersistentDataHandler;
use Facebook\Request;
use Facebook\Authentication\AccessToken;
use Facebook\GraphNodes\GraphEdge;
use Facebook\Response;
use Facebook\Tests\Fixtures\FakeGraphApiForResumableUpload;
use Facebook\Tests\Fixtures\FooClientInterface;
use Facebook\Tests\Fixtures\FooPersistentDataInterface;
use Facebook\Tests\Fixtures\FooUrlDetectionInterface;
use Facebook\Url\UrlDetectionHandler;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 *
 */
class FacebookTest extends TestCase
{
    protected array $config = [
        'app_id' => '1337',
        'app_secret' => 'foo_secret',
        'default_graph_version' => 'v0.0',
    ];

    public function testInstantiatingWithoutAppIdThrows()
    {
        $this->expectException(FacebookSDKExceptionAlias::class);
        // unset value so there is no fallback to test expected Exception
        putenv(Facebook::APP_ID_ENV_NAME . '=');
        $config = [
            'app_secret' => 'foo_secret',
            'default_graph_version' => 'v0.0',
        ];
        new Facebook($config);
    }

    public function testInstantiatingWithoutAppSecretThrows()
    {
        $this->expectException(FacebookSDKExceptionAlias::class);
        // unset value so there is no fallback to test expected Exception
        putenv(Facebook::APP_SECRET_ENV_NAME . '=');
        $config = [
            'app_id' => 'foo_id',
            'default_graph_version' => 'v0.0',
        ];
        new Facebook($config);
    }

    public function testInstantiatingWithoutDefaultGraphVersionThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $config = [
            'app_id' => 'foo_id',
            'app_secret' => 'foo_secret',
        ];
        new Facebook($config);
    }

    public function testSettingAnInvalidHttpClientTypeThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $config = array_merge($this->config, [
            'http_client' => 'foo_client',
        ]);
        new Facebook($config);
    }


    public function testSettingAnInvalidHttpClientClassThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $config = array_merge($this->config, [
            'http_client' => new \stdClass(),
        ]);
        new Facebook($config);
    }

    public function testSettingAnInvalidPersistentDataHandlerThrows()
    {
        $this->expectException(InvalidArgumentException::class);
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
        static::assertInstanceOf(
            FacebookMemoryPersistentDataHandler::class,
            $fb->getRedirectLoginHelper()->getPersistentDataHandler()
        );
    }

    public function testSettingAnInvalidUrlHandlerThrows(): void
    {
        $this->expectError();
        $config = array_merge($this->config, [
            'url_detection_handler' => 'foo_handler',
        ]);
        new Facebook($config);
    }

    public function testTheUrlHandlerWillDefaultToTheFacebookImplementation()
    {
        $fb = new Facebook($this->config);
        static::assertInstanceOf(UrlDetectionHandler::class, $fb->getUrlDetectionHandler());
    }

    public function testAnAccessTokenCanBeSetAsAString()
    {
        $fb = new Facebook($this->config);
        $fb->setDefaultAccessToken('foo_token');
        $accessToken = $fb->getDefaultAccessToken();

        static::assertInstanceOf(AccessToken::class, $accessToken);
        static::assertEquals('foo_token', (string)$accessToken);
    }

    public function testAnAccessTokenCanBeSetAsAnAccessTokenEntity()
    {
        $fb = new Facebook($this->config);
        $fb->setDefaultAccessToken(new AccessToken('bar_token'));
        $accessToken = $fb->getDefaultAccessToken();

        static::assertInstanceOf(AccessToken::class, $accessToken);
        static::assertEquals('bar_token', (string)$accessToken);
    }

    public function testSettingAnAccessThatIsNotStringOrAccessTokenThrows()
    {
        $this->expectException(InvalidArgumentException::class);
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
        static::assertEquals('1337', $request->getApplication()->getId());
        static::assertEquals('foo_secret', $request->getApplication()->getSecret());
        static::assertEquals('foo_token', (string)$request->getAccessToken());
        static::assertEquals('v1337', $request->getGraphVersion());
        static::assertEquals(
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
        static::assertEquals('1337', $batchRequest->getApplication()->getId());
        static::assertEquals('foo_secret', $batchRequest->getApplication()->getSecret());
        static::assertEquals('foo_token', (string)$batchRequest->getAccessToken());
        static::assertEquals('v1337', $batchRequest->getGraphVersion());
        static::assertEquals(
            Client::BASE_GRAPH_URL_BETA,
            $fb->getClient()->getBaseGraphUrl()
        );
        static::assertInstanceOf(BatchRequest::class, $batchRequest);
        static::assertCount(0, $batchRequest->getRequests());
    }

    public function testCanInjectCustomHandlers()
    {
        $config = array_merge($this->config, [
            'http_client_handler' => new FooClientInterface(),
            'persistent_data_handler' => new FooPersistentDataInterface(),
            'url_detection_handler' => new FooUrlDetectionInterface(),
        ]);
        $fb = new Facebook($config);

        static::assertInstanceOf(
            FooClientInterface::class,
            $fb->getClient()->getHttpCllient()
        );
        static::assertInstanceOf(
            FooPersistentDataInterface::class,
            $fb->getRedirectLoginHelper()->getPersistentDataHandler()
        );
        static::assertInstanceOf(
            FooUrlDetectionInterface::class,
            $fb->getRedirectLoginHelper()->getUrlDetectionHandler()
        );
    }

    public function testPaginationReturnsProperResponse()
    {
        $config = array_merge($this->config, [
            'http_client' => new FooClientInterface(),
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
        static::assertInstanceOf(GraphEdge::class, $nextPage);
        static::assertInstanceOf(GraphUser::class, $nextPage[0]);
        static::assertEquals('Foo', $nextPage[0]['name']);

        $lastResponse = $fb->getLastResponse();
        static::assertInstanceOf(Response::class, $lastResponse);
        static::assertEquals(1337, $lastResponse->getHttpStatusCode());
    }

    public function testCanGetSuccessfulTransferWithMaxTries()
    {
        $config = array_merge($this->config, [
            'http_client' => new FakeGraphApiForResumableUpload(),
        ]);
        $fb = new Facebook($config);
        $response = $fb->uploadVideo('me', __DIR__ . '/foo.txt', [], 'foo-token', 3);
        static::assertEquals([
            'video_id' => '1337',
            'success' => true,
        ], $response);
    }

    public function testMaxingOutRetriesWillThrow(): void
    {
        $this->expectException(\Facebook\Exceptions\FacebookResponseException::class);
        $client = new FakeGraphApiForResumableUpload();
        $client->failOnTransfer();

        $config = array_merge($this->config, [
            'http_client' => $client,
        ]);
        $fb = new Facebook($config);
        $fb->uploadVideo('4', __DIR__ . '/foo.txt', [], 'foo-token', 3);
    }
}
