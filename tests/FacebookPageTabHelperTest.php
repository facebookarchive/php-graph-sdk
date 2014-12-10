<?php

use Facebook\FacebookPageTabHelper;

class FacebookPageTabHelperTest extends PHPUnit_Framework_TestCase
{

  protected $rawSignedRequestAuthorized = '6Hi26ECjkj347belC0O8b8H5lwiIz5eA6V9VVjTg-HU=.eyJhbGdvcml0aG0iOiJITUFDLVNIQTI1NiIsImlzc3VlZF9hdCI6MzIxLCJvYXV0aF90b2tlbiI6ImZvb190b2tlbiIsInVzZXJfaWQiOiIxMjMiLCJwYWdlIjp7ImlkIjoiNDIiLCJsaWtlZCI6dHJ1ZSwiYWRtaW4iOmZhbHNlfX0=';

  public function testPageDataCanBeAccessed()
  {
    $_POST['signed_request'] = $this->rawSignedRequestAuthorized;
    $helper = new FacebookPageTabHelper('123', 'foo_app_secret');

    $this->assertTrue($helper->isLiked());
    $this->assertFalse($helper->isAdmin());
    $this->assertEquals('42', $helper->getPageId());
    $this->assertEquals('42', $helper->getPageData('id'));
    $this->assertEquals('default', $helper->getPageData('foo', 'default'));
  }

}
