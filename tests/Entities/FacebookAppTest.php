<?php

use Facebook\Entities\FacebookApp;

class FacebookAppTest extends \PHPUnit_Framework_TestCase
{
  public function testGetId()
  {
    $app = new FacebookApp('id', 'secret');

    $this->assertEquals('id', $app->getId());
  }

  public function testGetSecret()
  {
    $app = new FacebookApp('id', 'secret');

    $this->assertEquals('secret', $app->getSecret());
  }

  public function testGetAccessToken()
  {
    $app = new FacebookApp('id', 'secret');
    $accessToken = $app->getAccessToken();

    $this->assertInstanceOf('Facebook\Entities\AccessToken', $accessToken);
    $this->assertEquals('id|secret', (string)$accessToken);
  }
}
