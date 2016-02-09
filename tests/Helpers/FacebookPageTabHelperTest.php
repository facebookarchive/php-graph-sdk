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
namespace Facebook\Tests\Helpers;

use Facebook\FacebookApp;
use Facebook\FacebookClient;
use Facebook\Helpers\FacebookPageTabHelper;

class FacebookPageTabHelperTest extends \PHPUnit_Framework_TestCase
{
    protected $rawSignedRequestAuthorized = '6Hi26ECjkj347belC0O8b8H5lwiIz5eA6V9VVjTg-HU=.eyJhbGdvcml0aG0iOiJITUFDLVNIQTI1NiIsImlzc3VlZF9hdCI6MzIxLCJvYXV0aF90b2tlbiI6ImZvb190b2tlbiIsInVzZXJfaWQiOiIxMjMiLCJwYWdlIjp7ImlkIjoiNDIiLCJsaWtlZCI6dHJ1ZSwiYWRtaW4iOmZhbHNlfX0=';

    public function testPageDataCanBeAccessed()
    {
        $_POST['signed_request'] = $this->rawSignedRequestAuthorized;

        $app = new FacebookApp('123', 'foo_app_secret');
        $helper = new FacebookPageTabHelper($app, new FacebookClient());

        $this->assertFalse($helper->isAdmin());
        $this->assertEquals('42', $helper->getPageId());
        $this->assertEquals('42', $helper->getPageData('id'));
        $this->assertEquals('default', $helper->getPageData('foo', 'default'));
    }
}
