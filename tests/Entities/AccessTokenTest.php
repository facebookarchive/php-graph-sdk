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
use Facebook\Entities\AccessToken;
use Facebook\Tests\FacebookTestCase;

class AccessTokenTest extends FacebookTestCase
{
  protected $fakeApp;

  protected function setUp()
  {
    $this->fakeApp = $this->getAppMock('foo_app_id', 'foo_app_secret');
  }

  public function testGetApp()
  {
    $accessToken = new AccessToken($this->fakeApp, 'foo_token');

    $this->assertSame($this->fakeApp, $accessToken->getApp());
  }

  public function testGetValue()
  {
    $accessToken = new AccessToken($this->fakeApp, 'foo_token');

    $this->assertEquals('foo_token', $accessToken->getValue());
  }

  public function testGetExpiredAt()
  {
    $expiredAt = time();
    $accessToken = new AccessToken($this->fakeApp, 'foo_token', $expiredAt);

    $this->assertInstanceOf('DateTime', $accessToken->getExpiresAt());
    $this->assertEquals($expiredAt, $accessToken->getExpiresAt()->getTimestamp());
  }

  public function testGetMachineId()
  {
    $accessToken = new AccessToken($this->fakeApp, 'foo_token', 0, 'machine_id');

    $this->assertEquals('machine_id', $accessToken->getMachineId());
  }

  public function testThatAnAccessTokenCanBeReturnedAsAString()
  {
    $accessToken = new AccessToken($this->fakeApp, 'foo_token');

    $this->assertEquals('foo_token', (string) $accessToken);
  }

  public function testThatShortLivedAccessTokensCanBeDetected()
  {
    $anHourAndAHalf = time() + (1.5 * 60);
    $accessToken = new AccessToken($this->fakeApp, 'foo_token', $anHourAndAHalf);

    $this->assertFalse($accessToken->isLongLived(), 'Expected access token to be short lived.');
  }

  public function testThatLongLivedAccessTokensCanBeDetected()
  {
    $aWeek = time() + (60 * 60 * 24 * 7);
    $accessToken = new AccessToken($this->fakeApp, 'foo_token', $aWeek);

    $this->assertTrue($accessToken->isLongLived(), 'Expected access token to be long lived.');
  }

  /**
   * @dataProvider provideAccessTokenExpiration
   */
  public function testIsExpired($expiresAt, $expected)
  {
    $accessToken = new AccessToken($this->fakeApp, 'foo', $expiresAt);

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

  public function testATokenIsValidatedOnTheTokenExpirationAndMachineId()
  {
    $aWeek = time() + (60 * 60 * 24 * 7);
    $accessToken = new AccessToken($this->fakeApp, 'foo_token', $aWeek, 'foo_machine');

    $this->assertTrue($accessToken->isValid('foo_machine'), 'Expected access token to be valid.');
  }

  public function testATokenWillNotBeValidIfTheMachineIdDoesNotMatch()
  {
    $aWeek = time() + (60 * 60 * 24 * 7);
    $accessToken = new AccessToken($this->fakeApp, 'foo_token', $aWeek, 'foo_machine');

    $this->assertFalse($accessToken->isValid('bar_machine'), 'Expected access token to be invalid because the machine ID does not match.');
  }

  public function testATokenWillNotBeValidIfTheTokenHasExpired()
  {
    $lastWeek = time() - (60 * 60 * 24 * 7);
    $accessToken = new AccessToken($this->fakeApp, 'foo_token', $lastWeek);

    $this->assertFalse($accessToken->isValid(), 'Expected access token to be invalid because it has expired.');
  }
 
  public function testGetSecretProof()
  {
    $accessToken = new AccessToken($this->fakeApp, 'foo_token');

    $this->assertEquals('857d5f035a894f16b4180f19966e055cdeab92d4d53017b13dccd6d43b6497af', $accessToken->getSecretProof());
  }

  public function testGetCode()
  {
    $clientMock = $this->getClientMock(
      '/oauth/client_code',
      'GET',
      [
        'client_id' => $this->fakeApp->getId(),
        'client_secret' => $this->fakeApp->getSecret(),
        'access_token' => 'foo_token',
        'redirect_uri' => 'http://redirect',
      ],
      json_encode(['code' => 'foo_code'])
    );

    $accessToken = new AccessToken($this->fakeApp, 'foo_token');
    $code = $accessToken->getCode($clientMock, 'http://redirect');

    $this->assertInstanceOf('Facebook\Entities\Code', $code);
    $this->assertEquals('foo_code', $code->getValue());
  }

  public function testGetExtended()
  {
    $clientMock = $this->getClientMock(
      '/oauth/access_token',
      'GET',
      [
        'client_id' => $this->fakeApp->getId(),
        'client_secret' => $this->fakeApp->getSecret(),
        'grant_type' => 'fb_exchange_token',
        'fb_exchange_token' => 'foo_token',
      ],
      json_encode(['access_token' => 'extended_foo_token'])
    );

    $accessToken = new AccessToken($this->fakeApp, 'foo_token');
    $extendedAccessToken = $accessToken->getExtended($clientMock);

    $this->assertInstanceOf('Facebook\Entities\AccessToken', $extendedAccessToken);
    $this->assertEquals('extended_foo_token', $extendedAccessToken->getValue());
  }

  public function testGetDebugged()
  {
    $clientMock = m::mock('Facebook\FacebookClient[handle]')
      ->shouldReceive('handle')
      ->once()
      ->andReturn(
        m::mock('Facebook\Entities\FacebookResponse', [
          m::mock('Facebook\Entities\FacebookRequest'),
          '{}'
        ])->makePartial()
      )
      ->getMock();

    $accessToken = new AccessToken($this->fakeApp, 'foo_token');
    $debuggedAccessToken = $accessToken->getDebugged($clientMock);

    $this->assertInstanceOf('Facebook\Entities\DebugAccessToken', $debuggedAccessToken);
  }

  public function testSerialization()
  {
    $this->markTestSkipped('There is a problem with unserialize.');

    $accessToken = new AccessToken($this->fakeApp, 'foo', time(), 'bar');
    $newAccessToken = unserialize(serialize($accessToken));

    $this->assertEquals($accessToken->getValue(), $newAccessToken->getValue());
    $this->assertEquals($accessToken->getExpiresAt(), $newAccessToken->getExpiresAt());
    $this->assertEquals($accessToken->getMachineId(), $newAccessToken->getMachineId());
  }

}
