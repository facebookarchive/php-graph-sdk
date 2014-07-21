<?php

use Facebook\GraphNodes\GraphUser;

class GraphUserTest extends PHPUnit_Framework_TestCase
{

  public function testPagePropertiesWillGetCastAsGraphPageObjects()
  {
    $graphObject = new GraphUser([
      'id' => '123',
      'name' => 'Foo User',
      'hometown' => [
        'id' => '1',
        'name' => 'Foo Place',
      ],
      'location' => [
        'id' => '2',
        'name' => 'Bar Place',
      ],
    ]);
    $hometown = $graphObject->getHometown();
    $location = $graphObject->getLocation();

    $this->assertInstanceOf('Facebook\GraphNodes\GraphPage', $hometown);
    $this->assertInstanceOf('Facebook\GraphNodes\GraphPage', $location);
  }

  public function testUserPropertiesWillGetCastAsGraphUserObjects()
  {
    $graphObject = new GraphUser([
      'id' => '123',
      'name' => 'Foo User',
      'significant_other' => [
        'id' => '1337',
        'name' => 'Bar User',
      ],
    ]);
    $significantOther = $graphObject->getSignificantOther();

    $this->assertInstanceOf('Facebook\GraphNodes\GraphUser', $significantOther);
  }

}
