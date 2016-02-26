<?php
/**
 * Copyright 2016 Facebook, Inc.
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

use Facebook\Facebook;
use Facebook\FacebookClient;
use Facebook\Http\GraphRawResponse;
use Facebook\HttpClients\FacebookHttpClientInterface;
use Facebook\PersistentData\PersistentDataInterface;
use Facebook\Url\UrlDetectionInterface;
use Facebook\PseudoRandomString\PseudoRandomStringGeneratorInterface;
use Facebook\FacebookRequest;
use Facebook\Authentication\AccessToken;
use Facebook\GraphNodes\GraphEdge;
use Facebook\Tests\FakeGraphApi\FakeGraphApiForResumableUpload;

class FooClientInterface implements FacebookHttpClientInterface
{
    public function send($url, $method, $body, array $headers, $timeOut)
    {
        return new GraphRawResponse(
            "HTTP/1.1 1337 OK\r\nDate: Mon, 19 May 2014 18:37:17 GMT",
            '{"data":[{"id":"123","name":"Foo"},{"id":"1337","name":"Bar"}]}'
        );
    }
}

class FooPersistentDataInterface implements PersistentDataInterface
{
    public function get($key)
    {
        return 'foo';
    }

    public function set($key, $value)
    {
    }
}

class FooUrlDetectionInterface implements UrlDetectionInterface
{
    public function getCurrentUrl()
    {
        return 'https://foo.bar';
    }
}

class FooBarPseudoRandomStringGenerator implements PseudoRandomStringGeneratorInterface
{
    public function getPseudoRandomString($length)
    {
        return 'csprs123';
    }
}

class FacebookTest extends \PHPUnit_Framework_TestCase
{
    protected $config = [
        'app_id' => '1337',
        'app_secret' => 'foo_secret',
    ];

    /**
     * @expectedException \Facebook\Exceptions\FacebookSDKException
     */
    public function testInstantiatingWithoutAppIdThrows()
    {
        // unset value so there is no fallback to test expected Exception
        putenv(Facebook::APP_ID_ENV_NAME.'=');
        $config = [
            'app_secret' => 'foo_secret',
        ];
        $fb = new Facebook($config);
    }

    /**
     * @expectedException \Facebook\Exceptions\FacebookSDKException
     */
    public function testInstantiatingWithoutAppSecretThrows()
    {
        // unset value so there is no fallback to test expected Exception
        putenv(Facebook::APP_SECRET_ENV_NAME.'=');
        $config = [
            'app_id' => 'foo_id',
        ];
        $fb = new Facebook($config);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSettingAnInvalidHttpClientHandlerThrows()
    {
        $config = array_merge($this->config, [
            'http_client_handler' => 'foo_handler',
        ]);
        $fb = new Facebook($config);
    }

    public function testCurlHttpClientHandlerCanBeForced()
    {
        $config = array_merge($this->config, [
            'http_client_handler' => 'curl'
        ]);
        $fb = new Facebook($config);
        $this->assertInstanceOf(
            'Facebook\HttpClients\FacebookCurlHttpClient',
            $fb->getClient()->getHttpClientHandler()
        );
    }

    public function testStreamHttpClientHandlerCanBeForced()
    {
        $config = array_merge($this->config, [
            'http_client_handler' => 'stream'
        ]);
        $fb = new Facebook($config);
        $this->assertInstanceOf(
            'Facebook\HttpClients\FacebookStreamHttpClient',
            $fb->getClient()->getHttpClientHandler()
        );
    }

    public function testGuzzleHttpClientHandlerCanBeForced()
    {
        $config = array_merge($this->config, [
            'http_client_handler' => 'guzzle'
        ]);
        $fb = new Facebook($config);
        $this->assertInstanceOf(
            'Facebook\HttpClients\FacebookGuzzleHttpClient',
            $fb->getClient()->getHttpClientHandler()
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSettingAnInvalidPersistentDataHandlerThrows()
    {
        $config = array_merge($this->config, [
            'persistent_data_handler' => 'foo_handler',
        ]);
        $fb = new Facebook($config);
    }

    public function testPersistentDataHandlerCanBeForced()
    {
        $config = array_merge($this->config, [
            'persistent_data_handler' => 'memory'
        ]);
        $fb = new Facebook($config);
        $this->assertInstanceOf(
            'Facebook\PersistentData\FacebookMemoryPersistentDataHandler',
            $fb->getRedirectLoginHelper()->getPersistentDataHandler()
        );
    }

    public function testSettingAnInvalidUrlHandlerThrows()
    {
        $expectedException = (PHP_MAJOR_VERSION > 5 && class_exists('TypeError'))
            ? 'TypeError'
            : 'PHPUnit_Framework_Error';

        $this->setExpectedException($expectedException);

        $config = array_merge($this->config, [
            'url_detection_handler' => 'foo_handler',
        ]);
        $fb = new Facebook($config);
    }

    public function testTheUrlHandlerWillDefaultToTheFacebookImplementation()
    {
        $fb = new Facebook($this->config);
        $this->assertInstanceOf('Facebook\Url\FacebookUrlDetectionHandler', $fb->getUrlDetectionHandler());
    }

    public function testAnAccessTokenCanBeSetAsAString()
    {
        $fb = new Facebook($this->config);
        $fb->setDefaultAccessToken('foo_token');
        $accessToken = $fb->getDefaultAccessToken();

        $this->assertInstanceOf('Facebook\Authentication\AccessToken', $accessToken);
        $this->assertEquals('foo_token', (string)$accessToken);
    }

    public function testAnAccessTokenCanBeSetAsAnAccessTokenEntity()
    {
        $fb = new Facebook($this->config);
        $fb->setDefaultAccessToken(new AccessToken('bar_token'));
        $accessToken = $fb->getDefaultAccessToken();

        $this->assertInstanceOf('Facebook\Authentication\AccessToken', $accessToken);
        $this->assertEquals('bar_token', (string)$accessToken);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSettingAnInvalidPseudoRandomStringGeneratorThrows()
    {
        $config = array_merge($this->config, [
            'pseudo_random_string_generator' => 'foo_generator',
        ]);
        new Facebook($config);
    }

    public function testMcryptCsprgCanBeForced()
    {
        if (!function_exists('mcrypt_create_iv')) {
            $this->markTestSkipped(
                'Mcrypt must be installed to test mcrypt_create_iv().'
            );
        }

        $config = array_merge($this->config, [
            'persistent_data_handler' => 'memory', // To keep session errors from happening
            'pseudo_random_string_generator' => 'mcrypt'
        ]);
        $fb = new Facebook($config);
        $this->assertInstanceOf(
            'Facebook\PseudoRandomString\McryptPseudoRandomStringGenerator',
            $fb->getRedirectLoginHelper()->getPseudoRandomStringGenerator()
        );
    }

    public function testOpenSslCsprgCanBeForced()
    {
        if (!function_exists('openssl_random_pseudo_bytes')) {
            $this->markTestSkipped(
                'The OpenSSL extension must be enabled to test openssl_random_pseudo_bytes().'
            );
        }

        $config = array_merge($this->config, [
            'persistent_data_handler' => 'memory', // To keep session errors from happening
            'pseudo_random_string_generator' => 'openssl'
        ]);
        $fb = new Facebook($config);
        $this->assertInstanceOf(
            'Facebook\PseudoRandomString\OpenSslPseudoRandomStringGenerator',
            $fb->getRedirectLoginHelper()->getPseudoRandomStringGenerator()
        );
    }

    public function testUrandomCsprgCanBeForced()
    {
        if (ini_get('open_basedir')) {
            $this->markTestSkipped(
                'Cannot test /dev/urandom generator due to open_basedir constraint.'
            );
        }

        if (!is_readable('/dev/urandom')) {
            $this->markTestSkipped(
                '/dev/urandom not found or is not readable.'
            );
        }

        $config = array_merge($this->config, [
            'persistent_data_handler' => 'memory', // To keep session errors from happening
            'pseudo_random_string_generator' => 'urandom'
        ]);
        $fb = new Facebook($config);
        $this->assertInstanceOf(
            'Facebook\PseudoRandomString\UrandomPseudoRandomStringGenerator',
            $fb->getRedirectLoginHelper()->getPseudoRandomStringGenerator()
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSettingAnAccessThatIsNotStringOrAccessTokenThrows()
    {
        $config = array_merge($this->config, [
            'default_access_token' => 123,
        ]);
        $fb = new Facebook($config);
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
        $this->assertEquals('1337', $request->getApp()->getId());
        $this->assertEquals('foo_secret', $request->getApp()->getSecret());
        $this->assertEquals('foo_token', (string)$request->getAccessToken());
        $this->assertEquals('v1337', $request->getGraphVersion());
        $this->assertEquals(
            FacebookClient::BASE_GRAPH_URL_BETA,
            $fb->getClient()->getBaseGraphUrl()
        );
    }

    public function testCanInjectCustomHandlers()
    {
        $config = array_merge($this->config, [
            'http_client_handler' => new FooClientInterface(),
            'persistent_data_handler' => new FooPersistentDataInterface(),
            'url_detection_handler' => new FooUrlDetectionInterface(),
            'pseudo_random_string_generator' => new FooBarPseudoRandomStringGenerator(),
        ]);
        $fb = new Facebook($config);

        $this->assertInstanceOf(
            'Facebook\Tests\FooClientInterface',
            $fb->getClient()->getHttpClientHandler()
        );
        $this->assertInstanceOf(
            'Facebook\Tests\FooPersistentDataInterface',
            $fb->getRedirectLoginHelper()->getPersistentDataHandler()
        );
        $this->assertInstanceOf(
            'Facebook\Tests\FooUrlDetectionInterface',
            $fb->getRedirectLoginHelper()->getUrlDetectionHandler()
        );
        $this->assertInstanceOf(
            'Facebook\Tests\FooBarPseudoRandomStringGenerator',
            $fb->getRedirectLoginHelper()->getPseudoRandomStringGenerator()
        );
    }

    public function testPaginationReturnsProperResponse()
    {
        $config = array_merge($this->config, [
            'http_client_handler' => new FooClientInterface(),
        ]);
        $fb = new Facebook($config);

        $request = new FacebookRequest($fb->getApp(), 'foo_token', 'GET');
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
            '\Facebook\GraphNodes\GraphUser'
        );

        $nextPage = $fb->next($graphEdge);
        $this->assertInstanceOf('Facebook\GraphNodes\GraphEdge', $nextPage);
        $this->assertInstanceOf('Facebook\GraphNodes\GraphUser', $nextPage[0]);
        $this->assertEquals('Foo', $nextPage[0]['name']);

        $lastResponse = $fb->getLastResponse();
        $this->assertInstanceOf('Facebook\FacebookResponse', $lastResponse);
        $this->assertEquals(1337, $lastResponse->getHttpStatusCode());
    }

    public function testCanGetSuccessfulTransferWithMaxTries()
    {
        $config = array_merge($this->config, [
          'http_client_handler' => new FakeGraphApiForResumableUpload(),
        ]);
        $fb = new Facebook($config);
        $response = $fb->uploadVideo('me', __DIR__.'/foo.txt', [], 'foo-token', 3);
        $this->assertEquals([
          'video_id' => '1337',
          'success' => true,
        ], $response);
    }

    /**
     * @expectedException \Facebook\Exceptions\FacebookResponseException
     */
    public function testMaxingOutRetriesWillThrow()
    {
        $client = new FakeGraphApiForResumableUpload();
        $client->failOnTransfer();

        $config = array_merge($this->config, [
          'http_client_handler' => $client,
        ]);
        $fb = new Facebook($config);
        $response = $fb->uploadVideo('4', __DIR__.'/foo.txt', [], 'foo-token', 3);
    }
}
