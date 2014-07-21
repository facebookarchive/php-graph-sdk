<?php

use Facebook\GraphNodes\GraphAlbum;

class GraphAlbumTest extends PHPUnit_Framework_TestCase
{

  public function testFromPropertyWillGetCastAsGraphUserObject()
  {
    $graphObject = new GraphAlbum([
      'id' => '123',
      'name' => 'Foo Album',
      'from' => [
        'id' => '1',
        'name' => 'Foo McBar',
      ]
    ]);
    $from = $graphObject->getFrom();

    $this->assertInstanceOf('Facebook\GraphNodes\GraphUser', $from);
  }

  public function testPlacePropertyWillGetCastAsGraphPageObject()
  {
    $graphObject = new GraphAlbum([
      'id' => '123',
      'name' => 'Foo Album',
      'place' => [
        'id' => '1',
        'name' => 'For Bar Place',
      ]
    ]);
    $place = $graphObject->getPlace();

    $this->assertInstanceOf('Facebook\GraphNodes\GraphPage', $place);
  }

}
