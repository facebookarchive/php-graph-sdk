<?php

use Facebook\GraphNodes\GraphList;

class GraphListTest extends PHPUnit_Framework_TestCase
{

  public function testValidDataListWillBeAddedToTheInternalData()
  {
    $graphList = new GraphList([
      'data' => [
        ['foo' => 'bar'],
        ['faz' => 'baz'],
      ],
    ]);
    $items = $graphList->asArray();

    $this->assertInstanceOf('Facebook\GraphNodes\GraphObject', $items[0]);
    $this->assertInstanceOf('Facebook\GraphNodes\GraphObject', $items[1]);
  }

  public function testInvalidListDataWillNotBeAddedToTheInternalData()
  {
    $GraphList = new GraphList(['foo' => 'bar']);
    $items = $GraphList->asArray();

    $this->assertEquals([], $items);
  }

  // @TODO Test pagination

}
