<?php

use Facebook\FacebookPersistentDataHandler;

class FacebookPersistentDataHandlerTest extends PHPUnit_Framework_TestCase
{

  public static function setUpBeforeClass()
  {
    FacebookTestHelper::setUpBeforeClass();
  }

  public function testCanSetAndGetPersistentData()
  {
    $handler = new FacebookPersistentDataHandler(true);

    $handler->setPersistentData('foo', 'bar');
    $this->assertEquals('bar', $_SESSION['FBPDH_foo']);

    $data = $handler->getPersistentData('foo');
    $this->assertEquals('bar', $data);

    $dataWithDefault = $handler->getPersistentData('bar', 'baz');
    $this->assertEquals('baz', $dataWithDefault);
  }

}