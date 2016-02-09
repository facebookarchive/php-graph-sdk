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
namespace Facebook\Tests\Http;

use Facebook\Http\GraphRawResponse;

class GraphRawResponseTest extends \PHPUnit_Framework_TestCase
{

    protected $fakeRawProxyHeader = "HTTP/1.0 200 Connection established
Proxy-agent: Kerio Control/7.1.1 build 1971\r\n\r\n";
    protected $fakeRawHeader = <<<HEADER
HTTP/1.1 200 OK
Etag: "9d86b21aa74d74e574bbb35ba13524a52deb96e3"
Content-Type: text/javascript; charset=UTF-8
X-FB-Rev: 9244768
Date: Mon, 19 May 2014 18:37:17 GMT
X-FB-Debug: 02QQiffE7JG2rV6i/Agzd0gI2/OOQ2lk5UW0=
Access-Control-Allow-Origin: *\r\n\r\n
HEADER;
    protected $fakeHeadersAsArray = [
        'Etag' => '"9d86b21aa74d74e574bbb35ba13524a52deb96e3"',
        'Content-Type' => 'text/javascript; charset=UTF-8',
        'X-FB-Rev' => '9244768',
        'Date' => 'Mon, 19 May 2014 18:37:17 GMT',
        'X-FB-Debug' => '02QQiffE7JG2rV6i/Agzd0gI2/OOQ2lk5UW0=',
        'Access-Control-Allow-Origin' => '*',
    ];

    public function testCanSetTheHeadersFromAnArray()
    {
        $myHeaders = [
            'foo' => 'bar',
            'baz' => 'faz',
        ];
        $response = new GraphRawResponse($myHeaders, '');
        $headers = $response->getHeaders();

        $this->assertEquals($myHeaders, $headers);
    }

    public function testCanSetTheHeadersFromAString()
    {
        $response = new GraphRawResponse($this->fakeRawHeader, '');
        $headers = $response->getHeaders();
        $httpResponseCode = $response->getHttpResponseCode();

        $this->assertEquals($this->fakeHeadersAsArray, $headers);
        $this->assertEquals(200, $httpResponseCode);
    }

    public function testWillIgnoreProxyHeaders()
    {
        $response = new GraphRawResponse($this->fakeRawProxyHeader . $this->fakeRawHeader, '');
        $headers = $response->getHeaders();
        $httpResponseCode = $response->getHttpResponseCode();

        $this->assertEquals($this->fakeHeadersAsArray, $headers);
        $this->assertEquals(200, $httpResponseCode);
    }
}
