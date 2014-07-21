<?php

use Facebook\GraphNodes\GraphPage;

class GraphPageTest extends PHPUnit_Framework_TestCase
{

  public function testPagePropertiesReturnGraphPageObjects()
  {
    $graphObject = new GraphPage([
      'id' => '123',
      'name' => 'Foo Page',
      'best_page' => [
        'id' => '1',
        'name' => 'Bar Page',
      ],
      'global_brand_parent_page' => [
        'id' => '2',
        'name' => 'Faz Page',
      ],
    ]);
    $bestPage = $graphObject->getBestPage();
    $globalBrandParentPage = $graphObject->getGlobalBrandParentPage();

    $this->assertInstanceOf('Facebook\GraphNodes\GraphPage', $bestPage);
    $this->assertInstanceOf('Facebook\GraphNodes\GraphPage', $globalBrandParentPage);
  }

  public function testLocationPropertyWillGetCastAsGraphLocationObject()
  {
    $graphObject = new GraphPage([
      'id' => '123',
      'name' => 'Foo Page',
      'location' => [
        'city' => 'Washington',
        'country' => 'United States',
        'latitude' => 38.881634205431,
        'longitude' => -77.029121075722,
        'state' => 'DC',
      ],
    ]);
    $location = $graphObject->getLocation();

    $this->assertInstanceOf('Facebook\GraphNodes\GraphLocation', $location);
  }

}
