<?php
/**
 * Copyright 2017 Facebook, Inc.
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

use Facebook\HttpClients\FacebookCurlHttpClient;
use Facebook\HttpClients\FacebookCurl;
use Facebook\Http\GraphRawResponse;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

class FacebookCurlHttpClientTest extends AbstractTestHttpClient
{
    /**
     * @var FacebookCurl|ObjectProphecy
     */
    protected $curlMock;

    /**
     * @var FacebookCurlHttpClient
     */
    protected $curlClient;

    const CURL_VERSION_STABLE = 0x072400;
    const CURL_VERSION_BUGGY = 0x071400;

    protected function setUp()
    {
        if (!extension_loaded('curl')) {
            $this->markTestSkipped('cURL must be installed to test cURL client handler.');
        }
        $this->curlMock = $this->prophesize(FacebookCurl::class);
        $this->curlClient = new FacebookCurlHttpClient($this->curlMock->reveal());
    }

    public function testCanOpenGetCurlConnection()
    {
        $this->curlMock->init()->shouldBeCalled();
        $this->curlMock->setoptArray(Argument::that(function ($arg) {
            // array_diff() will sometimes trigger error on child-arrays
            if (['X-Foo-Header: X-Bar'] !== $arg[CURLOPT_HTTPHEADER]) {
                return false;
            }
            unset($arg[CURLOPT_HTTPHEADER]);

            $caInfo = array_diff($arg, [
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_URL => 'http://foo.com',
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_TIMEOUT => 123,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HEADER => true,
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
        }))->shouldBeCalled();

        $this->curlClient->openConnection('http://foo.com', 'GET', 'foo_body', ['X-Foo-Header' => 'X-Bar'], 123);
    }

    public function testCanOpenCurlConnectionWithPostBody()
    {
        $this->curlMock->init()->shouldBeCalled();
        $this->curlMock->setoptArray(Argument::that(function ($arg) {

            // array_diff() will sometimes trigger error on child-arrays
            if ([] !== $arg[CURLOPT_HTTPHEADER]) {
                return false;
            }
            unset($arg[CURLOPT_HTTPHEADER]);

            $caInfo = array_diff($arg, [
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_URL => 'http://bar.com',
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_TIMEOUT => 60,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HEADER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_POSTFIELDS => 'baz=bar',
            ]);

            if (count($caInfo) !== 1) {
                return false;
            }

            if (1 !== preg_match('/.+\/certs\/DigiCertHighAssuranceEVRootCA\.pem$/', $caInfo[CURLOPT_CAINFO])) {
                return false;
            }

            return true;
        }))->shouldBeCalled();

        $this->curlClient->openConnection('http://bar.com', 'POST', 'baz=bar', [], 60);
    }

    public function testCanCloseConnection()
    {
        $this->curlMock->close()->shouldBeCalled();

        $this->curlClient->closeConnection();
    }

    public function testIsolatesTheHeaderAndBody()
    {
        $this->curlMock->exec()->willReturn($this->fakeRawHeader . $this->fakeRawBody);

        $this->curlClient->sendRequest();
        list($rawHeader, $rawBody) = $this->curlClient->extractResponseHeadersAndBody();

        $this->assertEquals($rawHeader, trim($this->fakeRawHeader));
        $this->assertEquals($rawBody, $this->fakeRawBody);
    }

    public function testProperlyHandlesProxyHeaders()
    {
        $rawHeader = $this->fakeRawProxyHeader . $this->fakeRawHeader;
        $this->curlMock->exec()->willReturn($rawHeader . $this->fakeRawBody);

        $this->curlClient->sendRequest();
        list($rawHeaders, $rawBody) = $this->curlClient->extractResponseHeadersAndBody();

        $this->assertEquals($rawHeaders, trim($rawHeader));
        $this->assertEquals($rawBody, $this->fakeRawBody);
    }

    public function testProperlyHandlesProxyHeadersWithCurlBug()
    {
        $rawHeader = $this->fakeRawProxyHeader2 . $this->fakeRawHeader;
        $this->curlMock->exec()->willReturn($rawHeader . $this->fakeRawBody);

        $this->curlClient->sendRequest();
        list($rawHeaders, $rawBody) = $this->curlClient->extractResponseHeadersAndBody();

        $this->assertEquals($rawHeaders, trim($rawHeader));
        $this->assertEquals($rawBody, $this->fakeRawBody);
    }

    public function testProperlyHandlesRedirectHeaders()
    {
        $rawHeader = $this->fakeRawRedirectHeader . $this->fakeRawHeader;
        $this->curlMock->exec()->willReturn($rawHeader . $this->fakeRawBody);

        $this->curlClient->sendRequest();
        list($rawHeaders, $rawBody) = $this->curlClient->extractResponseHeadersAndBody();

        $this->assertEquals($rawHeaders, trim($rawHeader));
        $this->assertEquals($rawBody, $this->fakeRawBody);
    }

    public function testCanSendNormalRequest()
    {
        $this->curlMock->init()->shouldBeCalled();
        $this->curlMock->setoptArray(Argument::type('array'))->shouldBeCalled();
        $this->curlMock->exec()->willReturn($this->fakeRawHeader . $this->fakeRawBody);
        $this->curlMock->errno()->shouldBeCalled();
        $this->curlMock->close()->shouldBeCalled();

        $response = $this->curlClient->send('http://foo.com/', 'GET', '', [], 60);

        $this->assertInstanceOf(GraphRawResponse::class, $response);
        $this->assertEquals($this->fakeRawBody, $response->getBody());
        $this->assertEquals($this->fakeHeadersAsArray, $response->getHeaders());
        $this->assertEquals(200, $response->getHttpResponseCode());
    }

    /**
     * @expectedException \Facebook\Exceptions\FacebookSDKException
     * @expectedExceptionCode 123
     * @expectedExceptionMessage Foo error
     */
    public function testThrowsExceptionOnClientError()
    {
        $this->curlMock->init()->shouldBeCalled();
        $this->curlMock->setoptArray(Argument::type('array'))->shouldBeCalled();
        $this->curlMock->exec()->willReturn(false);
        $this->curlMock->errno()->willReturn(123);
        $this->curlMock->error()->willReturn('Foo error');
        $this->curlMock->close()->shouldBeCalled();

        $this->curlClient->send('http://foo.com/', 'GET', '', [], 60);
    }
}
