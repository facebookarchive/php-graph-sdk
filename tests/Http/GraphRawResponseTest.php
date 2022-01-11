<?php

declare(strict_types=1);
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
namespace Facebook\Tests\Http;

use Facebook\Http\GraphRawResponse;
use PHPUnit\Framework\TestCase;

/**
 * Class GraphRawResponseTest
 */
class GraphRawResponseTest extends TestCase
{

    protected string $fakeRawProxyHeader = "HTTP/1.0 200 Connection established
Proxy-agent: Kerio Control/7.1.1 build 1971\r\n\r\n";
    protected string $fakeRawHeader = <<<HEADER
HTTP/1.1 200 OK
Etag: "9d86b21aa74d74e574bbb35ba13524a52deb96e3"
Content-Type: text/javascript; charset=UTF-8
X-FB-Rev: 9244768
Date: Mon, 19 May 2014 18:37:17 GMT
X-FB-Debug: 02QQiffE7JG2rV6i/Agzd0gI2/OOQ2lk5UW0=
Access-Control-Allow-Origin: *\r\n\r\n
HEADER;
    protected array $fakeHeadersAsArray = [
        'Etag' => '"9d86b21aa74d74e574bbb35ba13524a52deb96e3"',
        'Content-Type' => 'text/javascript; charset=UTF-8',
        'X-FB-Rev' => '9244768',
        'Date' => 'Mon, 19 May 2014 18:37:17 GMT',
        'X-FB-Debug' => '02QQiffE7JG2rV6i/Agzd0gI2/OOQ2lk5UW0=',
        'Access-Control-Allow-Origin' => '*',
    ];

    protected string $jsonFakeHeader = 'x-fb-ads-insights-throttle: {"app_id_util_pct": 0.00,"acc_id_util_pct": 0.00}';
    protected array $jsonFakeHeaderAsArray = ['x-fb-ads-insights-throttle' => '{"app_id_util_pct": 0.00,"acc_id_util_pct": 0.00}'];

    public function testCanSetTheHeadersFromAnArray(): void
    {
        $myHeaders = [
            'foo' => 'bar',
            'baz' => 'faz',
        ];
        $response = new GraphRawResponse($myHeaders, '');
        $headers = $response->getHeaders();

        static::assertEquals($myHeaders, $headers);
    }

    public function testCanSetTheHeadersFromAString(): void
    {
        $response = new GraphRawResponse($this->fakeRawHeader, '');
        $headers = $response->getHeaders();
        $httpResponseCode = $response->getHttpResponseCode();

        static::assertEquals($this->fakeHeadersAsArray, $headers);
        static::assertEquals(200, $httpResponseCode);
    }

    public function testWillIgnoreProxyHeaders(): void
    {
        $response = new GraphRawResponse($this->fakeRawProxyHeader . $this->fakeRawHeader, '');
        $headers = $response->getHeaders();
        $httpResponseCode = $response->getHttpResponseCode();

        static::assertEquals($this->fakeHeadersAsArray, $headers);
        static::assertEquals(200, $httpResponseCode);
    }

    public function testCanTransformJsonHeaderValues(): void
    {
        $response = new GraphRawResponse($this->jsonFakeHeader, '');
        $headers = $response->getHeaders();

        static::assertEquals($this->jsonFakeHeaderAsArray['x-fb-ads-insights-throttle'], $headers['x-fb-ads-insights-throttle']);
    }
    
    public function testHttpResponseCode(): void
    {
        // HTTP/1.0
        $headers = str_replace('HTTP/1.1', 'HTTP/1.0', $this->fakeRawHeader);
        $response = new GraphRawResponse($headers, '');
        static::assertEquals(200, $response->getHttpResponseCode());
        
        // HTTP/1.1
        $response = new GraphRawResponse($this->fakeRawHeader, '');
        static::assertEquals(200, $response->getHttpResponseCode());
        
        // HTTP/2
        $headers = str_replace('HTTP/1.1', 'HTTP/2', $this->fakeRawHeader);
        $response = new GraphRawResponse($headers, '');
        static::assertEquals(200, $response->getHttpResponseCode());
    }
}
