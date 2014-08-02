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
namespace Facebook\Tests\Helpers;

use Mockery as m;
use Facebook\Helpers\FacebookRedirectLoginHelper;
use Facebook\FacebookClient;

class FacebookRedirectLoginHelperTest extends \PHPUnit_Framework_TestCase
{
  protected $helper;

  const REDIRECT_URL = 'http://invalid.zzz';

  public function setUp()
  {
    $fakeApp = m::mock('Facebook\Entities\FacebookApp', ['123', 'foo_app_secret'])->makePartial();
    $fakeClient = m::mock('Facebook\FacebookClient')->makePartial();
    
    $this->helper = new FacebookRedirectLoginHelper($fakeClient, $fakeApp);
  }

  public function testLoginURL()
  {
    $this->helper->disableSessionStatusCheck();
    $loginUrl = $this->helper->getLoginUrl(self::REDIRECT_URL);
    $state = $_SESSION['FBRLH_state'];
    $params = array(
      'client_id' => '123',
      'redirect_uri' => self::REDIRECT_URL,
      'state' => $state,
      'sdk' => 'php-sdk-' . FacebookClient::VERSION,
      'scope' => implode(',', array())
    );
    $expectedUrl = 'https://www.facebook.com/dialog/oauth?';
    $this->assertTrue(strpos($loginUrl, $expectedUrl) !== false);
    foreach ($params as $key => $value) {
      $this->assertTrue(
        strpos($loginUrl, $key . '=' . urlencode($value)) !== false
      );
    }
  }

  public function testLogoutURL()
  {
    $this->helper->disableSessionStatusCheck();

    $fakeApp = m::mock('Facebook\Entities\FacebookApp', ['foo_app_id', 'foo_app_secret']);
    $fakeAccessToken = m::mock('Facebook\Entities\AccessToken', [$fakeApp, 'foo_token'])->makePartial();

    $logoutUrl = $this->helper->getLogoutUrl($fakeAccessToken, self::REDIRECT_URL);

    $params = array(
      'next' => self::REDIRECT_URL,
      'access_token' => 'foo_token'
    );
    $expectedUrl = 'https://www.facebook.com/logout.php?';
    $this->assertTrue(strpos($logoutUrl, $expectedUrl) !== false);
    foreach ($params as $key => $value) {
      $this->assertTrue(
        strpos($logoutUrl, $key . '=' . urlencode($value)) !== false
      );
    }
  }
  
  /**
   * @dataProvider provideUris
   */
  public function testGetFilterdUriRemoveFacebookQueryParams($uri, $expected)
  {
    $this->helper->disableSessionStatusCheck();

    $class = new \ReflectionClass('Facebook\\Helpers\\FacebookRedirectLoginHelper');
    $method = $class->getMethod('getFilteredUri');
    $method->setAccessible(true);

    $currentUri = $method->invoke($this->helper, $uri);
    $this->assertEquals($expected, $currentUri);
  }

  public function provideUris()
  {
    return array(
      array(
        'http://localhost/something?state=0000&foo=bar&code=abcd',
        'http://localhost/something?foo=bar',
      ),
      array(
        'https://localhost/something?state=0000&foo=bar&code=abcd',
        'https://localhost/something?foo=bar',
      ),
      array(
        'http://localhost/something?state=0000&foo=bar&error=abcd&error_reason=abcd&error_description=abcd&error_code=1',
        'http://localhost/something?foo=bar',
      ),
      array(
        'https://localhost/something?state=0000&foo=bar&error=abcd&error_reason=abcd&error_description=abcd&error_code=1',
        'https://localhost/something?foo=bar',
      ),
      array(
        'http://localhost/something?state=0000&foo=bar&error=abcd',
        'http://localhost/something?state=0000&foo=bar&error=abcd',
      ),
      array(
        'https://localhost/something?state=0000&foo=bar&error=abcd',
        'https://localhost/something?state=0000&foo=bar&error=abcd',
      ),
    );
  }
  
  public function testCSPRNG()
  {
    $class = new \ReflectionClass('Facebook\\Helpers\\FacebookRedirectLoginHelper');
    $method = $class->getMethod('random');
    $method->setAccessible(true);

    $this->assertEquals(1, preg_match('/^([0-9a-f]+)$/', $method->invoke($this->helper, 32)));
  }

}
