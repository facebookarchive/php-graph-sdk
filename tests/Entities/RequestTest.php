<?php

use Facebook\Facebook;
use Facebook\Entities\Request;

class RequestTest extends PHPUnit_Framework_TestCase
{

  public function testAnEmptyRequestEntityCanInstantiate()
  {
    $request = new Request();

    $this->assertInstanceOf('Facebook\Entities\Request', $request);
  }

  public function testAMissingAccessTokenWillFallBackToDefault()
  {
    Facebook::setDefaultAccessToken('foo_access_token');

    $request = new Request();

    $accessToken = $request->getAccessToken();

    $this->assertEquals('foo_access_token', $accessToken);
  }

  public function testARequestEntityInstantiatesAsExpected()
  {
    $request = new Request(
      $accessToken = 'foo_access_token',
      $method = 'GET',
      $endpoint = '/foo',
      $params = ['foo' => 'bar'],
      $eTag = 'foo_e_tag',
      $graphVersion = 'v1337',
      $appSecret = 'foo_app_secret'
    );

    $url = $request->getUrl();
    $this->assertEquals('/v1337/foo?foo=bar&access_token=foo_access_token&appsecret_proof=12f5dcbb7557d24b1d37fd180c45991c5999f325ece0af331c00a85d762f2b95', $url);

    $expectedHeaders = [
      'User-Agent' => 'fb-php-'.Facebook::VERSION,
      'Accept-Encoding' => '*',
      'If-None-Match' => 'foo_e_tag',
    ];
    $headers = $request->getHeaders();
    $this->assertEquals($expectedHeaders, $headers);

    $expectedParams = [
      'foo' => 'bar',
      'access_token' => 'foo_access_token',
      'appsecret_proof' => '12f5dcbb7557d24b1d37fd180c45991c5999f325ece0af331c00a85d762f2b95',
    ];
    $params = $request->getParams();
    $this->assertEquals($expectedParams, $params);
  }

  public function testARequestEntityInstantiatesAsExpectedWithDefaults()
  {
    Facebook::setDefaultApplication('123', 'foo_app_secret');

    $request = new Request(
      $accessToken = 'foo_access_token',
      $method = 'GET',
      $endpoint = '/foo'
    );

    $graphVersion = Facebook::getDefaultGraphApiVersion();

    $url = $request->getUrl();
    $this->assertEquals('/'.$graphVersion.'/foo?access_token=foo_access_token&appsecret_proof=12f5dcbb7557d24b1d37fd180c45991c5999f325ece0af331c00a85d762f2b95', $url);

    $expectedHeaders = [
      'User-Agent' => 'fb-php-'.Facebook::VERSION,
      'Accept-Encoding' => '*',
    ];
    $headers = $request->getHeaders();
    $this->assertEquals($expectedHeaders, $headers);

    $expectedParams = [
      'access_token' => 'foo_access_token',
      'appsecret_proof' => '12f5dcbb7557d24b1d37fd180c45991c5999f325ece0af331c00a85d762f2b95',
    ];
    $params = $request->getParams();
    $this->assertEquals($expectedParams, $params);
  }

  public function testParamsAreNotOverwritten()
  {
    Facebook::setDefaultApplication('123', 'foo_app_secret');

    $request = new Request(
      $accessToken = 'foo_access_token',
      $method = 'GET',
      $endpoint = '/foo',
      $params = [
        'access_token' => 'bar_access_token',
        'appsecret_proof' => 'bar_app_secret',
      ]
    );

    $graphVersion = Facebook::getDefaultGraphApiVersion();

    $url = $request->getUrl();
    $this->assertEquals('/'.$graphVersion.'/foo?access_token=bar_access_token&appsecret_proof=bar_app_secret', $url);

    $expectedParams = [
      'access_token' => 'bar_access_token',
      'appsecret_proof' => 'bar_app_secret',
    ];
    $params = $request->getParams();
    $this->assertEquals($expectedParams, $params);
  }

  /**
   * @expectedException \Facebook\Exceptions\FacebookSDKException
   */
  public function testAMissingAccessTokenWillThrow()
  {
    Facebook::setDefaultAccessToken(null);

    $request = new Request();

    $request->validateAccessToken();
  }

  /**
   * @expectedException \Facebook\Exceptions\FacebookSDKException
   */
  public function testAMissingMethodWillThrow()
  {
    $request = new Request();

    $request->validateMethod();
  }

  public function testGracefullyHandlesUrlAppending()
  {
    $params = [];
    $url = 'https://www.foo.com/';
    $processed_url = Request::appendParamsToUrl($url, $params);
    $this->assertEquals('https://www.foo.com/', $processed_url);

    $params = [
      'access_token' => 'foo',
    ];
    $url = 'https://www.foo.com/';
    $processed_url = Request::appendParamsToUrl($url, $params);
    $this->assertEquals('https://www.foo.com/?access_token=foo', $processed_url);

    $params = [
      'access_token' => 'foo',
      'bar' => 'baz',
    ];
    $url = 'https://www.foo.com/?foo=bar';
    $processed_url = Request::appendParamsToUrl($url, $params);
    $this->assertEquals('https://www.foo.com/?access_token=foo&bar=baz&foo=bar', $processed_url);

    $params = [
      'access_token' => 'foo',
    ];
    $url = 'https://www.foo.com/?foo=bar&access_token=bar';
    $processed_url = Request::appendParamsToUrl($url, $params);
    $this->assertEquals('https://www.foo.com/?access_token=bar&foo=bar', $processed_url);
  }

  public function testSlashesAreProperlyPrepended()
  {
    $slashTestOne = Request::forceSlashPrefix('foo');
    $slashTestTwo = Request::forceSlashPrefix('/foo');
    $slashTestThree = Request::forceSlashPrefix('foo/bar');
    $slashTestFour = Request::forceSlashPrefix('/foo/bar');
    $slashTestFive = Request::forceSlashPrefix(null);
    $slashTestSix = Request::forceSlashPrefix('');

    $this->assertEquals('/foo', $slashTestOne);
    $this->assertEquals('/foo', $slashTestTwo);
    $this->assertEquals('/foo/bar', $slashTestThree);
    $this->assertEquals('/foo/bar', $slashTestFour);
    $this->assertEquals(null, $slashTestFive);
    $this->assertEquals('', $slashTestSix);
  }

}
