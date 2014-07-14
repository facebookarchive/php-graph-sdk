<?php

use Facebook\Helpers\FacebookRedirectLoginHelper;
use Facebook\FacebookRequest;

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
    $params = array(
      'client_id' => FacebookTestCredentials::$appId,
      'redirect_uri' => self::REDIRECT_URL,
      'state' => $state,
      'sdk' => 'php-sdk-' . FacebookRequest::VERSION,
      'scope' => implode(',', array())
    );
    $expectedUrl = 'https://www.facebook.com/v2.0/dialog/oauth?';
    $this->assertTrue(strpos($loginUrl, $expectedUrl) !== false);
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
    $logoutUrl = $helper->getLogoutUrl(
      FacebookTestHelper::$testSession, self::REDIRECT_URL
    );
    $params = array(
      'next' => self::REDIRECT_URL,
      'access_token' => FacebookTestHelper::$testSession->getToken()
    );
    $expectedUrl = 'https://www.facebook.com/logout.php?';
    $this->assertTrue(strpos($logoutUrl, $expectedUrl) !== false);
    foreach ($params as $key => $value) {
      $this->assertTrue(
        strpos($logoutUrl, $key . '=' . urlencode($value)) !== false
      );
    }
  }
  
  public function testGetFilterdUriRemoveFacebookQueryParams()
  {
    $helper = new FacebookRedirectLoginHelper(
      FacebookTestCredentials::$appId,
      FacebookTestCredentials::$appSecret
    );
    $helper->disableSessionStatusCheck();

    $class = new ReflectionClass('Facebook\\Helpers\\FacebookRedirectLoginHelper');
    $method = $class->getMethod('getFilteredUri');
    $method->setAccessible(true);

    $currentUri = $method->invoke($helper, 'http://localhost/something?state=0000&foo=bar&code=abcd');
    $this->assertEquals('http://localhost/something?foo=bar', $currentUri);
  }
  
  public function testCSPRNG()
  {
    $helper = new FacebookRedirectLoginHelper(
      FacebookTestCredentials::$appId,
      FacebookTestCredentials::$appSecret
    );
	$this->assertEquals(1, preg_match('/^([0-9a-f]+)$/', $helper->random(32)));
  }

}
