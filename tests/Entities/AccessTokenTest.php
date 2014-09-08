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
use Facebook\Entities\FacebookApp;
use Facebook\Entities\AccessToken;

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
      ->shouldReceive('getProperty')
      ->with('app_id')
      ->once()
      ->andReturn('123');
    $graphSessionInfoMock
      ->shouldReceive('getProperty')
      ->with('machine_id')
      ->once()
      ->andReturn('foo_machine');
    $graphSessionInfoMock
      ->shouldReceive('getIsValid')
      ->once()
      ->andReturn(true);
    $graphSessionInfoMock
      ->shouldReceive('getExpiresAt')
      ->twice()
      ->andReturn($dt);

    $app = new FacebookApp('123', 'foo_secret');
    $isValid = AccessToken::validateAccessToken($graphSessionInfoMock, $app, 'foo_machine');

    $this->assertTrue($isValid, 'Expected access token to be valid.');
  }

  public function testATokenWillNotBeValidIfTheAppIdDoesNotMatch()
  {
    $aWeek = time() + (60 * 60 * 24 * 7);
    $dt = new \DateTime();
    $dt->setTimestamp($aWeek);

    $graphSessionInfoMock = m::mock('Facebook\GraphNodes\GraphSessionInfo');
    $graphSessionInfoMock
      ->shouldReceive('getProperty')
      ->with('app_id')
      ->once()
      ->andReturn('1337');
    $graphSessionInfoMock
      ->shouldReceive('getProperty')
      ->with('machine_id')
      ->once()
      ->andReturn('foo_machine');
    $graphSessionInfoMock
      ->shouldReceive('getIsValid')
      ->once()
      ->andReturn(true);
    $graphSessionInfoMock
      ->shouldReceive('getExpiresAt')
      ->twice()
      ->andReturn($dt);

    $app = new FacebookApp('123', 'foo_secret');
    $isValid = AccessToken::validateAccessToken($graphSessionInfoMock, $app, 'foo_machine');

    $this->assertFalse($isValid, 'Expected access token to be invalid because the app ID does not match.');
  }

  public function testATokenWillNotBeValidIfTheMachineIdDoesNotMatch()
  {
    $aWeek = time() + (60 * 60 * 24 * 7);
    $dt = new \DateTime();
    $dt->setTimestamp($aWeek);

    $graphSessionInfoMock = m::mock('Facebook\GraphNodes\GraphSessionInfo');
    $graphSessionInfoMock
      ->shouldReceive('getProperty')
      ->with('app_id')
      ->once()
      ->andReturn('123');
    $graphSessionInfoMock
      ->shouldReceive('getProperty')
      ->with('machine_id')
      ->once()
      ->andReturn('foo_machine');
    $graphSessionInfoMock
      ->shouldReceive('getIsValid')
      ->once()
      ->andReturn(true);
    $graphSessionInfoMock
      ->shouldReceive('getExpiresAt')
      ->twice()
      ->andReturn($dt);

    $app = new FacebookApp('123', 'foo_secret');
    $isValid = AccessToken::validateAccessToken($graphSessionInfoMock, $app, 'bar_machine');

    $this->assertFalse($isValid, 'Expected access token to be invalid because the machine ID does not match.');
  }

  public function testATokenWillNotBeValidIfTheCollectionTellsUsItsNotValid()
  {
    $aWeek = time() + (60 * 60 * 24 * 7);
    $dt = new \DateTime();
    $dt->setTimestamp($aWeek);

    $graphSessionInfoMock = m::mock('Facebook\GraphNodes\GraphSessionInfo');
    $graphSessionInfoMock
      ->shouldReceive('getProperty')
      ->with('app_id')
      ->once()
      ->andReturn('123');
    $graphSessionInfoMock
      ->shouldReceive('getProperty')
      ->with('machine_id')
      ->once()
      ->andReturn('foo_machine');
    $graphSessionInfoMock
      ->shouldReceive('getIsValid')
      ->once()
      ->andReturn(false);
    $graphSessionInfoMock
      ->shouldReceive('getExpiresAt')
      ->twice()
      ->andReturn($dt);

    $app = new FacebookApp('123', 'foo_secret');
    $isValid = AccessToken::validateAccessToken($graphSessionInfoMock, $app, 'foo_machine');

    $this->assertFalse($isValid, 'Expected access token to be invalid because the collection says it is not valid.');
  }

  public function testATokenWillNotBeValidIfTheTokenHasExpired()
  {
    $lastWeek = time() - (60 * 60 * 24 * 7);
    $dt = new \DateTime();
    $dt->setTimestamp($lastWeek);

    $graphSessionInfoMock = m::mock('Facebook\GraphNodes\GraphSessionInfo');
    $graphSessionInfoMock
      ->shouldReceive('getProperty')
      ->with('app_id')
      ->once()
      ->andReturn('123');
    $graphSessionInfoMock
      ->shouldReceive('getProperty')
      ->with('machine_id')
      ->once()
      ->andReturn('foo_machine');
    $graphSessionInfoMock
      ->shouldReceive('getIsValid')
      ->once()
      ->andReturn(true);
    $graphSessionInfoMock
      ->shouldReceive('getExpiresAt')
      ->twice()
      ->andReturn($dt);

    $app = new FacebookApp('123', 'foo_secret');
    $isValid = AccessToken::validateAccessToken($graphSessionInfoMock, $app, 'foo_machine');

    $this->assertFalse($isValid, 'Expected access token to be invalid because it has expired.');
  }

  public function testInfoAboutAnAccessTokenCanBeObtainedFromGraph()
  {
    $app = new FacebookApp('123', 'foo_secret');
    $response = m::mock('Facebook\Entities\FacebookResponse');
    $response
      ->shouldReceive('getGraphSessionInfo')
      ->once()
      ->andReturn($response);
    $response
      ->shouldReceive('getExpiresAt')
      ->once()
      ->andReturn(null);
    $client = m::mock('Facebook\FacebookClient');
    $client
      ->shouldReceive('sendRequest')
      ->with(m::type('Facebook\Entities\FacebookRequest'))
      ->once()
      ->andReturn($response);

    $accessToken = new AccessToken('foo_token');
    $accessTokenInfo = $accessToken->getInfo($app, $client);

    $this->assertSame($response, $accessTokenInfo);
  }

  public function testAShortLivedAccessTokenCabBeExtended()
  {
    $app = new FacebookApp('123', 'foo_secret');
    $response = m::mock('Facebook\Entities\FacebookResponse');
    $response
      ->shouldReceive('getDecodedBody')
      ->once()
      ->andReturn([
          'access_token' => 'long_token',
          'expires' => 123,
          'machine_id' => 'foo_machine',
        ]);
    $client = m::mock('Facebook\FacebookClient');
    $client
      ->shouldReceive('sendRequest')
      ->with(m::type('Facebook\Entities\FacebookRequest'))
      ->once()
      ->andReturn($response);

    $accessToken = new AccessToken('foo_token');
    $longLivedAccessToken = $accessToken->extend($app, $client);

    $this->assertInstanceOf('Facebook\Entities\AccessToken', $longLivedAccessToken);
    $this->assertEquals('long_token', (string)$longLivedAccessToken);
    $this->assertEquals('foo_machine', $longLivedAccessToken->getMachineId());
    $this->assertEquals(time() + 123, $longLivedAccessToken->getExpiresAt()->getTimeStamp());
  }

  public function testALongLivedAccessTokenCanBeUsedToObtainACode()
  {
    $app = new FacebookApp('123', 'foo_secret');
    $response = m::mock('Facebook\Entities\FacebookResponse');
    $response
      ->shouldReceive('getDecodedBody')
      ->once()
      ->andReturn([
          'code' => 'foo_code',
        ]);
    $client = m::mock('Facebook\FacebookClient');
    $client
      ->shouldReceive('sendRequest')
      ->with(m::type('Facebook\Entities\FacebookRequest'))
      ->once()
      ->andReturn($response);

    $code = AccessToken::getCodeFromAccessToken('foo_token', $app, $client);

    $this->assertEquals('foo_code', $code);
  }

  public function testACodeCanBeUsedToObtainAnAccessToken()
  {
    $app = new FacebookApp('123', 'foo_secret');
    $response = m::mock('Facebook\Entities\FacebookResponse');
    $response
      ->shouldReceive('getDecodedBody')
      ->once()
      ->andReturn([
          'access_token' => 'new_long_token',
          'expires' => 123,
          'machine_id' => 'foo_machine',
        ]);
    $client = m::mock('Facebook\FacebookClient');
    $client
      ->shouldReceive('sendRequest')
      ->with(m::type('Facebook\Entities\FacebookRequest'))
      ->once()
      ->andReturn($response);
    $accessTokenFromCode = AccessToken::getAccessTokenFromCode('foo_code', $app, $client);

    $this->assertInstanceOf('Facebook\Entities\AccessToken', $accessTokenFromCode);
    $this->assertEquals('new_long_token', (string)$accessTokenFromCode);
    $this->assertEquals('foo_machine', $accessTokenFromCode->getMachineId());
    $this->assertEquals(time() + 123, $accessTokenFromCode->getExpiresAt()->getTimeStamp());
  }

  public function testAccessTokenCanBeSerialized()
  {
    $accessToken = new AccessToken('foo', time(), 'bar');
    $newAccessToken = unserialize(serialize($accessToken));

    $this->assertEquals((string)$accessToken, (string)$newAccessToken);
    $this->assertEquals($accessToken->getExpiresAt(), $newAccessToken->getExpiresAt());
    $this->assertEquals($accessToken->getMachineId(), $newAccessToken->getMachineId());
  }

}
