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

namespace Facebook\Tests\Helpers;

use Facebook\Application;
use Facebook\Client;
use Facebook\Helpers\JavaScriptHelper;
use PHPUnit\Framework\TestCase;

/**
 * Class JavaScriptHelperTest
 */
class JavaScriptHelperTest extends TestCase
{
    public string $rawSignedRequestAuthorized = 'vdZXlVEQ5NTRRTFvJ7Jeo_kP4SKnBDvbNP0fEYKS0Sg=.eyJvYXV0aF90b2tlbiI6ImZvb190b2tlbiIsImFsZ29yaXRobSI6IkhNQUMtU0hBMjU2IiwiaXNzdWVkX2F0IjoxNDAyNTUxMDMxLCJ1c2VyX2lkIjoiMTIzIn0=';

    public function testARawSignedRequestCanBeRetrievedFromCookieData(): void
    {
        $_COOKIE['fbsr_123'] = $this->rawSignedRequestAuthorized;

        $app = new Application('123', 'foo_app_secret');
        $helper = new JavaScriptHelper($app, new Client(), 'v0.0');

        $rawSignedRequest = $helper->getRawSignedRequest();

        static::assertEquals($this->rawSignedRequestAuthorized, $rawSignedRequest);
    }
}
