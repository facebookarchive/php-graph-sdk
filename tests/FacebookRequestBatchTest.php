<?php

use Facebook\FacebookRequest;
use Facebook\FacebookResponse;
use Facebook\GraphUser;

class FacebookRequestBatchTest extends PHPUnit_Framework_TestCase
{

  public static function setUpBeforeClass()
  {
    FacebookTestHelper::setUpBeforeClass();
  }

  public function testBatchRequest()
  {
    $req1 = new FacebookRequest(null, 'GET', '/me');
    $req2 = new FacebookRequest(null, 'GET', '/me');

    $responses = FacebookRequest::batch(
      FacebookTestHelper::$testSession,
      array($req1, $req2)
    );

    $this->assertEquals(2, count($responses));
    $this->assertTrue($responses[0] instanceof FacebookResponse);
    $this->assertTrue($responses[1] instanceof FacebookResponse);

    $obj1 = $responses[0]->getGraphObject(GraphUser::className());
    $this->assertNotNull($obj1->getName());
    $this->assertNotNull($obj1->getLastName());

    $obj2 = $responses[1]->getGraphObject(GraphUser::className());
    $this->assertNotNull($obj2->getName());
    $this->assertNotNull($obj2->getLastName());
  }

}
