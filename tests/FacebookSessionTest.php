<?php

use Mockery as m;
use Facebook\FacebookSession;
use Facebook\GraphSessionInfo;

class FacebookSessionTest extends PHPUnit_Framework_TestCase
{

  public function tearDown()
  {
    m::close();
  }

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
