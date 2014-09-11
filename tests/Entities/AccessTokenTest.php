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
use Facebook\GraphNodes\GraphSessionInfo;

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
    $accessToken = new AccessToken('foo_token', $this->aWeekFromNow());

    $isLongLived = $accessToken->isLongLived();

    $this->assertTrue($isLongLived, 'Expected access token to be long lived.');
  }

  public function testATokenIsValidatedOnTheAppIdAndMachineIdAndTokenValidityAndTokenExpiration()
  {
    $graphSession = $this->createGraphSessionInfo('123', 'foo_machine', true, $this->aWeekFromNow());
    $app = new FacebookApp('123', 'foo_secret');

    $isValid = AccessToken::validateAccessToken($graphSession, $app, 'foo_machine');

    $this->assertTrue($isValid, 'Expected access token to be valid.');
  }

  public function testATokenWillNotBeValidIfTheAppIdDoesNotMatch()
  {
    $graphSession = $this->createGraphSessionInfo('1337', 'foo_machine', true, $this->aWeekFromNow());
    $app = new FacebookApp('123', 'foo_secret');

    $isValid = AccessToken::validateAccessToken($graphSession, $app, 'foo_machine');

    $this->assertFalse($isValid, 'Expected access token to be invalid because the app ID does not match.');
  }

  public function testATokenWillNotBeValidIfTheMachineIdDoesNotMatch()
  {
    $graphSession = $this->createGraphSessionInfo('123', 'foo_machine', true, $this->aWeekFromNow());
    $app = new FacebookApp('123', 'foo_secret');

    $isValid = AccessToken::validateAccessToken($graphSession, $app, 'bar_machine');

    $this->assertFalse($isValid, 'Expected access token to be invalid because the machine ID does not match.');
  }

  public function testATokenWillNotBeValidIfTheCollectionTellsUsItsNotValid()
  {
    $graphSession = $this->createGraphSessionInfo('123', 'foo_machine', false, $this->aWeekFromNow());
    $app = new FacebookApp('123', 'foo_secret');

    $isValid = AccessToken::validateAccessToken($graphSession, $app, 'foo_machine');

    $this->assertFalse($isValid, 'Expected access token to be invalid because the collection says it is not valid.');
  }

  public function testATokenWillNotBeValidIfTheTokenHasExpired()
  {
    $expiredTime = time() - (60 * 60 * 24 * 7);
    $graphSession = $this->createGraphSessionInfo('123', 'foo_machine', true, $expiredTime);
    $app = new FacebookApp('123', 'foo_secret');

    $isValid = AccessToken::validateAccessToken($graphSession, $app, 'foo_machine');

    $this->assertFalse($isValid, 'Expected access token to be invalid because it has expired.');
  }

  public function testInfoAboutAnAccessTokenCanBeObtainedFromGraph()
  {
    $app = new FacebookApp('123', 'foo_secret');
    $response = $this->createFacebookResponseMockWithNoExpiresAt();
    $client = $this->createFacebookClientMockWithResponse($response);
    $accessToken = new AccessToken('foo_token');

    $accessTokenInfo = $accessToken->getInfo($app, $client);

    $this->assertSame($response, $accessTokenInfo);
  }

  public function testAShortLivedAccessTokenCabBeExtended()
  {
    $app = new FacebookApp('123', 'foo_secret');
    $response = $this->createFacebookResponseMockWithDecodedBody([
      'access_token' => 'long_token',
      'expires' => 123,
      'machine_id' => 'foo_machine',
    ]);
    $client = $this->createFacebookClientMockWithResponse($response);
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
    $response = $this->createFacebookResponseMockWithDecodedBody([
      'code' => 'foo_code',
    ]);
    $client = $this->createFacebookClientMockWithResponse($response);

    $code = AccessToken::getCodeFromAccessToken('foo_token', $app, $client);

    $this->assertEquals('foo_code', $code);
  }

  public function testACodeCanBeUsedToObtainAnAccessToken()
  {
    $app = new FacebookApp('123', 'foo_secret');
    $response = $this->createFacebookResponseMockWithDecodedBody([
      'access_token' => 'new_long_token',
      'expires' => 123,
      'machine_id' => 'foo_machine',
    ]);
    $client = $this->createFacebookClientMockWithResponse($response);

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

    $this->assertEquals((string) $accessToken, (string) $newAccessToken);
    $this->assertEquals($accessToken->getExpiresAt(), $newAccessToken->getExpiresAt());
    $this->assertEquals($accessToken->getMachineId(), $newAccessToken->getMachineId());
  }

  private function createGraphSessionInfo($appId, $machineId, $isValid, $expiresAt)
  {
    return new GraphSessionInfo([
      'app_id' => $appId,
      'machine_id' => $machineId,
      'is_valid' => $isValid,
      'expires_at' => $expiresAt
    ]);
  }

  private function aWeekFromNow()
  {
    return time() + (60 * 60 * 24 * 7);//a week from now
  }

  private function createFacebookClientMockWithResponse($response)
  {
    $client = m::mock('Facebook\FacebookClient');
    $client
      ->shouldReceive('sendRequest')
      ->with(m::type('Facebook\Entities\FacebookRequest'))
      ->once()
      ->andReturn($response);
    return $client;
  }

  private function createFacebookResponseMockWithDecodedBody($decodedBody)
  {
    $response = $this->createFacebookResponseMock();
    $response
      ->shouldReceive('getDecodedBody')
      ->once()
      ->andReturn($decodedBody);
    return $response;
  }

  private function createFacebookResponseMock()
  {
    return m::mock('Facebook\Entities\FacebookResponse');
  }

  private function createFacebookResponseMockWithNoExpiresAt()
  {
    $response = $this->createFacebookResponseMock();
    $response
      ->shouldReceive('getGraphSessionInfo')
      ->once()
      ->andReturn($response);
    $response
      ->shouldReceive('getExpiresAt')
      ->once()
      ->andReturn(null);
    return $response;
  }

}
