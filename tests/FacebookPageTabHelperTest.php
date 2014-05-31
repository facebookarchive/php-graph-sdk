<?php

use Facebook\FacebookPageTabHelper;
use Facebook\FacebookSession;

class FacebookPageTabHelperTest extends PHPUnit_Framework_TestCase
{

  public static function setUpBeforeClass()
  {
    FacebookTestHelper::setUpBeforeClass();
  }

  public static function tearDownAfterClass()
  {

  }

  public function testGetSessionFromPageTabGET() {
    $signedRequest = FacebookSessionTest::makeSignedRequest(array(
      'oauth_token' => 'token',
      'page' => array(
        'liked' => 'true',
        'admin' => 'false',
        'id' => 42
      ),
      'user_id' => '42'
    ));
    $_GET['signed_request'] = $signedRequest;
    $helper = new FacebookPageTabHelper();
    $session = $helper->getSession();
    $this->assertTrue($session instanceof FacebookSession);
    $this->assertTrue($session->getToken() == 'token');
    $this->assertTrue($helper->isLiked());
    $this->assertFalse($helper->isAdmin());
    $this->assertEquals(42, $helper->getPageId());
    $this->assertEquals('42', $helper->getUserId());
  }

  public function testGetSessionFromPageTabPOST() {
    $signedRequest = FacebookSessionTest::makeSignedRequest(array(
      'oauth_token' => 'token',
      'page' => array(
        'liked' => 'true',
        'admin' => 'false',
        'id' => 42
      )    ));
    $_POST['signed_request'] = $signedRequest;
    $helper = new FacebookPageTabHelper();
    $session = $helper->getSession();
    $this->assertTrue($session instanceof FacebookSession);
    $this->assertTrue($session->getToken() == 'token');
    $this->assertTrue($helper->isLiked());
    $this->assertFalse($helper->isAdmin());
    $this->assertEquals(42, $helper->getPageId());
  }

  public function testLoggedOutPageTab() {
    $signedRequest = FacebookSessionTest::makeSignedRequest(array(
      'page' => array(
        'liked' => 'false',
        'admin' => 'true',
        'id' => 42
      )
    ));
    $_POST['signed_request'] = $signedRequest;
    $helper = new FacebookPageTabHelper();
    $session = $helper->getSession();
    $this->assertNull($session);
    $this->assertFalse($helper->isLiked());
    $this->assertTrue($helper->isAdmin());
    $this->assertEquals(42, $helper->getPageId());
  }

}