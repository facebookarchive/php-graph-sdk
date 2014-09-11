<?php
/**
 * Copyright 2014 Facebook, Inc.
 *
 * You are hereby granted a non-exclusive, worldwide, royalty-free license to
 * use, copy, modify, and distribute this software in source code or binary
 * form for use in connection with the web services and APIs provided by
 * Facebook.
 *
 * As with any software that integrates with the Facebook platform, your use
 * of this software is subject to the Facebook Developer Principles and
 * Policies [http://developers.facebook.com/policy/]. This copyright notice
 * shall be included in all copies or substantial portions of the software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
 * DEALINGS IN THE SOFTWARE.
 *
 */
namespace Facebook\Tests\HttpClients;

use Mockery as m;
use Facebook\HttpClients\FacebookCurlHttpClient;

class FacebookCurlHttpClientTest extends AbstractTestHttpClient
{

  /**
   * @var \Facebook\HttpClients\FacebookCurl
   */
  protected $curlMock;

  /**
   * @var FacebookCurlHttpClient
   */
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
      ->with([
          CURLOPT_URL            => 'http://foo.com',
          CURLOPT_CONNECTTIMEOUT => 10,
          CURLOPT_TIMEOUT        => 60,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_HEADER         => true,
        ])
      ->once()
      ->andReturn(null);

    $this->curlClient->openConnection('http://foo.com', 'GET', [], []);
  }

  public function testCanOpenGetCurlConnectionWithHeaders()
  {
    $this->curlMock
      ->shouldReceive('init')
      ->once()
      ->andReturn(null);
    $this->curlMock
      ->shouldReceive('setopt_array')
      ->with([
          CURLOPT_URL            => 'http://foo.com',
          CURLOPT_CONNECTTIMEOUT => 10,
          CURLOPT_TIMEOUT        => 60,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_HEADER         => true,
          CURLOPT_HTTPHEADER     => ['X-foo: bar'],
        ])
      ->once()
      ->andReturn(null);

    $this->curlClient->openConnection('http://foo.com', 'GET', [], ['X-foo' => 'bar']);
  }

  public function testCanOpenPostCurlConnection()
  {
    $this->curlMock
      ->shouldReceive('init')
      ->once()
      ->andReturn(null);
    $this->curlMock
      ->shouldReceive('setopt_array')
      ->with([
          CURLOPT_URL            => 'http://bar.com',
          CURLOPT_CONNECTTIMEOUT => 10,
          CURLOPT_TIMEOUT        => 60,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_HEADER         => true,
          CURLOPT_POSTFIELDS     => [
            'baz' => 'bar',
          ],
        ])
      ->once()
      ->andReturn(null);

    $this->curlClient->openConnection('http://bar.com', 'POST', ['baz' => 'bar'], []);
  }

  public function testCanOpenPutCurlConnection()
  {
    $this->curlMock
      ->shouldReceive('init')
      ->once()
      ->andReturn(null);
    $this->curlMock
      ->shouldReceive('setopt_array')
      ->with([
          CURLOPT_URL            => 'http://baz.com',
          CURLOPT_CONNECTTIMEOUT => 10,
          CURLOPT_TIMEOUT        => 60,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_HEADER         => true,
          CURLOPT_CUSTOMREQUEST  => 'PUT',
          CURLOPT_POSTFIELDS     => [
            'baz' => 'bar',
          ],
        ])
      ->once()
      ->andReturn(null);

    $this->curlClient->openConnection('http://baz.com', 'PUT', ['baz' => 'bar'], []);
  }

  public function testCanOpenDeleteCurlConnection()
  {
    $this->curlMock
      ->shouldReceive('init')
      ->once()
      ->andReturn(null);
    $this->curlMock
      ->shouldReceive('setopt_array')
      ->with([
          CURLOPT_URL            => 'http://faz.com',
          CURLOPT_CONNECTTIMEOUT => 10,
          CURLOPT_TIMEOUT        => 60,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_HEADER         => true,
          CURLOPT_CUSTOMREQUEST  => 'DELETE',
          CURLOPT_POSTFIELDS     => [
            'baz' => 'bar',
          ],
        ])
      ->once()
      ->andReturn(null);

    $this->curlClient->openConnection('http://faz.com', 'DELETE', ['baz' => 'bar'], []);
  }

  public function testCanAddBundledCert()
  {
    $this->curlMock
      ->shouldReceive('setopt')
      ->with(CURLOPT_CAINFO, '/.fb_ca_chain_bundle\.crt$/')
      ->once()
      ->andReturn(null);

    $this->curlClient->addBundledCert();
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
    $headers = $this->curlClient->compileRequestHeaders([]);
    $expectedHeaders = [];
    $this->assertEquals($expectedHeaders, $headers);

    $headers = $this->curlClient->compileRequestHeaders(['X-foo' => 'bar']);
    $expectedHeaders = ['X-foo: bar'];
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
      ->andReturn(['version_number' => self::CURL_VERSION_STABLE]);
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
      ->andReturn(['version_number' => self::CURL_VERSION_STABLE]);
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
      ->andReturn(['version_number' => self::CURL_VERSION_BUGGY]);
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
            ->andReturn(['version_number' => self::CURL_VERSION_BUGGY]);
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
      ->andReturn(['version_number' => self::CURL_VERSION_STABLE]);
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
      ->andReturn(['version_number' => self::CURL_VERSION_STABLE]);
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
   * @expectedException \Facebook\Exceptions\FacebookSDKException
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
