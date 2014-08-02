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
namespace Facebook\Helpers;

use Facebook\FacebookClient;
use Facebook\Entities\FacebookApp;
use Facebook\Entities\AccessToken;
use Facebook\Entities\FacebookRequest;
use Facebook\HttpClients\FacebookHttpClientInterface;
use Facebook\Exceptions\FacebookSDKException;

/**
 * Class FacebookTestHelper
 * @package Facebook
 */
class FacebookTestHelper extends AbstractFacebookHelper implements \PHPUnit_Framework_TestListener
{
  protected static $testAppId = '';
  protected static $testAppSecret = '';
  /**
   * @var AccessToken
   */
  protected static $testUserAccessToken;

  protected static $testUserId;

  /**
   * @param string $appId
   * @param string $appSecret
   * @param FacebookHttpClientInterface $httpClient
   * @param bool $useSecretProof
   * @param bool $useBeta
   *
   * @return AbstractFacebookHelper
   */
  public function __construct($appId = '', $appSecret = '', FacebookHttpClientInterface $httpClient = null, $useSecretProof = true, $useBeta = false)
  {
    if (strlen($appId) && strlen($appSecret)) {
      static::$testAppId = $appId;
      static::$testAppSecret = $appSecret;
    }

    if (!strlen(static::$testAppId) || !strlen(static::$testAppSecret)) {
      throw new FacebookSDKException(
        'You must fill out phpunit.xml'
      );
    }
    $this->client = new FacebookClient($httpClient, $useSecretProof, $useBeta);
    $this->app = new FacebookApp(static::$testAppId, static::$testAppSecret);
  }

  /**
   * @return AccessToken
   */
  public function getAccessToken() {
    if (!static::$testUserAccessToken instanceof AccessToken) {
      $response = $this->client->handle(new FacebookRequest(
        '/' . $this->app->getId() . '/accounts/test-users',
        'POST',
        array(
          'installed' => true,
          'name' => 'PHPUnit Test User',
          'locale' => 'en_US',
          'permissions' => implode(',', ['read_stream', 'user_photos']),
        ),
        $this->app->getAccessToken()
      ));
      $data = $response->getGraphObject();

      static::$testUserId = $data->getProperty('id');
      static::$testUserAccessToken = $data->getProperty('access_token');
    }

    return static::$testUserAccessToken;
  }

  public function startTestSuite(\PHPUnit_Framework_TestSuite $suite)
  {
    
  }

  public function startTest(\PHPUnit_Framework_Test $test)
  {
    
  }

  public function endTest(\PHPUnit_Framework_Test $test, $time)
  {
    
  }

  public function endTestSuite(\PHPUnit_Framework_TestSuite $suite)
  {
    if (!static::$testUserId) {
      return;
    }

    $this->client->handle(new FacebookRequest(
      '/' . static::$testUserId,
      'DELETE',
      [],
      $this->app->getAccessToken()
    ));
  }

  public function addError(\PHPUnit_Framework_Test $test, \Exception $e, $time)
  {
    
  }

  public function addFailure(\PHPUnit_Framework_Test $test, \PHPUnit_Framework_AssertionFailedError $e, $time)
  {
    
  }

  public function addIncompleteTest(\PHPUnit_Framework_Test $test, \Exception $e, $time)
  {
    
  }

  public function addRiskyTest(\PHPUnit_Framework_Test $test, \Exception $e, $time)
  {
    
  }

  public function addSkippedTest(\PHPUnit_Framework_Test $test, \Exception $e, $time)
  {
    
  }

}
