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
namespace Facebook\Tests;

use Mockery as m;
use Facebook\Facebook;
use Facebook\FacebookClient;
use Facebook\HttpClients\FacebookHttpClientInterface;
use Facebook\PersistentData\PersistentDataInterface;


class FooClientInterface implements FacebookHttpClientInterface
{
  public function getResponseHeaders() { return ['X-foo-header' => 'bar']; }
  public function getResponseHttpStatusCode() { return 1337; }
  public function send($url, $method = 'GET', array $parameters = [], array $headers = []) { return 'foo_response'; }
}

class FooPersistentDataInterface implements PersistentDataInterface
{
  public function get($key) { return 'foo'; }
  public function set($key, $value) {}
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
    $this->assertInstanceOf('Facebook\HttpClients\FacebookCurlHttpClient',
      $fb->getClient()->getHttpClientHandler());
  }

  public function testStreamHttpClientHandlerCanBeForced()
  {
    $config = array_merge($this->config, [
      'http_client_handler' => 'stream'
    ]);
    $fb = new Facebook($config);
    $this->assertInstanceOf('Facebook\HttpClients\FacebookStreamHttpClient',
      $fb->getClient()->getHttpClientHandler());
  }

  public function testGuzzleHttpClientHandlerCanBeForced()
  {
    $config = array_merge($this->config, [
      'http_client_handler' => 'guzzle'
    ]);
    $fb = new Facebook($config);
    $this->assertInstanceOf('Facebook\HttpClients\FacebookGuzzleHttpClient',
      $fb->getClient()->getHttpClientHandler());
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
    $this->assertInstanceOf('Facebook\PersistentData\FacebookMemoryPersistentDataHandler',
      $fb->getRedirectLoginHelper()->getPersistentDataHandler());
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
        'http_client_handler' => new FooClientInterface(),
        'persistent_data_handler' => new FooPersistentDataInterface(),
        'enable_beta_mode' => true,
        'default_graph_version' => 'v1337',
      ]);
    $fb = new Facebook($config);

    $request = $fb->request('FOO_VERB', '/foo');
    $this->assertInstanceOf('Facebook\Tests\FooClientInterface',
      $fb->getClient()->getHttpClientHandler());
    $this->assertInstanceOf('Facebook\Tests\FooPersistentDataInterface',
      $fb->getRedirectLoginHelper()->getPersistentDataHandler());
    $this->assertEquals(FacebookClient::BASE_GRAPH_URL_BETA,
      $fb->getClient()->getBaseGraphUrl());
    $this->assertEquals('1337', $request->getApp()->getId());
    $this->assertEquals('foo_secret', $request->getApp()->getSecret());
    $this->assertEquals('foo_token', (string) $request->getAccessToken());
    $this->assertEquals('v1337', $request->getGraphVersion());
  }

}
