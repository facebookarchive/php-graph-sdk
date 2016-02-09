<?php
/**
 * Copyright 2016 Facebook, Inc.
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
namespace Facebook\Tests\Authentication;

use Facebook\Authentication\AccessToken;

class AccessTokenTest extends \PHPUnit_Framework_TestCase
{

    public function testAnAccessTokenCanBeReturnedAsAString()
    {
        $accessToken = new AccessToken('foo_token');

        $this->assertEquals('foo_token', $accessToken->getValue());
        $this->assertEquals('foo_token', (string)$accessToken);
    }

    public function testAnAppSecretProofWillBeProperlyGenerated()
    {
        $accessToken = new AccessToken('foo_token');

        $appSecretProof = $accessToken->getAppSecretProof('shhhhh!is.my.secret');

        $this->assertEquals('796ba0d8a6b339e476a7b166a9e8ac0a395f7de736dc37de5f2f4397f5854eb8', $appSecretProof);
    }

    public function testAnAppAccessTokenCanBeDetected()
    {
        $normalToken = new AccessToken('foo_token');
        $isNormalToken = $normalToken->isAppAccessToken();

        $this->assertFalse($isNormalToken, 'Normal access token not expected to look like an app access token.');

        $appToken = new AccessToken('123|secret');
        $isAppToken = $appToken->isAppAccessToken();

        $this->assertTrue($isAppToken, 'App access token expected to look like an app access token.');
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

    public function testAnAppAccessTokenDoesNotExpire()
    {
        $appToken = new AccessToken('123|secret');
        $hasExpired = $appToken->isExpired();

        $this->assertFalse($hasExpired, 'App access token not expected to expire.');
    }

    public function testAnAccessTokenCanExpire()
    {
        $expireTime = time() - 100;
        $appToken = new AccessToken('foo_token', $expireTime);
        $hasExpired = $appToken->isExpired();

        $this->assertTrue($hasExpired, 'Expected 100 second old access token to be expired.');
    }

    public function testAccessTokenCanBeSerialized()
    {
        $accessToken = new AccessToken('foo', time(), 'bar');

        $newAccessToken = unserialize(serialize($accessToken));

        $this->assertEquals((string)$accessToken, (string)$newAccessToken);
        $this->assertEquals($accessToken->getExpiresAt(), $newAccessToken->getExpiresAt());
    }

    private function aWeekFromNow()
    {
        return time() + (60 * 60 * 24 * 7);//a week from now
    }
}
