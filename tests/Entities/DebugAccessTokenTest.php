<?php

use Facebook\Entities\DebugAccessToken;

class DebugAccessTokenTest extends \PHPUnit_Framework_TestCase
{
  public function testATokenIsValidatedOnTheAppIdAndMachineIdAndTokenValidityAndTokenExpiration()
  {
    $this->markTestSkipped('Needs FacebookClient mock');
    /*$aWeek = time() + (60 * 60 * 24 * 7);
    $dt = new \DateTime();
    $dt->setTimestamp($aWeek);

    $graphSessionInfoMock = m::mock('Facebook\GraphNodes\GraphSessionInfo');
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

    $this->assertTrue($isValid, 'Expected access token to be valid.');*/
  }

  public function testATokenWillNotBeValidIfTheAppIdDoesNotMatch()
  {
    $this->markTestSkipped('Needs FacebookClient mock');
    /*$aWeek = time() + (60 * 60 * 24 * 7);
    $dt = new \DateTime();
    $dt->setTimestamp($aWeek);

    $graphSessionInfoMock = m::mock('Facebook\GraphNodes\GraphSessionInfo');
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

    $this->assertFalse($isValid, 'Expected access token to be invalid because the app ID does not match.');*/
  }

  public function testATokenWillNotBeValidIfTheCollectionTellsUsItsNotValid()
  {
    $this->markTestSkipped('Needs FacebookClient mock');
    /*$aWeek = time() + (60 * 60 * 24 * 7);
    $dt = new \DateTime();
    $dt->setTimestamp($aWeek);

    $graphSessionInfoMock = m::mock('Facebook\GraphNodes\GraphSessionInfo');
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

    $this->assertFalse($isValid, 'Expected access token to be invalid because the collection says it is not valid.');*/
  }

  public function testInfoAboutAnAccessTokenCanBeObtainedFromGraph()
  {
    $this->markTestSkipped('Needs FacebookClient mock');
    /*$testUserAccessToken = FacebookTestHelper::$testUserAccessToken;

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
    }*/
  }

}
