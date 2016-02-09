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
use Facebook\HttpClients\FacebookStreamHttpClient;

class FacebookStreamHttpClientTest extends AbstractTestHttpClient
{
    /**
     * @var \Facebook\HttpClients\FacebookStream
     */
    protected $streamMock;

    /**
     * @var FacebookStreamHttpClient
     */
    protected $streamClient;

    public function setUp()
    {
        $this->streamMock = m::mock('Facebook\HttpClients\FacebookStream');
        $this->streamClient = new FacebookStreamHttpClient($this->streamMock);
    }

    public function testCanCompileHeader()
    {
        $headers = [
            'X-foo' => 'bar',
            'X-bar' => 'faz',
        ];
        $header = $this->streamClient->compileHeader($headers);
        $this->assertEquals("X-foo: bar\r\nX-bar: faz", $header);
    }

    public function testCanSendNormalRequest()
    {
        $this->streamMock
            ->shouldReceive('streamContextCreate')
            ->once()
            ->with(m::on(function ($arg) {
                if (!isset($arg['http']) || !isset($arg['ssl'])) {
                    return false;
                }

                if ($arg['http'] !== [
                        'method' => 'GET',
                        'header' => 'X-foo: bar',
                        'content' => 'foo_body',
                        'timeout' => 123,
                        'ignore_errors' => true,
                    ]
                ) {
                    return false;
                }

                $caInfo = array_diff_assoc($arg['ssl'], [
                    'verify_peer' => true,
                    'verify_peer_name' => true,
                    'allow_self_signed' => true,
                ]);

                if (count($caInfo) !== 1) {
                    return false;
                }

                if (1 !== preg_match('/.+\/certs\/DigiCertHighAssuranceEVRootCA\.pem$/', $caInfo['cafile'])) {
                    return false;
                }

                return true;
            }))
            ->andReturn(null);
        $this->streamMock
            ->shouldReceive('getResponseHeaders')
            ->once()
            ->andReturn(explode("\n", trim($this->fakeRawHeader)));
        $this->streamMock
            ->shouldReceive('fileGetContents')
            ->once()
            ->with('http://foo.com/')
            ->andReturn($this->fakeRawBody);

        $response = $this->streamClient->send('http://foo.com/', 'GET', 'foo_body', ['X-foo' => 'bar'], 123);

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
        $this->streamMock
            ->shouldReceive('streamContextCreate')
            ->once()
            ->andReturn(null);
        $this->streamMock
            ->shouldReceive('getResponseHeaders')
            ->once()
            ->andReturn(null);
        $this->streamMock
            ->shouldReceive('fileGetContents')
            ->once()
            ->with('http://foo.com/')
            ->andReturn(false);

        $this->streamClient->send('http://foo.com/', 'GET', 'foo_body', [], 60);
    }
}
