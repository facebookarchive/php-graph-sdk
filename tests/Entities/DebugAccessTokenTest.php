<?php

use Facebook\Entities\DebugAccessToken;
use Facebook\Tests\FacebookTestCase;

class DebugAccessTokenTest extends FacebookTestCase
{
  protected $fakeApp;

  protected function setUp()
  {
    $this->fakeApp = $this->getAppMock('foo_app_id', 'foo_app_secret');
  }

  public function testInfoAboutAnAccessTokenCanBeObtainedFromGraph()
  {
    $clientMock = $this->getClientMock('/debug_token', 'GET', [
      'input_token' => 'foo_token',
    ], json_encode(
      [
        'data' => [
          'app_id' => '138483919580948',
          'application' => 'Social Cafe',
          'expires_at' => 1352419328,
          'is_valid' => true,
          'issued_at' => 1347235328,
          'metadata' => [
              'sso' => 'iphone-safari'
          ],
          'scopes' => [
              'email',
              'publish_actions'
          ],
          'user_id' => 1207059
        ]
      ]
    ));

    $accessToken = new DebugAccessToken($clientMock, $this->fakeApp, 'foo_token');

    $this->assertInstanceOf('Facebook\Entities\DebugAccessToken', $accessToken);
    $this->assertEquals('foo_token', $accessToken->getValue());
    $this->assertEquals('138483919580948', $accessToken->getAppId());
    $this->assertEquals('Social Cafe', $accessToken->getAppName());
    $this->assertTrue($accessToken->getExpiresAt() instanceof \DateTime);
    $this->assertEquals(1352419328, $accessToken->getExpiresAt()->getTimestamp());
    $this->assertTrue($accessToken->getIsValid());
    $this->assertTrue($accessToken->getIssuedAt() instanceof \DateTime);
    $this->assertEquals(1347235328, $accessToken->getIssuedAt()->getTimestamp());
    $this->assertEquals(['email', 'publish_actions'], $accessToken->getScopes());
    $this->assertEquals(1207059, $accessToken->getUserId());
  }

  public function testATokenIsValidatedOnTheAppIdAndMachineIdAndTokenValidityAndTokenExpiration()
  {
    $aWeek = time() + (60 * 60 * 24 * 7);
    $clientMock = $this->getClientMock('/debug_token', 'GET', [
      'input_token' => 'foo_token',
    ], json_encode(
      [
        'data' => [
          'app_id' => 'foo_app_id',
          'expires_at' => $aWeek,
          'is_valid' => true,
        ]
      ]
    ));

    $accessToken = new DebugAccessToken($clientMock, $this->fakeApp, 'foo_token', 'foo_machine');

    $this->assertTrue($accessToken->isValid('foo_machine'));
  }

  public function testATokenWillNotBeValidIfTheAppIdDoesNotMatch()
  {
    $aWeek = time() + (60 * 60 * 24 * 7);
    $clientMock = $this->getClientMock('/debug_token', 'GET', [
      'input_token' => 'foo_token',
    ], json_encode(
      [
        'data' => [
          'app_id' => 'bar_app_id',
          'expires_at' => $aWeek,
          'is_valid' => true,
        ]
      ]
    ));

    $accessToken = new DebugAccessToken($clientMock, $this->fakeApp, 'foo_token');

    $this->assertFalse($accessToken->isValid());
  }

  public function testATokenWillNotBeValidIfTheCollectionTellsUsItsNotValid()
  {
    $aWeek = time() + (60 * 60 * 24 * 7);
    $clientMock = $this->getClientMock('/debug_token', 'GET', [
      'input_token' => 'foo_token',
    ], json_encode(
      [
        'data' => [
          'app_id' => 'foo_app_id',
          'expires_at' => $aWeek,
          'is_valid' => false,
        ]
      ]
    ));

    $accessToken = new DebugAccessToken($clientMock, $this->fakeApp, 'foo_token');

    $this->assertFalse($accessToken->isValid());
  }

}
