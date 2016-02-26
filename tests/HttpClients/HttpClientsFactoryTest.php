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

use Facebook\HttpClients\FacebookCurlHttpClient;
use Facebook\HttpClients\FacebookGuzzleHttpClient;
use Facebook\HttpClients\FacebookStreamHttpClient;
use Facebook\HttpClients\HttpClientsFactory;
use GuzzleHttp\Client;
use PHPUnit_Framework_TestCase;

class HttpClientsFactoryTest extends PHPUnit_Framework_TestCase
{
    const COMMON_NAMESPACE = 'Facebook\HttpClients\\';
    const COMMON_INTERFACE = 'Facebook\HttpClients\FacebookHttpClientInterface';

    /**
     * @param mixed  $handler
     * @param string $expected
     *
     * @dataProvider httpClientsProvider
     */
    public function testCreateHttpClient($handler, $expected)
    {
        $httpClient = HttpClientsFactory::createHttpClient($handler);

        $this->assertInstanceOf(self::COMMON_INTERFACE, $httpClient);
        $this->assertInstanceOf($expected, $httpClient);
    }

    /**
     * @return array
     */
    public function httpClientsProvider()
    {
        return [
            ['curl', self::COMMON_NAMESPACE . 'FacebookCurlHttpClient'],
            ['guzzle', self::COMMON_NAMESPACE . 'FacebookGuzzleHttpClient'],
            ['stream', self::COMMON_NAMESPACE . 'FacebookStreamHttpClient'],
            [new Client(), self::COMMON_NAMESPACE . 'FacebookGuzzleHttpClient'],
            [new FacebookCurlHttpClient(), self::COMMON_NAMESPACE . 'FacebookCurlHttpClient'],
            [new FacebookGuzzleHttpClient(), self::COMMON_NAMESPACE . 'FacebookGuzzleHttpClient'],
            [new FacebookStreamHttpClient(), self::COMMON_NAMESPACE . 'FacebookStreamHttpClient'],
            [null, self::COMMON_INTERFACE],
        ];
    }
}
