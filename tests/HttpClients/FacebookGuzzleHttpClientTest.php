<?php
/**
 * Copyright 2016 Facebook, Inc.
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
use Facebook\HttpClients\FacebookGuzzleHttpClient;
use GuzzleHttp\Message\Request;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Stream\Stream;
use GuzzleHttp\Exception\RequestException;

class FacebookGuzzleHttpClientTest extends AbstractTestHttpClient
{
    /**
     * @var \GuzzleHttp\Client
     */
    protected $guzzleMock;

    /**
     * @var FacebookGuzzleHttpClient
     */
    protected $guzzleClient;

    protected function setUp()
    {
        $this->guzzleMock = m::mock('GuzzleHttp\Client');
        $this->guzzleClient = new FacebookGuzzleHttpClient($this->guzzleMock);
    }

    public function testCanSendNormalRequest()
    {
        $request = new Request('GET', 'http://foo.com');

        $body = Stream::factory($this->fakeRawBody);
        $response = new Response(200, $this->fakeHeadersAsArray, $body);

        $this->guzzleMock
            ->shouldReceive('createRequest')
            ->once()
            ->with('GET', 'http://foo.com/', m::on(function ($arg) {

                // array_diff_assoc() will sometimes trigger error on child-arrays
                if (['X-foo' => 'bar'] !== $arg['headers']) {
                    return false;
                }
                unset($arg['headers']);

                $caInfo = array_diff_assoc($arg, [
                    'body' => 'foo_body',
                    'timeout' => 123,
                    'connect_timeout' => 10,
                ]);

                if (count($caInfo) !== 1) {
                    return false;
                }

                if (1 !== preg_match('/.+\/certs\/DigiCertHighAssuranceEVRootCA\.pem$/', $caInfo['verify'])) {
                    return false;
                }

                return true;
            }))
            ->andReturn($request);
        $this->guzzleMock
            ->shouldReceive('send')
            ->once()
            ->with($request)
            ->andReturn($response);

        $response = $this->guzzleClient->send('http://foo.com/', 'GET', 'foo_body', ['X-foo' => 'bar'], 123);

        $this->assertInstanceOf('Facebook\Http\GraphRawResponse', $response);
        $this->assertEquals($this->fakeRawBody, $response->getBody());
        $this->assertEquals($this->fakeHeadersAsArray, $response->getHeaders());
        $this->assertEquals(200, $response->getHttpResponseCode());
    }

    /**
     * @expectedException \Facebook\Exceptions\FacebookSDKException
     */
    public function testThrowsExceptionOnClientError()
    {
        $request = new Request('GET', 'http://foo.com');

        $this->guzzleMock
            ->shouldReceive('createRequest')
            ->once()
            ->with('GET', 'http://foo.com/', m::on(function ($arg) {

                // array_diff_assoc() will sometimes trigger error on child-arrays
                if ([] !== $arg['headers']) {
                    return false;
                }
                unset($arg['headers']);

                $caInfo = array_diff_assoc($arg, [
                    'body' => 'foo_body',
                    'timeout' => 60,
                    'connect_timeout' => 10,
                ]);

                if (count($caInfo) !== 1) {
                    return false;
                }

                if (1 !== preg_match('/.+\/certs\/DigiCertHighAssuranceEVRootCA\.pem$/', $caInfo['verify'])) {
                    return false;
                }

                return true;
            }))
            ->andReturn($request);
        $this->guzzleMock
            ->shouldReceive('send')
            ->once()
            ->with($request)
            ->andThrow(new RequestException('Foo', $request));

        $this->guzzleClient->send('http://foo.com/', 'GET', 'foo_body', [], 60);
    }
}
