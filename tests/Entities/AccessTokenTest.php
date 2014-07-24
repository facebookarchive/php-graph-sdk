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
namespace Facebook\Tests\Entities;

use Mockery as m;
use Facebook\Tests\FacebookTestCredentials;
use Facebook\Entities\AccessToken;
use Facebook\Tests\FacebookTestHelper;

class AccessTokenTest extends \PHPUnit_Framework_TestCase
{

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

    $this->assertTrue($isValid, 'Expected access token to be valid.');
  }

  public function testATokenWillNotBeValidIfTheAppIdDoesNotMatch()
  {
    $aWeek = time() + (60 * 60 * 24 * 7);
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

    $this->assertFalse($isValid, 'Expected access token to be invalid because the app ID does not match.');
  }

  public function testATokenWillNotBeValidIfTheMachineIdDoesNotMatch()
  {
    $aWeek = time() + (60 * 60 * 24 * 7);
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

    $isValid = AccessToken::validateAccessToken($graphSessionInfoMock, '123', 'bar_machine');

    $this->assertFalse($isValid, 'Expected access token to be invalid because the machine ID does not match.');
  }

  public function testATokenWillNotBeValidIfTheCollectionTellsUsItsNotValid()
  {
    $aWeek = time() + (60 * 60 * 24 * 7);
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

    $this->assertFalse($isValid, 'Expected access token to be invalid because the collection says it is not valid.');
  }

  public function testATokenWillNotBeValidIfTheTokenHasExpired()
  {
    $lastWeek = time() - (60 * 60 * 24 * 7);
    $dt = new \DateTime();
    $dt->setTimestamp($lastWeek);

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

  public function testSerialization()
  {
    $accessToken = new AccessToken('foo', time(), 'bar');
    $newAccessToken = unserialize(serialize($accessToken));

    $this->assertEquals((string)$accessToken, (string)$newAccessToken);
    $this->assertEquals($accessToken->getExpiresAt(), $newAccessToken->getExpiresAt());
    $this->assertEquals($accessToken->getMachineId(), $newAccessToken->getMachineId());
  }

  /**
   * @dataProvider provideAccessTokenExpiration
   */
  public function testIsExpired($expiresAt, $expected)
  {
    $accessToken = new AccessToken('foo', $expiresAt);

    $this->assertEquals($expected, $accessToken->isExpired());
  }

  public function provideAccessTokenExpiration()
  {
    return array(
      array(time()+60, false),
      array(time()-60, true),
      array(0, false),
    );
  }
}
