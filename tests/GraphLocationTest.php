<?php

use Facebook\FacebookRequest;
use Facebook\GraphLocation;
use Facebook\GraphObject;

class GraphLocationTest extends PHPUnit_Framework_TestCase
{

  public function testLocation()
  {
    $response = (
    new FacebookRequest(
      FacebookTestHelper::$testSession,
      'GET',
      '/104048449631599'
    ))->execute()->getGraphObject();
    $this->assertTrue($response instanceof GraphObject);

    $location = $response->getProperty('location', GraphLocation::className());
    $this->assertTrue(is_float($location->getLatitude()));
    $this->assertTrue(is_float($location->getLongitude()));
  }

}
