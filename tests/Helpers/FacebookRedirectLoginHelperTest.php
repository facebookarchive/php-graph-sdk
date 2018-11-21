<?php
/**
 * Copyright 2017 Facebook, Inc.
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
namespace Facebook\Tests\Helpers;

use Facebook\Facebook;
use Facebook\FacebookApp;
use Facebook\FacebookClient;
use Facebook\Helpers\FacebookRedirectLoginHelper;
use Facebook\PersistentData\FacebookMemoryPersistentDataHandler;
use Facebook\Tests\Fixtures\FooPseudoRandomStringGenerator;
use Facebook\Tests\Fixtures\FooRedirectLoginOAuth2Client;

class FacebookRedirectLoginHelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FacebookMemoryPersistentDataHandler
     */
    protected $persistentDataHandler;

    /**
     * @var FacebookRedirectLoginHelper
     */
    protected $redirectLoginHelper;

    const REDIRECT_URL = 'http://invalid.zzz';
    const FOO_CODE = "foo_code";
    const FOO_ENFORCE_HTTPS = "foo_enforce_https";
    const FOO_STATE = "foo_state";
    const FOO_PARAM = "some_param=blah";

    protected function setUp()
    {
        $this->persistentDataHandler = new FacebookMemoryPersistentDataHandler();

        $app = new FacebookApp('123', 'foo_app_secret');
        $oAuth2Client = new FooRedirectLoginOAuth2Client($app, new FacebookClient(), 'v1337');
        $this->redirectLoginHelper = new FacebookRedirectLoginHelper($oAuth2Client, $this->persistentDataHandler);
    }

    public function testLoginURL()
    {
        $scope = ['foo', 'bar'];
        $loginUrl = $this->redirectLoginHelper->getLoginUrl(self::REDIRECT_URL, $scope);

        $expectedUrl = 'https://www.facebook.com/v1337/dialog/oauth?';
        $this->assertTrue(strpos($loginUrl, $expectedUrl) === 0, 'Unexpected base login URL returned from getLoginUrl().');

        $params = [
            'client_id' => '123',
            'redirect_uri' => self::REDIRECT_URL,
            'state' => $this->persistentDataHandler->get('state'),
            'sdk' => 'php-sdk-' . Facebook::VERSION,
            'scope' => implode(',', $scope),
        ];
        foreach ($params as $key => $value) {
            $this->assertContains($key . '=' . urlencode($value), $loginUrl);
        }
    }

    public function testLogoutURL()
    {
        $logoutUrl = $this->redirectLoginHelper->getLogoutUrl('foo_token', self::REDIRECT_URL);
        $expectedUrl = 'https://www.facebook.com/logout.php?';
        $this->assertTrue(strpos($logoutUrl, $expectedUrl) === 0, 'Unexpected base logout URL returned from getLogoutUrl().');

        $params = [
            'next' => self::REDIRECT_URL,
            'access_token' => 'foo_token',
        ];
        foreach ($params as $key => $value) {
            $this->assertTrue(
                strpos($logoutUrl, $key . '=' . urlencode($value)) !== false
            );
        }
    }

    public function testAnAccessTokenCanBeObtainedFromRedirect()
    {
        $this->persistentDataHandler->set('state', static::FOO_STATE);

        $_GET['code'] = static::FOO_CODE;
        $_GET['enforce_https'] = static::FOO_ENFORCE_HTTPS;
        $_GET['state'] = static::FOO_STATE;

        $fullUrl = self::REDIRECT_URL . '?state=' . static::FOO_STATE . '&enforce_https=' . static::FOO_ENFORCE_HTTPS . '&code=' . static::FOO_CODE . '&' . static::FOO_PARAM;

        $accessToken = $this->redirectLoginHelper->getAccessToken($fullUrl);

        // 'code', 'enforce_https' and 'state' should be stripped from the URL
        $expectedUrl = self::REDIRECT_URL . '?' . static::FOO_PARAM;
        $expectedString = 'foo_token_from_code|' . static::FOO_CODE . '|' . $expectedUrl;

        $this->assertEquals($expectedString, $accessToken->getValue());
    }

    public function testACustomCsprsgCanBeInjected()
    {
        $app = new FacebookApp('123', 'foo_app_secret');
        $accessTokenClient = new FooRedirectLoginOAuth2Client($app, new FacebookClient(), 'v1337');
        $fooPrsg = new FooPseudoRandomStringGenerator();
        $helper = new FacebookRedirectLoginHelper($accessTokenClient, $this->persistentDataHandler, null, $fooPrsg);

        $loginUrl = $helper->getLoginUrl(self::REDIRECT_URL);

        $this->assertContains('state=csprs123', $loginUrl);
    }

    public function testThePseudoRandomStringGeneratorWillAutoDetectCsprsg()
    {
        $this->assertInstanceOf(
            'Facebook\PseudoRandomString\PseudoRandomStringGeneratorInterface',
            $this->redirectLoginHelper->getPseudoRandomStringGenerator()
        );
    }
}
