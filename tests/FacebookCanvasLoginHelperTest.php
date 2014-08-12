<?php

use Facebook\FacebookCanvasLoginHelper;

class FacebookCanvasLoginHelperTest extends PHPUnit_Framework_TestCase
{

  public $rawSignedRequestAuthorized = 'vdZXlVEQ5NTRRTFvJ7Jeo_kP4SKnBDvbNP0fEYKS0Sg=.eyJvYXV0aF90b2tlbiI6ImZvb190b2tlbiIsImFsZ29yaXRobSI6IkhNQUMtU0hBMjU2IiwiaXNzdWVkX2F0IjoxNDAyNTUxMDMxLCJ1c2VyX2lkIjoiMTIzIn0=';
  protected $helper;

  public function setUp()
  {
    $this->helper = new FacebookCanvasLoginHelper('123', 'foo_app_secret');
  }

  public function testSignedRequestDataCanBeRetrievedFromPostData()
  {
    $_POST['signed_request'] = $this->rawSignedRequestAuthorized;

    $rawSignedRequest = $this->helper->getRawSignedRequest();

    $this->assertEquals($this->rawSignedRequestAuthorized, $rawSignedRequest);
  }

}
