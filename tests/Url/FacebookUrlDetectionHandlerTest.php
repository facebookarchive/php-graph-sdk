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
namespace Facebook\Tests\Url;

use Facebook\Url\FacebookUrlDetectionHandler;

class FacebookUrlDetectionHandlerTest extends \PHPUnit_Framework_TestCase
{

  public function setUp()
  {
    unset(
      $_SERVER['REQUEST_URI'],
      $_SERVER['HTTPS'],
      $_SERVER['SERVER_PORT'],
      $_SERVER['SERVER_NAME'],
      $_SERVER['SERVER_ADDR'],
      $_SERVER['HTTP_HOST'],
      $_SERVER['HTTP_X_FORWARDED_HOST'],
      $_SERVER['HTTP_X_FORWARDED_PORT'],
      $_SERVER['HTTP_X_FORWARDED_PROTO']
    );
  }

  public function testProperlyGeneratesUrlFromCommonScenario()
  {
    $_SERVER['HTTP_HOST'] = 'foo.bar';
    $_SERVER['SERVER_PORT'] = '80';
    $_SERVER['REQUEST_URI'] = '/baz?foo=123';

    $urlHandler = new FacebookUrlDetectionHandler();
    $currentUri = $urlHandler->getCurrentUrl();

    $this->assertEquals('http://foo.bar/baz?foo=123', $currentUri);
  }

  public function testProperlyGeneratesSecureUrlFromCommonScenario()
  {
    $_SERVER['HTTP_HOST'] = 'foo.bar';
    $_SERVER['SERVER_PORT'] = '443';
    $_SERVER['REQUEST_URI'] = '/baz?foo=123';

    $urlHandler = new FacebookUrlDetectionHandler();
    $currentUri = $urlHandler->getCurrentUrl();

    $this->assertEquals('https://foo.bar/baz?foo=123', $currentUri);
  }

  public function testProperlyGeneratesUrlFromProxy()
  {
    $_SERVER['HTTP_X_FORWARDED_PORT'] = '80';
    $_SERVER['HTTP_X_FORWARDED_PROTO'] = 'http';
    $_SERVER['HTTP_HOST'] = 'foo.bar';
    $_SERVER['SERVER_PORT'] = '80';
    $_SERVER['REQUEST_URI'] = '/baz?foo=123';

    $urlHandler = new FacebookUrlDetectionHandler();
    $currentUri = $urlHandler->getCurrentUrl();

    $this->assertEquals('http://foo.bar/baz?foo=123', $currentUri);
  }

  public function testProperlyGeneratesSecureUrlFromProxy()
  {
    $_SERVER['HTTP_X_FORWARDED_PORT'] = '443';
    $_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';
    $_SERVER['HTTP_HOST'] = 'foo.bar';
    $_SERVER['SERVER_PORT'] = '80';
    $_SERVER['REQUEST_URI'] = '/baz?foo=123';

    $urlHandler = new FacebookUrlDetectionHandler();
    $currentUri = $urlHandler->getCurrentUrl();

    $this->assertEquals('https://foo.bar/baz?foo=123', $currentUri);
  }

  public function testProperlyGeneratesUrlWithCustomPort()
  {
    $_SERVER['HTTP_HOST'] = 'foo.bar';
    $_SERVER['SERVER_PORT'] = '1337';
    $_SERVER['REQUEST_URI'] = '/foo.php';

    $urlHandler = new FacebookUrlDetectionHandler();
    $currentUri = $urlHandler->getCurrentUrl();

    $this->assertEquals('http://foo.bar:1337/foo.php', $currentUri);
  }

  public function testProperlyGeneratesSecureUrlWithCustomPort()
  {
    $_SERVER['HTTP_HOST'] = 'foo.bar';
    $_SERVER['SERVER_PORT'] = '1337';
    $_SERVER['REQUEST_URI'] = '/foo.php';
    $_SERVER['HTTPS'] = 'On';

    $urlHandler = new FacebookUrlDetectionHandler();
    $currentUri = $urlHandler->getCurrentUrl();

    $this->assertEquals('https://foo.bar:1337/foo.php', $currentUri);
  }

  public function testProperlyGeneratesUrlWithCustomPortFromProxy()
  {
    $_SERVER['HTTP_X_FORWARDED_PORT'] = '8888';
    $_SERVER['HTTP_X_FORWARDED_PROTO'] = 'http';
    $_SERVER['HTTP_HOST'] = 'foo.bar';
    $_SERVER['SERVER_PORT'] = '80';
    $_SERVER['REQUEST_URI'] = '/foo.php';

    $urlHandler = new FacebookUrlDetectionHandler();
    $currentUri = $urlHandler->getCurrentUrl();

    $this->assertEquals('http://foo.bar:8888/foo.php', $currentUri);
  }

}
