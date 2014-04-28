<?php

use Facebook\FacebookRequest;

class FacebookRequestTest extends PHPUnit_Framework_TestCase
{

  public static function setUpBeforeClass()
  {
    FacebookTestHelper::setUpBeforeClass();
  }

  public function testMe()
  {
    $response = (
      new FacebookRequest(
        FacebookTestHelper::$testSession,
        'GET',
        '/me'
      ))->execute()->getGraphObject();
    $this->assertNotNull($response->getProperty('id'));
    $this->assertNotNull($response->getProperty('name'));
  }

}