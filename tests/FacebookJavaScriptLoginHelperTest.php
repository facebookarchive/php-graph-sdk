<?php

use Facebook\FacebookJavaScriptLoginHelper;
use Facebook\FacebookSession;

class FacebookJavaScriptLoginHelperTest extends PHPUnit_Framework_TestCase
{

  public function testGetSessionFromCookie() {
    $helper = new FacebookJavaScriptLoginHelper(
      FacebookTestCredentials::$appId
    );
    $signedRequest = FacebookSessionTest::makeSignedRequest(array(
      'oauth_token' => 'token'
    ));
    $_COOKIE['fbsr_' . FacebookTestCredentials::$appId] = $signedRequest;
    $session = $helper->getSession();
    $this->assertTrue($session instanceof FacebookSession);
    $this->assertTrue($session->getToken() == 'token');
  }

}
