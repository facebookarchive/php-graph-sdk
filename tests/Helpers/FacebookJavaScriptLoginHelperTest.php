<?php

use Facebook\FacebookJavaScriptLoginHelper;

class FacebookJavaScriptLoginHelperTest extends PHPUnit_Framework_TestCase
{

  public $appId = '123';
  public $appSecret = 'foo_app_secret';
  public $rawSignedRequestAuthorized = 'vdZXlVEQ5NTRRTFvJ7Jeo_kP4SKnBDvbNP0fEYKS0Sg=.eyJvYXV0aF90b2tlbiI6ImZvb190b2tlbiIsImFsZ29yaXRobSI6IkhNQUMtU0hBMjU2IiwiaXNzdWVkX2F0IjoxNDAyNTUxMDMxLCJ1c2VyX2lkIjoiMTIzIn0=';

  public function testARawSignedRequestCanBeRetrievedFromCookieData()
  {
    $_COOKIE['fbsr_123'] = $this->rawSignedRequestAuthorized;

    $helper = new FacebookJavaScriptLoginHelper($this->appId, $this->appSecret);

    $rawSignedRequest = $helper->getRawSignedRequest();

    $this->assertEquals($this->rawSignedRequestAuthorized, $rawSignedRequest);
  }

}
