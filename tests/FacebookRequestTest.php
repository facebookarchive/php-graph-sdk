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

use Facebook\Tests\FacebookTestCredentials;
use Facebook\FacebookRequest;
use Facebook\FacebookSession;
use Facebook\Tests\FacebookTestHelper;

class FacebookRequestTest extends \PHPUnit_Framework_TestCase
{

  public function testGetsTheLoggedInUsersProfile()
  {
    $response = (
      new FacebookRequest(
        FacebookTestHelper::$testSession,
        'GET',
        '/me'
      ))->execute()->getGraphObject();
    $this->assertNotNull($response->getProperty('id'));
    $this->assertNotNull($response->getProperty('name'));
  }

  public function testCanPostAndDelete()
  {
    // Create a test user
    $params = array(
      'name' => 'Foo User',
    );
    $response = (
      new FacebookRequest(
        new FacebookSession(FacebookTestHelper::getAppToken()),
        'POST',
        '/' . FacebookTestCredentials::$appId . '/accounts/test-users',
        $params
      ))->execute()->getGraphObject();
    $user_id = $response->getProperty('id');
    $this->assertNotNull($user_id);

    // Delete test user
    $response = (
    new FacebookRequest(
      new FacebookSession(FacebookTestHelper::getAppToken()),
      'DELETE',
      '/' . $user_id
    ))->execute()->getGraphObject()->asArray();
    $this->assertTrue($response);
  }

  public function testETagHit()
  {
    $response = (
    new FacebookRequest(
      FacebookTestHelper::$testSession,
      'GET',
      '/104048449631599'
    ))->execute();

    $response = (
    new FacebookRequest(
      FacebookTestHelper::$testSession,
      'GET',
      '/104048449631599',
      null,
      null,
      $response->getETag()
    ))->execute();

    $this->assertTrue($response->isETagHit());
    $this->assertNull($response->getETag());
  }

  public function testETagMiss()
  {
    $response = (
    new FacebookRequest(
      FacebookTestHelper::$testSession,
      'GET',
      '/104048449631599',
      null,
      null,
      'someRandomValue'
    ))->execute();

    $this->assertFalse($response->isETagHit());
    $this->assertNotNull($response->getETag());
  }

  public function testGracefullyHandlesUrlAppending()
  {
    $params = array();
    $url = 'https://www.foo.com/';
    $processed_url = FacebookRequest::appendParamsToUrl($url, $params);
    $this->assertEquals('https://www.foo.com/', $processed_url);

    $params = array(
      'access_token' => 'foo',
    );
    $url = 'https://www.foo.com/';
    $processed_url = FacebookRequest::appendParamsToUrl($url, $params);
    $this->assertEquals('https://www.foo.com/?access_token=foo', $processed_url);

    $params = array(
      'access_token' => 'foo',
      'bar' => 'baz',
    );
    $url = 'https://www.foo.com/?foo=bar';
    $processed_url = FacebookRequest::appendParamsToUrl($url, $params);
    $this->assertEquals('https://www.foo.com/?access_token=foo&bar=baz&foo=bar', $processed_url);

    $params = array(
      'access_token' => 'foo',
    );
    $url = 'https://www.foo.com/?foo=bar&access_token=bar';
    $processed_url = FacebookRequest::appendParamsToUrl($url, $params);
    $this->assertEquals('https://www.foo.com/?access_token=bar&foo=bar', $processed_url);
  }

  public function testAppSecretProof()
  {
    $enableAppSecretProof = FacebookSession::useAppSecretProof();

    FacebookSession::enableAppSecretProof(true);
    $request = new FacebookRequest(
      FacebookTestHelper::$testSession,
      'GET',
      '/me'
    );
    $this->assertTrue(isset($request->getParameters()['appsecret_proof']));


    FacebookSession::enableAppSecretProof(false);
    $request = new FacebookRequest(
      FacebookTestHelper::$testSession,
      'GET',
      '/me'
    );
    $this->assertFalse(isset($request->getParameters()['appsecret_proof']));

    FacebookSession::enableAppSecretProof($enableAppSecretProof);
  }

}