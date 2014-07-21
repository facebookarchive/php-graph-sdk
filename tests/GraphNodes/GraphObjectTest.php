<?php

use Facebook\GraphNodes\GraphObject;

class MyFooSubClassGraphObject extends GraphObject {}

class MyFooGraphObject extends GraphObject {
  protected $graphObjectMap = [
    'foo_object' => 'MyFooSubClassGraphObject',
  ];
}

class GraphObjectTest extends PHPUnit_Framework_TestCase
{

  public function testAnEmptyBaseGraphObjectCanInstantiate()
  {
    $graphObject = new GraphObject();
    $backingData = $graphObject->asArray();

    $this->assertInstanceOf('Facebook\GraphNodes\GraphObject', $graphObject);
    $this->assertEquals([], $backingData);
  }

  public function testAGraphObjectCanInstantiateWithData()
  {
    $graphObject = new GraphObject(['foo' => 'bar']);
    $backingData = $graphObject->asArray();

    $this->assertEquals(['foo' => 'bar'], $backingData);
  }

  public function testOnlyNumericArraysAreCastableAsAGraphList()
  {
    $shouldPassOne = GraphObject::isCastableAsGraphList([]);
    $shouldPassTwo = GraphObject::isCastableAsGraphList(['foo', 'bar']);
    $shouldFail = GraphObject::isCastableAsGraphList(['faz' => 'baz']);

    $this->assertTrue($shouldPassOne, 'Expected the given array to be castable as a GraphList.');
    $this->assertTrue($shouldPassTwo, 'Expected the given array to be castable as a GraphList.');
    $this->assertFalse($shouldFail, 'Expected the given array to not be castable as a GraphList.');
  }

  public function testDatesThatShouldBeCastAsDateTimeObjectsAreDetected()
  {
    $shouldPass = GraphObject::shouldCastAsDateTime('start_time');
    $shouldFail = GraphObject::shouldCastAsDateTime('foo_time');

    $this->assertTrue($shouldPass, 'Expected the key "start_time" should be cast as a DateTime object.');
    $this->assertFalse($shouldFail, 'Expected the key "foo_time" to not be cast as a DateTime object.');
  }

  public function testATimeStampCanBeConvertedToADateTimeObject()
  {
    $someTimeStampFromGraph = 1405547020;
    $dateTime = GraphObject::castToDateTime($someTimeStampFromGraph);
    $prettyDate = $dateTime->format(\DateTime::RFC1036);
    $timeStamp = $dateTime->getTimestamp();

    $this->assertInstanceOf('DateTime', $dateTime);
    $this->assertEquals('Wed, 16 Jul 14 21:43:40 +0000', $prettyDate);
    $this->assertEquals(1405547020, $timeStamp);
  }

  public function testAGraphDateStringCanBeConvertedToADateTimeObject()
  {
    $someDateStringFromGraph = '2014-07-15T03:44:53+0000';
    $dateTime = GraphObject::castToDateTime($someDateStringFromGraph);
    $prettyDate = $dateTime->format(\DateTime::RFC1036);
    $timeStamp = $dateTime->getTimestamp();

    $this->assertInstanceOf('DateTime', $dateTime);
    $this->assertEquals('Tue, 15 Jul 14 03:44:53 +0000', $prettyDate);
    $this->assertEquals(1405395893, $timeStamp);
  }

  public function testTheCorrectCalledClassNameWillBeReturned()
  {
    $classNameOne = GraphObject::className();
    $classNameTwo = MyFooGraphObject::className();

    $this->assertEquals('Facebook\GraphNodes\GraphObject', $classNameOne);
    $this->assertEquals('MyFooGraphObject', $classNameTwo);
  }

  public function testCastingAsASubClassObjectWillInstantiateTheSubClass()
  {
    $mySubClassObject = MyFooGraphObject::make(['foo' => 'bar']);

    $this->assertInstanceOf('MyFooGraphObject', $mySubClassObject);
  }

  public function testASubClassMappingWillAutomaticallyInstantiateSubClass()
  {
    $mySubClassObject = MyFooGraphObject::make([
        'id' => '123',
        'name' => 'Foo Name',
        'foo_object' => [
          'id' => '1337',
          'name' => 'Should be sub classed!',
        ],
      ]);
    $fooObject = $mySubClassObject->getFooObject();

    $this->assertInstanceOf('MyFooSubClassGraphObject', $fooObject);
  }

  public function testAnUnknownSubObjectTypeWillBeCastAsAGenericGraphObject()
  {
    $mySubClassObject = MyFooGraphObject::make([
        'id' => '123',
        'name' => 'Foo Name',
        'unknown_object' => [
          'id' => '1337',
          'name' => 'Should be generic!',
        ],
      ]);
    $unknownObject = $mySubClassObject->getUnknownObject();

    $this->assertInstanceOf('MyFooGraphObject', $mySubClassObject);
    $this->assertInstanceOf('Facebook\GraphNodes\GraphObject', $unknownObject);
  }

  public function testAListFromGraphWillBeCastAsAGraphList()
  {
    $dataFromGraph = [
      'data' => [
        [
          'id' => '123',
          'name' => 'Foo McBar',
          'link' => 'http://facebook/foo',
        ],
        [
          'id' => '1337',
          'name' => 'Bar McBaz',
          'link' => 'http://facebook/bar',
        ],
      ],
      'paging' => [
        'next' => 'http://facebook/next',
        'previous' => 'http://facebook/prev',
      ],
    ];
    $graphList = GraphObject::make($dataFromGraph);
    $graphData = $graphList->asArray();

    $userOne = $graphData[0]->asArray();
    $userTwo = $graphData[1]->asArray();

    $this->assertInstanceOf('Facebook\GraphNodes\GraphList', $graphList);
    $this->assertEquals([
        'id' => '123',
        'name' => 'Foo McBar',
        'link' => 'http://facebook/foo',
      ], $userOne);
    $this->assertEquals([
        'id' => '1337',
        'name' => 'Bar McBaz',
        'link' => 'http://facebook/bar',
      ], $userTwo);
  }

  public function testAGraphObjectWillBeCastAsAGraphObject()
  {
    $dataFromGraph = [
      'id' => '123',
      'name' => 'Foo McBar',
      'link' => 'http://facebook/foo',
    ];
    $graphObject = GraphObject::make($dataFromGraph);
    $graphData = $graphObject->asArray();

    $this->assertInstanceOf('Facebook\GraphNodes\GraphObject', $graphObject);
    $this->assertEquals([
        'id' => '123',
        'name' => 'Foo McBar',
        'link' => 'http://facebook/foo',
      ], $graphData);
  }

  public function testAGraphObjectWithARootDataKeyWillBeCastAsAGraphObject()
  {
    $dataFromGraph = [
      'data' => [
        'id' => '123',
        'name' => 'Foo McBar',
        'link' => 'http://facebook/foo',
      ],
    ];

    $graphObject = GraphObject::make($dataFromGraph);
    $graphData = $graphObject->asArray();

    $this->assertInstanceOf('Facebook\GraphNodes\GraphObject', $graphObject);
    $this->assertEquals([
        'id' => '123',
        'name' => 'Foo McBar',
        'link' => 'http://facebook/foo',
      ], $graphData);
  }

  public function testAGraphObjectContainingADateWillGetCastAsDateTimeObject()
  {
    $dataFromGraph = [
      'foo' => 'bar',
      'created_time' => '2013-12-24T00:34:20+0000',
      'faz' => 'baz',
    ];
    $graphObject = GraphObject::make($dataFromGraph);
    $graphData = $graphObject->asArray();

    $this->assertInstanceOf('DateTime', $graphData['created_time']);
  }

  public function testACollectionWillBeCastRecursively()
  {
    $someUser = [
      'id' => '123',
      'name' => 'Foo McBar',
    ];
    $likesCollection = [
      'data' => [
        [
          'id' => '1',
          'name' => 'Sammy Kaye Powers',
          'is_sexy' => true,
        ],
        [
          'id' => '2',
          'name' => 'Yassine Guedidi',
          'is_sexy' => true,
        ],
        [
          'id' => '3',
          'name' => 'Fosco Marotto',
          'is_sexy' => true,
        ],
        [
          'id' => '4',
          'name' => 'Foo McUgly',
          'is_sexy' => false,
        ],
      ],
      'paging' => [
        'next' => 'http://facebook/next_likes',
        'previous' => 'http://facebook/prev_likes',
      ],
    ];
    $commentsCollection = [
      'data' => [
        [
          'id' => '42_1',
          'from' => $someUser,
          'message' => 'Foo comment.',
          'created_time' => '2014-07-15T03:54:34+0000',
          'likes' => $likesCollection,
        ],
        [
          'id' => '42_2',
          'from' => $someUser,
          'message' => 'Bar comment.',
          'created_time' => '2014-07-15T04:11:24+0000',
          'likes' => $likesCollection,
        ],
      ],
      'paging' => [
        'next' => 'http://facebook/next_comments',
        'previous' => 'http://facebook/prev_comments',
      ],
    ];
    $dataFromGraph = [
      'data' => [
        [
          'id' => '1337_1',
          'from' => $someUser,
          'story' => 'Some great foo story.',
          'likes' => $likesCollection,
          'comments' => $commentsCollection,
        ],
        [
          'id' => '1337_2',
          'from' => $someUser,
          'to' => [
            'data' => [$someUser],
          ],
          'message' => 'Some great bar message.',
          'likes' => $likesCollection,
          'comments' => $commentsCollection,
        ],
      ],
      'paging' => [
        'next' => 'http://facebook/next',
        'previous' => 'http://facebook/prev',
      ],
    ];

    $graphObject = GraphObject::make($dataFromGraph);
    $this->assertInstanceOf('Facebook\GraphNodes\GraphList', $graphObject);

    $graphData = $graphObject->asArray();

    // Story
    $storyObject = $graphData[0]->asArray();
    $this->assertInstanceOf('Facebook\GraphNodes\GraphObject', $storyObject['from']);
    $this->assertEquals($someUser, $storyObject['from']->asArray());
    $this->assertInstanceOf('Facebook\GraphNodes\GraphList', $storyObject['likes']);
    $this->assertInstanceOf('Facebook\GraphNodes\GraphList', $storyObject['comments']);

    // Story Comments
    $storyComments = $storyObject['comments']->asArray();
    $firstStoryComment = $storyComments[0]->asArray();
    $this->assertEquals($someUser, $firstStoryComment['from']->asArray());

    // Message
    $messageObject = $graphData[1]->asArray();
    $this->assertInstanceOf('Facebook\GraphNodes\GraphList', $messageObject['to']);
    $toUsers = $messageObject['to']->asArray();
    $this->assertEquals($someUser, $toUsers[0]->asArray());
  }

  public function testAnExistingPropertyCanBeAccessed()
  {
    $graphObject = new GraphObject(['foo' => 'bar']);
    $property = $graphObject->getProperty('foo');

    $this->assertEquals('bar', $property);
  }

  public function testAMissingPropertyWillReturnNull()
  {
    $graphObject = new GraphObject(['foo' => 'bar']);
    $property = $graphObject->getProperty('baz');

    $this->assertNull($property, 'Expected the property to return null.');
  }

  public function testAMissingPropertyWillReturnTheDefault()
  {
    $graphObject = new GraphObject(['foo' => 'bar']);
    $property = $graphObject->getProperty('baz', 'faz');

    $this->assertEquals('faz', $property);
  }

  public function testTheKeysFromTheGraphDataCanBeReturned()
  {
    $graphObject = new GraphObject([
      'key1' => 'foo',
      'key2' => 'bar',
      'key3' => 'baz',
    ]);
    $propertyKeys = $graphObject->getPropertyNames();

    $this->assertEquals(['key1', 'key2', 'key3'], $propertyKeys);
  }

  public function testAGraphObjectCanBeCast()
  {
    $graphObject = new GraphObject(['foo' => 'bar']);
    $newGraphObject = $graphObject->cast();
    $this->assertInstanceOf('Facebook\GraphNodes\GraphObject', $newGraphObject);

    $fooGraphObject = new GraphObject(['foo' => 'bar']);
    $newFooGraphObject = $fooGraphObject->cast('MyFooGraphObject');
    $this->assertInstanceOf('MyFooGraphObject', $newFooGraphObject);
  }

  public function testAGraphObjectContainingObjectDataCanBeRecastGracefully()
  {
    $someUser = [
      'id' => '123',
      'name' => 'Foo McBar',
      'created_time' => '2014-07-15T03:54:34+0000',
    ];
    $graphObject = new GraphObject([
      'user_one' => $someUser,
      'user_two' => $someUser,
      'created_time' => '2014-07-15T03:54:34+0000',
    ]);
    $newFooGraphObject = $graphObject->cast('MyFooGraphObject');
    $this->assertInstanceOf('Facebook\GraphNodes\GraphObject', $newFooGraphObject);
  }

  public function testPropertiesCanBeAccessedMagically()
  {
    $graphObject = new GraphObject([
      'id' => '123',
      'name' => 'Foo McBar',
      'created_time' => '2014-07-14T20:35:42+0000',
      'can_remove' => true,
    ]);
    $id = $graphObject->getId();
    $name = $graphObject->getName();
    $createdTime = $graphObject->getCreatedTime();
    $canRemove = $graphObject->getCanRemove();
    $noExists = $graphObject->getFoo();
    $default = $graphObject->getBar('foo_default');

    $this->assertEquals('123', $id);
    $this->assertEquals('Foo McBar', $name);
    $this->assertInstanceOf('DateTime', $createdTime);
    $this->assertTrue($canRemove);
    $this->assertNull($noExists);
    $this->assertEquals('foo_default', $default);
  }

  public function testAGraphObjectCanBeRecastMagically()
  {
    $graphObject = new GraphObject(['foo' => 'bar']);
    $fooGraphObject = $graphObject->castAsMyFooGraphObject(false);
    $property = $fooGraphObject->getProperty('foo');

    $this->assertInstanceOf('MyFooGraphObject', $fooGraphObject);
    $this->assertEquals('bar', $property);
  }

  /**
   * @expectedException \Facebook\Exceptions\FacebookSDKException
   */
  public function testTryingToRecastToAGraphObjectThatDoesntExistWillThrow()
  {
    $graphObject = new GraphObject(['foo' => 'bar']);
    $graphObject->castAsFooObject();
  }

  public function testCamelCaseWillConvertToSnakeCase()
  {
    $snakeOne = GraphObject::snake('FooBarBaz');
    $snakeTwo = GraphObject::snake('fazBazFoo');
    $snakeThree = GraphObject::snake('AFooCamel');
    $snakeFour = GraphObject::snake('foobar');

    $this->assertEquals('foo_bar_baz', $snakeOne);
    $this->assertEquals('faz_baz_foo', $snakeTwo);
    $this->assertEquals('a_foo_camel', $snakeThree);
    $this->assertEquals('foobar', $snakeFour);
  }

}
