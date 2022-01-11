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
namespace Facebook\Tests\Url;

use Facebook\Url\UrlDetectionHandler;
use PHPUnit\Framework\TestCase;

/**
 * Class UrlDetectionHandlerTest
 */
class UrlDetectionHandlerTest extends TestCase
{
    public function testProperlyGeneratesUrlFromCommonScenario(): void
    {
        $_SERVER = [
            'HTTP_HOST' => 'foo.bar',
            'SERVER_PORT' => '80',
            'REQUEST_URI' => '/baz?foo=123',
        ];

        $urlHandler = new UrlDetectionHandler();
        $currentUri = $urlHandler->getCurrentUrl();

        static::assertEquals('http://foo.bar/baz?foo=123', $currentUri);
    }

    public function testProperlyGeneratesSecureUrlFromCommonScenario(): void
    {
        $_SERVER = [
            'HTTP_HOST' => 'foo.bar',
            'SERVER_PORT' => '443',
            'REQUEST_URI' => '/baz?foo=123',
        ];

        $urlHandler = new UrlDetectionHandler();
        $currentUri = $urlHandler->getCurrentUrl();

        static::assertEquals('https://foo.bar/baz?foo=123', $currentUri);
    }

    public function testProperlyGeneratesUrlFromProxy(): void
    {
        $_SERVER = [
            'HTTP_X_FORWARDED_PORT' => '80',
            'HTTP_X_FORWARDED_PROTO' => 'http',
            'HTTP_HOST' => 'foo.bar',
            'SERVER_PORT' => '80',
            'REQUEST_URI' => '/baz?foo=123',
        ];

        $urlHandler = new UrlDetectionHandler();
        $currentUri = $urlHandler->getCurrentUrl();

        static::assertEquals('http://foo.bar/baz?foo=123', $currentUri);
    }

    public function testProperlyGeneratesSecureUrlFromProxy(): void
    {
        $_SERVER = [
            'HTTP_X_FORWARDED_PORT' => '443',
            'HTTP_X_FORWARDED_PROTO' => 'https',
            'HTTP_HOST' => 'foo.bar',
            'SERVER_PORT' => '80',
            'REQUEST_URI' => '/baz?foo=123',
        ];

        $urlHandler = new UrlDetectionHandler();
        $currentUri = $urlHandler->getCurrentUrl();

        static::assertEquals('https://foo.bar/baz?foo=123', $currentUri);
    }

    public function testProperlyGeneratesUrlWithCustomPort(): void
    {
        $_SERVER = [
            'HTTP_HOST' => 'foo.bar',
            'SERVER_PORT' => '1337',
            'REQUEST_URI' => '/foo.php',
        ];

        $urlHandler = new UrlDetectionHandler();
        $currentUri = $urlHandler->getCurrentUrl();

        static::assertEquals('http://foo.bar:1337/foo.php', $currentUri);
    }

    public function testProperlyGeneratesSecureUrlWithCustomPort(): void
    {
        $_SERVER = [
            'HTTP_HOST' => 'foo.bar',
            'SERVER_PORT' => '1337',
            'REQUEST_URI' => '/foo.php',
            'HTTPS' => 'On',
        ];

        $urlHandler = new UrlDetectionHandler();
        $currentUri = $urlHandler->getCurrentUrl();

        static::assertEquals('https://foo.bar:1337/foo.php', $currentUri);
    }

    public function testProperlyGeneratesUrlWithCustomPortFromProxy(): void
    {
        $_SERVER = [
            'HTTP_X_FORWARDED_PORT' => '8888',
            'HTTP_X_FORWARDED_PROTO' => 'http',
            'HTTP_HOST' => 'foo.bar',
            'SERVER_PORT' => '80',
            'REQUEST_URI' => '/foo.php',
        ];

        $urlHandler = new UrlDetectionHandler();
        $currentUri = $urlHandler->getCurrentUrl();

        static::assertEquals('http://foo.bar:8888/foo.php', $currentUri);
    }
}
