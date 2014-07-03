<?php

use Facebook\FacebookSignedRequestFromInputHelper;

class FooSignedRequestHelper extends FacebookSignedRequestFromInputHelper {
  public function getRawSignedRequest() {
    return null;
  }
}

class FacebookSignedRequestFromInputHelperTest extends PHPUnit_Framework_TestCase
{

  protected $helper;
  public $rawSignedRequestAuthorized = 'vdZXlVEQ5NTRRTFvJ7Jeo_kP4SKnBDvbNP0fEYKS0Sg=.eyJvYXV0aF90b2tlbiI6ImZvb190b2tlbiIsImFsZ29yaXRobSI6IkhNQUMtU0hBMjU2IiwiaXNzdWVkX2F0IjoxNDAyNTUxMDMxLCJ1c2VyX2lkIjoiMTIzIn0=';
  public $rawSignedRequestUnauthorized = 'KPlyhz-whtYAhHWr15N5TkbS_avz-2rUJFpFkfXKC88=.eyJhbGdvcml0aG0iOiJITUFDLVNIQTI1NiIsImlzc3VlZF9hdCI6MTQwMjU1MTA4Nn0=';

  public function setUp()
  {
    $this->helper = new FooSignedRequestHelper('123', 'foo_app_secret');
  }

  public function testSignedRequestDataCanBeRetrievedFromGetData()
  {
    $_GET['signed_request'] = 'foo_signed_request';

    $rawSignedRequest = $this->helper->getRawSignedRequestFromGet();

    $this->assertEquals('foo_signed_request', $rawSignedRequest);
  }

  public function testSignedRequestDataCanBeRetrievedFromPostData()
  {
    $_POST['signed_request'] = 'foo_signed_request';

    $rawSignedRequest = $this->helper->getRawSignedRequestFromPost();

    $this->assertEquals('foo_signed_request', $rawSignedRequest);
  }

  public function testSignedRequestDataCanBeRetrievedFromCookieData()
  {
    $_COOKIE['fbsr_123'] = 'foo_signed_request';

    $rawSignedRequest = $this->helper->getRawSignedRequestFromCookie();

    $this->assertEquals('foo_signed_request', $rawSignedRequest);
  }

  public function testSessionWillBeNullWhenAUserHasNotYetAuthorizedTheApp()
  {
    $this->helper->instantiateSignedRequest($this->rawSignedRequestUnauthorized);
    $session = $this->helper->getSession();

    $this->assertNull($session);
  }

  public function testAFacebookSessionCanBeInstantiatedWhenAUserHasAuthorizedTheApp()
  {
    $this->helper->instantiateSignedRequest($this->rawSignedRequestAuthorized);
    $session = $this->helper->getSession();

    $this->assertInstanceOf('Facebook\FacebookSession', $session);
    $this->assertEquals('foo_token', $session->getToken());
  }

}
