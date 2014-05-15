<?php

use Facebook\FacebookRequest;
use Facebook\FacebookMultipleRequests;

class FacebookMultipleRequestsTest extends PHPUnit_Framework_TestCase
{

  public static function setUpBeforeClass()
  {
    FacebookTestHelper::setUpBeforeClass();
  }

  public function testMultipleMe()
  {
    $requests = array(
      new FacebookRequest(
        FacebookTestHelper::$testSession,
        'GET',
        '/me?fields=first_name'
      ),
      new FacebookRequest(
        FacebookTestHelper::$testSession,
        'GET',
        '/me?fields=last_name'
      )
    );

    $responses = (
      new FacebookMultipleRequests(
        FacebookTestHelper::$testSession,
        $requests
      ))->execute();

    $this->assertTrue(is_array($responses));
    $this->assertCount(2, $responses);

    $this->assertInstanceOf('Facebook\FacebookResponse', $responses[0]);
    $this->assertInternalType('int', $responses[0]->getResponseCode());
    $this->assertNotNull($responses[0]->getGraphObject()->getProperty('first_name'));
    $this->assertNull($responses[0]->getGraphObject()->getProperty('last_name'));

    $this->assertInstanceOf('Facebook\FacebookResponse', $responses[1]);
    $this->assertNotNull($responses[1]->getGraphObject()->getProperty('last_name'));
    $this->assertNull($responses[1]->getGraphObject()->getProperty('first_name'));
    $this->assertInternalType('int', $responses[1]->getResponseCode());
  }

  public function testMultipleMeWithHeaders()
  {
    $requests = array(
      new FacebookRequest(
        FacebookTestHelper::$testSession,
        'GET',
        '/me?fields=first_name'
      ),
      new FacebookRequest(
        FacebookTestHelper::$testSession,
        'GET',
        '/me?fields=last_name'
      )
    );

    $responses = (
      new FacebookMultipleRequests(
        FacebookTestHelper::$testSession,
        $requests,
        true
      ))->execute();

    $this->assertTrue(is_array($responses));
    $this->assertCount(2, $responses);

    $this->assertArrayHasKey('ETag', $responses[0]->getResponseHeaders());
    $this->assertArrayHasKey('ETag', $responses[1]->getResponseHeaders());
  }
}