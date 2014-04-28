<?php

use Facebook\FacebookCanvasLoginHelper;
use Facebook\FacebookSession;

class FacebookCanvasLoginHelperTest extends PHPUnit_Framework_TestCase
{

  public static function setUpBeforeClass()
  {
    FacebookTestHelper::setUpBeforeClass();
  }

  public static function tearDownAfterClass()
  {

  }

  public function testGetSessionFromCanvasGET() {
    $helper = new FacebookCanvasLoginHelper();
    $signedRequest = FacebookSessionTest::makeSignedRequest(array(
      'oauth_token' => 'token'
    ));
    $_GET['signed_request'] = $signedRequest;
    $session = $helper->getSession();
    $this->assertTrue($session instanceof FacebookSession);
    $this->assertTrue($session->getToken() == 'token');
  }

  public function testGetSessionFromCanvasPOST() {
    $helper = new FacebookCanvasLoginHelper();
    $signedRequest = FacebookSessionTest::makeSignedRequest(array(
      'oauth_token' => 'token'
    ));
    $_POST['signed_request'] = $signedRequest;
    $session = $helper->getSession();
    $this->assertTrue($session instanceof FacebookSession);
    $this->assertTrue($session->getToken() == 'token');
  }
}
