<?php

use Facebook\GraphNodes\Collection;

class CollectionTest extends PHPUnit_Framework_TestCase
{

  public function testAnArrayCanBeInjectedViaTheConstructor()
  {
    $collection = new Collection(['foo', 'bar']);
    $this->assertEquals(['foo', 'bar'], $collection->asArray());
  }

  public function testAStrictArrayWillConvertObjectsToArrayOrStringRecursively()
  {
    $collectionOne = new Collection(['foo', 'bar']);
    $collectionTwo = new Collection([
      'id' => '123',
      'date' => new \DateTime('2014-07-15T03:44:53+0000'),
      'some_collection' => $collectionOne,
    ]);

    $strictArray = $collectionTwo->asStrictArray();

    $this->assertEquals([
      'id' => '123',
      'date' => '2014-07-15T03:44:53+0000',
      'some_collection' => ['foo', 'bar'],
    ], $strictArray);
  }

  public function testACollectionAsAStringWillReturnJson()
  {
    $collection = new Collection(['foo', 'bar', new \DateTime('2014-07-15T03:44:53+0000')]);

    $collectionAsString = (string) $collection;

    $this->assertEquals('["foo","bar","2014-07-15T03:44:53+0000"]', $collectionAsString);
  }

  public function testACollectionCanBeCounted()
  {
    $collection = new Collection(['foo', 'bar', 'baz']);

    $collectionCount = count($collection);

    $this->assertEquals(3, $collectionCount);
  }

  public function testACollectionCanBeAccessedAsAnArray()
  {
    $collection = new Collection(['foo' => 'bar', 'faz' => 'baz']);

    $this->assertEquals('bar', $collection['foo']);
    $this->assertEquals('baz', $collection['faz']);
  }

  public function testACollectionCanBeIteratedOver()
  {
    $collection = new Collection(['foo' => 'bar', 'faz' => 'baz']);

    $this->assertInstanceOf('IteratorAggregate', $collection);

    $newArray = [];

    foreach ($collection as $k => $v) {
      $newArray[$k] = $v;
    }

    $this->assertEquals(['foo' => 'bar', 'faz' => 'baz'], $newArray);
  }

}
