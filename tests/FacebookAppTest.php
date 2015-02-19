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
namespace Facebook\Tests;

use Facebook\FacebookApp;

class FacebookAppTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FacebookApp
     */
    private $app;

    public function setUp()
    {
        $this->app = new FacebookApp('id', 'secret');
    }

    public function testGetId()
    {
        $this->assertEquals('id', $this->app->getId());
    }

    public function testGetSecret()
    {
        $this->assertEquals('secret', $this->app->getSecret());
    }

    public function testAnAppAccessTokenCanBeGenerated()
    {
        $accessToken = $this->app->getAccessToken();

        $this->assertInstanceOf('Facebook\Authentication\AccessToken', $accessToken);
        $this->assertEquals('id|secret', (string)$accessToken);
    }

    public function testSerialization()
    {
        $newApp = unserialize(serialize($this->app));

        $this->assertInstanceOf('Facebook\FacebookApp', $newApp);
        $this->assertEquals('id', $newApp->getId());
        $this->assertEquals('secret', $newApp->getSecret());
    }
}
