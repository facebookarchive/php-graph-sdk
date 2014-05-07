<?php

use Facebook\FacebookRequest;

class ETagTest extends PHPUnit_Framework_TestCase
{

  public static function setUpBeforeClass()
  {
    FacebookTestHelper::setUpBeforeClass();
  }

  public function testETagHit()
  {
    $response = (
      new FacebookRequest(
        FacebookTestHelper::$testSession,
        'GET',
        '/104048449631599'
      ))->execute();

    $response = (
      new FacebookRequest(
        FacebookTestHelper::$testSession,
        'GET',
        '/104048449631599',
        null,
        null,
        $response->getETag()
      ))->execute();

    $this->assertTrue($response->isETagHit());
    $this->assertNull($response->getETag());
  }

  public function testETagMiss()
  {
    $response = (
      new FacebookRequest(
        FacebookTestHelper::$testSession,
        'GET',
        '/104048449631599',
        null,
        null,
        'someRandomValue'
      ))->execute();

    $this->assertFalse($response->isETagHit());
    $this->assertNotNull($response->getETag());
  }

}