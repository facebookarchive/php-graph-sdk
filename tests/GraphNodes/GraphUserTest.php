<?php

use Facebook\FacebookRequest;
use Facebook\GraphUser;

class GraphUserTest extends PHPUnit_Framework_TestCase
{

  public function testMeReturnsGraphUser()
  {
    $response = (
    new FacebookRequest(
      FacebookTestHelper::$testSession,
      'GET',
      '/me'
    ))->execute()->getGraphObject(GraphUser::className());

    $info = FacebookTestHelper::$testSession->getSessionInfo();

    $this->assertTrue($response instanceof GraphUser);
    $this->assertEquals($info->getId(), $response->getId());
    $this->assertNotNull($response->getName());
    $this->assertNotNull($response->getLastName());
    $this->assertNotNull($response->getLink());
  }

}
