<?php

use Facebook\Facebook;
use Facebook\Helpers\FacebookRedirectLoginHelper;

class FacebookRedirectLoginHelperTest extends PHPUnit_Framework_TestCase
{

  const REDIRECT_URL = 'http://invalid.zzz';

  public function testLoginURL()
  {
    $helper = new FacebookRedirectLoginHelper(
      FacebookTestCredentials::$appId,
      FacebookTestCredentials::$appSecret
    );
    $helper->disableSessionStatusCheck();
    $loginUrl = $helper->getLoginUrl(self::REDIRECT_URL);
    $state = $_SESSION['FBRLH_state'];
    $params = [
      'client_id' => FacebookTestCredentials::$appId,
      'redirect_uri' => self::REDIRECT_URL,
      'state' => $state,
      'sdk' => 'php-sdk-' . Facebook::VERSION,
      'scope' => '',
    ];
    $version = Facebook::getDefaultGraphApiVersion();
    $expectedUrl = 'https://www.facebook.com/' . $version . '/dialog/oauth?';
    $this->assertTrue(strpos($loginUrl, $expectedUrl) === 0);
    foreach ($params as $key => $value) {
      $this->assertTrue(
        strpos($loginUrl, $key . '=' . urlencode($value)) !== false
      );
    }
  }

  public function testLogoutURL()
  {
    $helper = new FacebookRedirectLoginHelper(
      FacebookTestCredentials::$appId,
      FacebookTestCredentials::$appSecret
    );
    $helper->disableSessionStatusCheck();
    $logoutUrl = $helper->getLogoutUrl('foo_token', self::REDIRECT_URL);
    $params = [
      'next' => self::REDIRECT_URL,
      'access_token' => 'foo_token',
    ];
    $expectedUrl = 'https://www.facebook.com/logout.php?';
    $this->assertTrue(strpos($logoutUrl, $expectedUrl) === 0);
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
    $helper = new FacebookRedirectLoginHelper(
      FacebookTestCredentials::$appId,
      FacebookTestCredentials::$appSecret
    );
    $helper->disableSessionStatusCheck();

    $class = new ReflectionClass('Facebook\\Helpers\\FacebookRedirectLoginHelper');
    $method = $class->getMethod('getFilteredUri');
    $method->setAccessible(true);

    $currentUri = $method->invoke($helper, $uri);
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
    $helper = new FacebookRedirectLoginHelper(
      FacebookTestCredentials::$appId,
      FacebookTestCredentials::$appSecret
    );
    
    $class = new ReflectionClass('Facebook\\Helpers\\FacebookRedirectLoginHelper');
    $method = $class->getMethod('random');
    $method->setAccessible(true);

    $this->assertEquals(1, preg_match('/^([0-9a-f]+)$/', $method->invoke($helper, 32)));
  }

}
