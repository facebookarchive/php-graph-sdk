<?php

require_once __DIR__ . '/AbstractTestHttpClient.php';

use Mockery as m;
use Facebook\HttpClients\FacebookCurlHttpClient;

class FacebookCurlHttpClientTest extends AbstractTestHttpClient
{

  protected $curlMock;
  protected $curlClient;

  const CURL_VERSION_STABLE = 0x072400;
  const CURL_VERSION_BUGGY = 0x071400;

  public function setUp()
  {
    $this->curlMock = m::mock('Facebook\HttpClients\FacebookCurl');
    $this->curlClient = new FacebookCurlHttpClient($this->curlMock);
  }

  public function tearDown()
  {
    m::close();
    (new FacebookCurlHttpClient()); // Resets the static dependency injection
  }

  public function testCanOpenGetCurlConnection()
  {
    $this->curlMock
      ->shouldReceive('init')
      ->once()
      ->andReturn(null);
    $this->curlMock
      ->shouldReceive('setopt_array')
      ->with(m::on(function($arg) {
            $caInfo = array_diff($arg, [
                CURLOPT_CUSTOMREQUEST  => 'GET',
                CURLOPT_URL            => 'http://foo.com',
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_TIMEOUT        => 60,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HEADER         => true,
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_SSL_VERIFYPEER => true,
              ]);

            if (count($caInfo) !== 1) {
              return false;
            }

            if (1 !== preg_match('/.+\/certs\/DigiCertHighAssuranceEVRootCA\.pem$/', $caInfo[CURLOPT_CAINFO])) {
              return false;
            }

            return true;
          }))
      ->once()
      ->andReturn(null);

    $this->curlClient->openConnection('http://foo.com', 'GET', array());
  }

  public function testCanOpenGetCurlConnectionWithHeaders()
  {
    $this->curlMock
      ->shouldReceive('init')
      ->once()
      ->andReturn(null);
    $this->curlMock
      ->shouldReceive('setopt_array')
      ->with(m::on(function($arg) {

            // array_diff() will sometimes trigger error on multidimensional arrays
            if (['X-foo: bar'] !== $arg[CURLOPT_HTTPHEADER]) {
              return false;
            }
            unset($arg[CURLOPT_HTTPHEADER]);

            $caInfo = array_diff($arg, [
                CURLOPT_CUSTOMREQUEST  => 'GET',
                CURLOPT_URL            => 'http://foo.com',
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_TIMEOUT        => 60,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HEADER         => true,
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_SSL_VERIFYPEER => true,
              ]);

            if (count($caInfo) !== 1) {
              return false;
            }

            if (1 !== preg_match('/.+\/certs\/DigiCertHighAssuranceEVRootCA\.pem$/', $caInfo[CURLOPT_CAINFO])) {
              return false;
            }

            return true;
          }))
      ->once()
      ->andReturn(null);

    $this->curlClient->addRequestHeader('X-foo', 'bar');
    $this->curlClient->openConnection('http://foo.com', 'GET', array());
  }

  public function testCanOpenPostCurlConnection()
  {
    $this->curlMock
      ->shouldReceive('init')
      ->once()
      ->andReturn(null);
    $this->curlMock
      ->shouldReceive('setopt_array')
      ->with(m::on(function($arg) {
            $caInfo = array_diff($arg, [
                CURLOPT_CUSTOMREQUEST  => 'POST',
                CURLOPT_URL            => 'http://bar.com',
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_TIMEOUT        => 60,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HEADER         => true,
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_POSTFIELDS     => 'baz=bar&foo%5B0%5D=1&foo%5B1%5D=2&foo%5B2%5D=3',
              ]);

            if (count($caInfo) !== 1) {
              return false;
            }

            if (1 !== preg_match('/.+\/certs\/DigiCertHighAssuranceEVRootCA\.pem$/', $caInfo[CURLOPT_CAINFO])) {
              return false;
            }

            return true;
          }))
      ->once()
      ->andReturn(null);

    // Prove can support multidimensional params
    $params = array(
      'baz' => 'bar',
      'foo' => array(1, 2, 3),
    );
    $this->curlClient->openConnection('http://bar.com', 'POST', $params);
  }

  public function testCanOpenDeleteCurlConnection()
  {
    $this->curlMock
      ->shouldReceive('init')
      ->once()
      ->andReturn(null);
    $this->curlMock
      ->shouldReceive('setopt_array')
      ->with(m::on(function($arg) {
            $caInfo = array_diff($arg, [
                CURLOPT_CUSTOMREQUEST  => 'DELETE',
                CURLOPT_URL            => 'http://faz.com',
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_TIMEOUT        => 60,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HEADER         => true,
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_POSTFIELDS     => 'baz=bar',
              ]);

            if (count($caInfo) !== 1) {
              return false;
            }

            if (1 !== preg_match('/.+\/certs\/DigiCertHighAssuranceEVRootCA\.pem$/', $caInfo[CURLOPT_CAINFO])) {
              return false;
            }

            return true;
          }))
      ->once()
      ->andReturn(null);

    $this->curlClient->openConnection('http://faz.com', 'DELETE', array('baz' => 'bar'));
  }

  public function testCanCloseConnection()
  {
    $this->curlMock
      ->shouldReceive('close')
      ->once()
      ->andReturn(null);

    $this->curlClient->closeConnection();
  }

  public function testTrySendRequest()
  {
    $this->curlMock
      ->shouldReceive('exec')
      ->once()
      ->andReturn('foo response');
    $this->curlMock
      ->shouldReceive('errno')
      ->once()
      ->andReturn(null);
    $this->curlMock
      ->shouldReceive('error')
      ->once()
      ->andReturn(null);
    $this->curlMock
      ->shouldReceive('getinfo')
      ->with(CURLINFO_HTTP_CODE)
      ->once()
      ->andReturn(200);

    $this->curlClient->tryToSendRequest();
  }

  public function testProperlyCompilesRequestHeaders()
  {
    $headers = $this->curlClient->compileRequestHeaders();
    $expectedHeaders = array();
    $this->assertEquals($expectedHeaders, $headers);

    $this->curlClient->addRequestHeader('X-foo', 'bar');
    $headers = $this->curlClient->compileRequestHeaders();
    $expectedHeaders = array(
      'X-foo: bar',
    );
    $this->assertEquals($expectedHeaders, $headers);

    $this->curlClient->addRequestHeader('X-bar', 'baz');
    $headers = $this->curlClient->compileRequestHeaders();
    $expectedHeaders = array(
      'X-foo: bar',
      'X-bar: baz',
    );
    $this->assertEquals($expectedHeaders, $headers);
  }

  public function testIsolatesTheHeaderAndBody()
  {
    $this->curlMock
      ->shouldReceive('getinfo')
      ->with(CURLINFO_HEADER_SIZE)
      ->once()
      ->andReturn(strlen($this->fakeRawHeader));
    $this->curlMock
      ->shouldReceive('version')
      ->once()
      ->andReturn(array('version_number' => self::CURL_VERSION_STABLE));
    $this->curlMock
      ->shouldReceive('exec')
      ->once()
      ->andReturn($this->fakeRawHeader . $this->fakeRawBody);

    $this->curlClient->sendRequest();
    list($rawHeader, $rawBody) = $this->curlClient->extractResponseHeadersAndBody();

    $this->assertEquals($rawHeader, trim($this->fakeRawHeader));
    $this->assertEquals($rawBody, $this->fakeRawBody);
  }

  public function testConvertsRawHeadersToArray()
  {
    $headers = FacebookCurlHttpClient::headersToArray($this->fakeRawHeader);

    $this->assertEquals($headers, $this->fakeHeadersAsArray);
  }

  public function testProperlyHandlesProxyHeaders()
  {
    $rawHeader = $this->fakeRawProxyHeader . $this->fakeRawHeader;
    $this->curlMock
      ->shouldReceive('getinfo')
      ->with(CURLINFO_HEADER_SIZE)
      ->once()
      ->andReturn(mb_strlen($rawHeader));
    $this->curlMock
      ->shouldReceive('version')
      ->once()
      ->andReturn(array('version_number' => self::CURL_VERSION_STABLE));
    $this->curlMock
      ->shouldReceive('exec')
      ->once()
      ->andReturn($rawHeader . $this->fakeRawBody);

    $this->curlClient->sendRequest();
    list($rawHeaders, $rawBody) = $this->curlClient->extractResponseHeadersAndBody();

    $this->assertEquals($rawHeaders, trim($rawHeader));
    $this->assertEquals($rawBody, $this->fakeRawBody);

    $headers = FacebookCurlHttpClient::headersToArray($rawHeaders);

    $this->assertEquals($headers, $this->fakeHeadersAsArray);
  }

  public function testProperlyHandlesProxyHeadersWithCurlBug()
  {
    $rawHeader = $this->fakeRawProxyHeader . $this->fakeRawHeader;
    $this->curlMock
      ->shouldReceive('getinfo')
      ->with(CURLINFO_HEADER_SIZE)
      ->once()
      ->andReturn(mb_strlen($this->fakeRawHeader)); // Mimic bug that doesn't count proxy header
    $this->curlMock
      ->shouldReceive('version')
      ->once()
      ->andReturn(array('version_number' => self::CURL_VERSION_BUGGY));
    $this->curlMock
      ->shouldReceive('exec')
      ->once()
      ->andReturn($rawHeader . $this->fakeRawBody);

    $this->curlClient->sendRequest();
    list($rawHeaders, $rawBody) = $this->curlClient->extractResponseHeadersAndBody();

    $this->assertEquals($rawHeaders, trim($rawHeader));
    $this->assertEquals($rawBody, $this->fakeRawBody);

    $headers = FacebookCurlHttpClient::headersToArray($rawHeaders);

    $this->assertEquals($headers, $this->fakeHeadersAsArray);
  }

  public function testProperlyHandlesProxyHeadersWithCurlBug2()
  {
        $rawHeader = $this->fakeRawProxyHeader2 . $this->fakeRawHeader;
        $this->curlMock
            ->shouldReceive('getinfo')
            ->with(CURLINFO_HEADER_SIZE)
            ->once()
            ->andReturn(mb_strlen($this->fakeRawHeader)); // Mimic bug that doesn't count proxy header
        $this->curlMock
            ->shouldReceive('version')
            ->once()
            ->andReturn(array('version_number' => self::CURL_VERSION_BUGGY));
        $this->curlMock
            ->shouldReceive('exec')
            ->once()
            ->andReturn($rawHeader . $this->fakeRawBody);

        $this->curlClient->sendRequest();
        list($rawHeaders, $rawBody) = $this->curlClient->extractResponseHeadersAndBody();

        $this->assertEquals($rawHeaders, trim($rawHeader));
        $this->assertEquals($rawBody, $this->fakeRawBody);

        $headers = FacebookCurlHttpClient::headersToArray($rawHeaders);

        $this->assertEquals($headers, $this->fakeHeadersAsArray);
  }

  public function testProperlyHandlesRedirectHeaders()
  {
    $rawHeader = $this->fakeRawRedirectHeader . $this->fakeRawHeader;
    $this->curlMock
      ->shouldReceive('getinfo')
      ->with(CURLINFO_HEADER_SIZE)
      ->once()
      ->andReturn(mb_strlen($rawHeader));
    $this->curlMock
      ->shouldReceive('version')
      ->once()
      ->andReturn(array('version_number' => self::CURL_VERSION_STABLE));
    $this->curlMock
      ->shouldReceive('exec')
      ->once()
      ->andReturn($rawHeader . $this->fakeRawBody);

    $this->curlClient->sendRequest();
    list($rawHeaders, $rawBody) = $this->curlClient->extractResponseHeadersAndBody();

    $this->assertEquals($rawHeaders, trim($rawHeader));
    $this->assertEquals($rawBody, $this->fakeRawBody);

    $headers = FacebookCurlHttpClient::headersToArray($rawHeaders);

    $this->assertEquals($headers, $this->fakeHeadersAsArray);
  }

  public function testCanSendNormalRequest()
  {
    $this->curlMock
      ->shouldReceive('init')
      ->once()
      ->andReturn(null);
    $this->curlMock
      ->shouldReceive('setopt_array')
      ->once()
      ->andReturn(null);
    $this->curlMock
      ->shouldReceive('exec')
      ->once()
      ->andReturn($this->fakeRawHeader . $this->fakeRawBody);
    $this->curlMock
      ->shouldReceive('errno')
      ->once()
      ->andReturn(null);
    $this->curlMock
      ->shouldReceive('error')
      ->once()
      ->andReturn(null);
    $this->curlMock
      ->shouldReceive('getinfo')
      ->with(CURLINFO_HTTP_CODE)
      ->once()
      ->andReturn(200);
    $this->curlMock
      ->shouldReceive('getinfo')
      ->with(CURLINFO_HEADER_SIZE)
      ->once()
      ->andReturn(mb_strlen($this->fakeRawHeader));
    $this->curlMock
      ->shouldReceive('version')
      ->once()
      ->andReturn(array('version_number' => self::CURL_VERSION_STABLE));
    $this->curlMock
      ->shouldReceive('close')
      ->once()
      ->andReturn(null);

    $responseBody = $this->curlClient->send('http://foo.com/');

    $this->assertEquals($responseBody, $this->fakeRawBody);
    $this->assertEquals($this->curlClient->getResponseHeaders(), $this->fakeHeadersAsArray);
    $this->assertEquals(200, $this->curlClient->getResponseHttpStatusCode());
  }

  /**
   * @expectedException \Facebook\FacebookSDKException
   */
  public function testThrowsExceptionOnClientError()
  {
    $this->curlMock
      ->shouldReceive('init')
      ->once()
      ->andReturn(null);
    $this->curlMock
      ->shouldReceive('setopt_array')
      ->once()
      ->andReturn(null);
    $this->curlMock
      ->shouldReceive('exec')
      ->once()
      ->andReturn(false);
    $this->curlMock
      ->shouldReceive('errno')
      ->once()
      ->andReturn(123);
    $this->curlMock
      ->shouldReceive('error')
      ->once()
      ->andReturn('Foo error');
    $this->curlMock
      ->shouldReceive('getinfo')
      ->with(CURLINFO_HTTP_CODE)
      ->once()
      ->andReturn(null);

    $this->curlClient->send('http://foo.com/');
  }

}
