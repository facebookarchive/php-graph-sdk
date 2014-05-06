<?php

use Facebook\FacebookRequest;

class FacebookRequestTest extends PHPUnit_Framework_TestCase
{

  public static function setUpBeforeClass()
  {
    FacebookTestHelper::setUpBeforeClass();
  }

  public function testMe()
  {
    $response = (
      new FacebookRequest(
        FacebookTestHelper::$testSession,
        'GET',
        '/me'
      ))->execute()->getGraphObject();
    $this->assertNotNull($response->getProperty('id'));
    $this->assertNotNull($response->getProperty('name'));
  }

  public function testGracefullyHandlesUrlAppending()
  {
    $params = array();
    $url = 'https://www.foo.com/';
    $processed_url = FacebookRequest::appendParamsToUrl($url, $params);
    $this->assertEquals('https://www.foo.com/', $processed_url);

    $params = array(
      'access_token' => 'foo',
    );
    $url = 'https://www.foo.com/';
    $processed_url = FacebookRequest::appendParamsToUrl($url, $params);
    $this->assertEquals('https://www.foo.com/?access_token=foo', $processed_url);

    $params = array(
      'access_token' => 'foo',
      'bar' => 'baz',
    );
    $url = 'https://www.foo.com/?foo=bar';
    $processed_url = FacebookRequest::appendParamsToUrl($url, $params);
    $this->assertEquals('https://www.foo.com/?access_token=foo&bar=baz&foo=bar', $processed_url);

    $params = array(
      'access_token' => 'foo',
    );
    $url = 'https://www.foo.com/?foo=bar&access_token=bar';
    $processed_url = FacebookRequest::appendParamsToUrl($url, $params);
    $this->assertEquals('https://www.foo.com/?access_token=bar&foo=bar', $processed_url);
  }

}