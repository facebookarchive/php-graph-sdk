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
use Facebook\FacebookSession;
use Facebook\GraphNodes\GraphSessionInfo;
use Facebook\Tests\FacebookTestHelper;

class FacebookSessionTest extends \PHPUnit_Framework_TestCase
{

  public function testSessionToken()
  {
    $session = new FacebookSession(FacebookTestHelper::getAppToken());
    $this->assertEquals(
      FacebookTestHelper::getAppToken(), $session->getToken()
    );
  }

  public function testGetSessionInfo()
  {
    $response = FacebookTestHelper::$testSession->getSessionInfo();
    $this->assertTrue($response instanceof GraphSessionInfo);
    $this->assertNotNull($response->getAppId());
    $this->assertTrue($response->isValid());
    $scopes = $response->getPropertyAsArray('scopes');
    $this->assertTrue(is_array($scopes));
    $this->assertEquals(5, count($scopes));
  }

  public function testExtendAccessToken()
  {
    $response = FacebookTestHelper::$testSession->getLongLivedSession();
    $this->assertTrue($response instanceof FacebookSession);
    $info = $response->getSessionInfo();
    $nextWeek = time() + (60 * 60 * 24 * 7);
    $this->assertTrue(
      $info->getProperty('expires_at') > $nextWeek
    );
  }

  public function testSessionFromSignedRequest()
  {
    $signedRequest = m::mock('Facebook\Entities\SignedRequest');
    $signedRequest
      ->shouldReceive('get')
      ->with('code')
      ->once()
      ->andReturn(null);
    $signedRequest
      ->shouldReceive('get')
      ->with('oauth_token')
      ->once()
      ->andReturn('foo_token');
    $signedRequest
      ->shouldReceive('get')
      ->with('expires', 0)
      ->once()
      ->andReturn(time() + (60 * 60 * 24));
    $signedRequest
      ->shouldReceive('getUserId')
      ->once()
      ->andReturn('123');

    $session = FacebookSession::newSessionFromSignedRequest($signedRequest);
    $this->assertInstanceOf('Facebook\FacebookSession', $session);
    $this->assertEquals('foo_token', $session->getToken());
    $this->assertEquals('123', $session->getUserId());
  }

  public function testAppSessionValidates()
  {
    $session = FacebookSession::newAppSession();
    try {
      $session->validate();
    } catch (\Facebook\FacebookSDKException $ex) {
      $this->fail('Exception thrown validating app session.');
    }
  }
  
}
