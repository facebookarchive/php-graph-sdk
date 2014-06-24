<?php

use Mockery as m;
use Facebook\Entities\AccessToken;

class AccessTokenTest extends PHPUnit_Framework_TestCase
{

  public function tearDown()
  {
    m::close();
  }

  public function testAnAccessTokenCanBeReturnedAsAString()
  {
    $accessToken = new AccessToken('foo_token');

    $this->assertEquals('foo_token', (string) $accessToken);
  }

  public function testShortLivedAccessTokensCanBeDetected()
  {
    $anHourAndAHalf = time() + (1.5 * 60);
    $accessToken = new AccessToken('foo_token', $anHourAndAHalf);

    $isLongLived = $accessToken->isLongLived();

    $this->assertFalse($isLongLived, 'Expected access token to be short lived.');
  }

  public function testLongLivedAccessTokensCanBeDetected()
  {
    $aWeek = time() + (60 * 60 * 24 * 7);
    $accessToken = new AccessToken('foo_token', $aWeek);

    $isLongLived = $accessToken->isLongLived();

    $this->assertTrue($isLongLived, 'Expected access token to be long lived.');
  }

  public function testATokenIsValidatedOnTheAppIdAndMachineIdAndTokenValidityAndTokenExpiration()
  {
    $aWeek = time() + (60 * 60 * 24 * 7);
    $dt = new \DateTime();
    $dt->setTimestamp($aWeek);

    $graphSessionInfoMock = m::mock('Facebook\GraphSessionInfo');
    $graphSessionInfoMock
      ->shouldReceive('getAppId')
      ->once()
      ->andReturn('123');
    $graphSessionInfoMock
      ->shouldReceive('getProperty')
      ->with('machine_id')
      ->once()
      ->andReturn('foo_machine');
    $graphSessionInfoMock
      ->shouldReceive('isValid')
      ->once()
      ->andReturn(true);
    $graphSessionInfoMock
      ->shouldReceive('getExpiresAt')
      ->twice()
      ->andReturn($dt);

    $isValid = AccessToken::validateAccessToken($graphSessionInfoMock, '123', 'foo_machine');

    $this->assertTrue($isValid, 'Expected access token to be valid.');
  }

  public function testATokenWillNotBeValidIfTheAppIdDoesNotMatch()
  {
    $aWeek = time() + (60 * 60 * 24 * 7);
    $dt = new \DateTime();
    $dt->setTimestamp($aWeek);

    $graphSessionInfoMock = m::mock('Facebook\GraphSessionInfo');
    $graphSessionInfoMock
      ->shouldReceive('getAppId')
      ->once()
      ->andReturn('123');
    $graphSessionInfoMock
      ->shouldReceive('getProperty')
      ->with('machine_id')
      ->once()
      ->andReturn('foo_machine');
    $graphSessionInfoMock
      ->shouldReceive('isValid')
      ->once()
      ->andReturn(true);
    $graphSessionInfoMock
      ->shouldReceive('getExpiresAt')
      ->twice()
      ->andReturn($dt);

    $isValid = AccessToken::validateAccessToken($graphSessionInfoMock, '42', 'foo_machine');

    $this->assertFalse($isValid, 'Expected access token to be invalid because the app ID does not match.');
  }

  public function testATokenWillNotBeValidIfTheMachineIdDoesNotMatch()
  {
    $aWeek = time() + (60 * 60 * 24 * 7);
    $dt = new \DateTime();
    $dt->setTimestamp($aWeek);

    $graphSessionInfoMock = m::mock('Facebook\GraphSessionInfo');
    $graphSessionInfoMock
      ->shouldReceive('getAppId')
      ->once()
      ->andReturn('123');
    $graphSessionInfoMock
      ->shouldReceive('getProperty')
      ->with('machine_id')
      ->once()
      ->andReturn('foo_machine');
    $graphSessionInfoMock
      ->shouldReceive('isValid')
      ->once()
      ->andReturn(true);
    $graphSessionInfoMock
      ->shouldReceive('getExpiresAt')
      ->twice()
      ->andReturn($dt);

    $isValid = AccessToken::validateAccessToken($graphSessionInfoMock, '123', 'bar_machine');

    $this->assertFalse($isValid, 'Expected access token to be invalid because the machine ID does not match.');
  }

  public function testATokenWillNotBeValidIfTheCollectionTellsUsItsNotValid()
  {
    $aWeek = time() + (60 * 60 * 24 * 7);
    $dt = new \DateTime();
    $dt->setTimestamp($aWeek);

    $graphSessionInfoMock = m::mock('Facebook\GraphSessionInfo');
    $graphSessionInfoMock
      ->shouldReceive('getAppId')
      ->once()
      ->andReturn('123');
    $graphSessionInfoMock
      ->shouldReceive('getProperty')
      ->with('machine_id')
      ->once()
      ->andReturn('foo_machine');
    $graphSessionInfoMock
      ->shouldReceive('isValid')
      ->once()
      ->andReturn(false);
    $graphSessionInfoMock
      ->shouldReceive('getExpiresAt')
      ->twice()
      ->andReturn($dt);

    $isValid = AccessToken::validateAccessToken($graphSessionInfoMock, '123', 'foo_machine');

    $this->assertFalse($isValid, 'Expected access token to be invalid because the collection says it is not valid.');
  }

  public function testATokenWillNotBeValidIfTheTokenHasExpired()
  {
    $lastWeek = time() - (60 * 60 * 24 * 7);
    $dt = new \DateTime();
    $dt->setTimestamp($lastWeek);

    $graphSessionInfoMock = m::mock('Facebook\GraphSessionInfo');
    $graphSessionInfoMock
      ->shouldReceive('getAppId')
      ->once()
      ->andReturn('123');
    $graphSessionInfoMock
      ->shouldReceive('getProperty')
      ->with('machine_id')
      ->once()
      ->andReturn('foo_machine');
    $graphSessionInfoMock
      ->shouldReceive('isValid')
      ->once()
      ->andReturn(true);
    $graphSessionInfoMock
      ->shouldReceive('getExpiresAt')
      ->twice()
      ->andReturn($dt);

    $isValid = AccessToken::validateAccessToken($graphSessionInfoMock, '123', 'foo_machine');

    $this->assertFalse($isValid, 'Expected access token to be invalid because it has expired.');
  }

  public function testInfoAboutAnAccessTokenCanBeObtainedFromGraph()
  {
    $testUserAccessToken = FacebookTestHelper::$testUserAccessToken;

    $accessToken = new AccessToken($testUserAccessToken);
    $accessTokenInfo = $accessToken->getInfo();

    $testAppId = FacebookTestCredentials::$appId;
    $this->assertEquals($testAppId, $accessTokenInfo->getAppId());

    $testUserId = FacebookTestHelper::$testUserId;
    $this->assertEquals($testUserId, $accessTokenInfo->getId());

    $expectedScopes = FacebookTestHelper::$testUserPermissions;
    $actualScopes = $accessTokenInfo->getPropertyAsArray('scopes');
    foreach ($expectedScopes as $scope) {
      $this->assertTrue(in_array($scope, $actualScopes),
        'Expected the following permission to be present: '.$scope);
    }
  }

  public function testAShortLivedAccessTokenCabBeExtended()
  {
    $testUserAccessToken = FacebookTestHelper::$testUserAccessToken;

    $accessToken = new AccessToken($testUserAccessToken);
    $longLivedAccessToken = $accessToken->extend();

    $this->assertInstanceOf('Facebook\Entities\AccessToken', $longLivedAccessToken);
  }

  public function testALongLivedAccessTokenCanBeUsedToObtainACode()
  {
    $testUserAccessToken = FacebookTestHelper::$testUserAccessToken;

    $accessToken = new AccessToken($testUserAccessToken);
    $longLivedAccessToken = $accessToken->extend();

    $code = AccessToken::getCodeFromAccessToken((string) $longLivedAccessToken);

    $this->assertTrue(is_string($code));
  }

  public function testACodeCanBeUsedToObtainAnAccessToken()
  {
    $testUserAccessToken = FacebookTestHelper::$testUserAccessToken;

    $accessToken = new AccessToken($testUserAccessToken);
    $longLivedAccessToken = $accessToken->extend();

    $code = AccessToken::getCodeFromAccessToken($longLivedAccessToken);
    $accessTokenFromCode = AccessToken::getAccessTokenFromCode($code);

    $this->assertInstanceOf('Facebook\Entities\AccessToken', $accessTokenFromCode);
  }

}
