<?php

use Facebook\FacebookRequest;
use Facebook\GraphObject;
use Facebook\FacebookResponse;
use Facebook\GraphUser;

class GraphObjectTest extends PHPUnit_Framework_TestCase
{

  public static function setUpBeforeClass()
  {
    FacebookTestHelper::setUpBeforeClass();
  }

  public function testFriends()
  {
    $response = (
    new FacebookRequest(
      FacebookTestHelper::$testSession,
      'GET',
      '/me/friends'
    ))->execute()->getGraphObjectList();
    $this->assertTrue(is_array($response));
  }

  public function testArrayProperties()
  {
    $backingData = array(
      'id' => 42,
      'friends' => array(
        'data' => array(
          array(
            'id' => 1,
            'name' => 'David'
          ),
          array(
            'id' => 2,
            'name' => 'Fosco'
          )
        ),
        'paging' => array(
          'next' => 'nexturl'
        )
      )
    );
    $obj = new GraphObject($backingData);
    $friends = $obj->getPropertyAsArray('friends');
    $this->assertEquals(2, count($friends));
    $this->assertTrue($friends[0] instanceof GraphObject);
    $this->assertTrue($friends[1] instanceof GraphObject);
    $this->assertEquals('David', $friends[0]->getProperty('name'));
    $this->assertEquals('Fosco', $friends[1]->getProperty('name'));

    $backingData = array(
      'id' => 42,
      'friends' => array(
        array(
          'id' => 1,
          'name' => 'Ilya'
        ),
        array(
          'id' => 2,
          'name' => 'Kevin'
        )
      )
    );
    $obj = new GraphObject($backingData);
    $friends = $obj->getPropertyAsArray('friends');
    $this->assertEquals(2, count($friends));
    $this->assertTrue($friends[0] instanceof GraphObject);
    $this->assertTrue($friends[1] instanceof GraphObject);
    $this->assertEquals('Ilya', $friends[0]->getProperty('name'));
    $this->assertEquals('Kevin', $friends[1]->getProperty('name'));

  }

  public function testAsList()
  {
    $backingData = array(
      'data' => array(
        array(
          'id' => 1,
          'name' => 'David'
        ),
        array(
          'id' => 2,
          'name' => 'Fosco'
        )
      )
    );
    $enc = json_encode($backingData);
    $response = new FacebookResponse(null, json_decode($enc), $enc);
    $list = $response->getGraphObjectList(GraphUser::className());
    $this->assertEquals(2, count($list));
    $this->assertTrue($list[0] instanceof GraphObject);
    $this->assertTrue($list[1] instanceof GraphObject);
    $this->assertEquals('David', $list[0]->getName());
    $this->assertEquals('Fosco', $list[1]->getName());
  }

}