<?php

use Facebook\FacebookRedirectLoginHelper;
use Facebook\FacebookRequest;
use Facebook\FacebookSession;

class FacebookRedirectLoginHelperTest extends PHPUnit_Framework_TestCase
{

  const REDIRECT_URL = 'http://invalid.zzz';

  public function testLoginURL()
  {
    $helper = new FacebookRedirectLoginHelper(
      self::REDIRECT_URL,
      FacebookTestCredentials::$appId,
      FacebookTestCredentials::$appSecret
    );
    $helper->disableSessionStatusCheck();
    $loginUrl = $helper->getLoginUrl();
    $state = $_SESSION['FBRLH_state'];
    $params = array(
      'client_id' => FacebookTestCredentials::$appId,
      'redirect_uri' => self::REDIRECT_URL,
      'state' => $state,
      'sdk' => 'php-sdk-' . FacebookRequest::VERSION,
      'scope' => implode(',', array())
    );
    $expectedUrl = 'https://www.facebook.com/' . FacebookRequest::GRAPH_API_VERSION . '/dialog/oauth?';
    $this->assertTrue(strpos($loginUrl, $expectedUrl) === 0, 'Unexpected base login URL returned from getLoginUrl().');
    foreach ($params as $key => $value) {
      $this->assertContains($key . '=' . urlencode($value), $loginUrl);
    }
  }

  public function testReRequestUrlContainsState()
  {
    $helper = new FacebookRedirectLoginHelper(
      self::REDIRECT_URL,
      FacebookTestCredentials::$appId,
      FacebookTestCredentials::$appSecret
    );
    $helper->disableSessionStatusCheck();

    $reRequestUrl = $helper->getReRequestUrl();
    $state = $_SESSION['FBRLH_state'];

    $this->assertContains('state=' . urlencode($state), $reRequestUrl);
  }

  public function testLogoutURL()
  {
    $helper = new FacebookRedirectLoginHelper(
      self::REDIRECT_URL,
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

  public function testLogoutURLFailsWithAppSession()
  {
    $helper = new FacebookRedirectLoginHelper(
      self::REDIRECT_URL,
      FacebookTestCredentials::$appId,
      FacebookTestCredentials::$appSecret
    );
    $helper->disableSessionStatusCheck();
    $session = FacebookTestHelper::getAppSession();
    $this->setExpectedException(
      'Facebook\\FacebookSDKException', 'Cannot generate a Logout URL with an App Session.'
    );
    $helper->getLogoutUrl(
      $session, self::REDIRECT_URL
    );
  }
  
  public function testCSPRNG()
  {
    $helper = new FacebookRedirectLoginHelper(
      self::REDIRECT_URL,
      FacebookTestCredentials::$appId,
      FacebookTestCredentials::$appSecret
    );
    $this->assertEquals(1, preg_match('/^([0-9a-f]+)$/', $helper->random(32)));
  }

}
