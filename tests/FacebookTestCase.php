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

use Mockery as m;
use Facebook\FacebookClient;
use Facebook\Entities\FacebookApp;
use Facebook\Entities\AccessToken;
use Facebook\Entities\FacebookRequest;
use Facebook\Helpers\FacebookTestHelper;

class FacebookTestCase extends \PHPUnit_Framework_TestCase
{
  /**
   * @param string $appId
   * @param string $appSecret
   *
   * @return FacebookApp
   */
  public function getAppMock($appId, $appSecret)
  {
    return m::mock('Facebook\Entities\FacebookApp', [$appId, $appSecret])->makePartial();
  }

  /**
   * @param FacebookApp $app
   * @param string $value
   *
   * @return AccessToken
   */
  public function getAccessTokenMock(FacebookApp $app, $value)
  {
    return m::mock('Facebook\Entities\AccessToken', [$app, $value])->makePartial();
  }

  /**
   * @param string $expectedEndpoint
   * @param string $expectedMethod
   * @param array $expectedParams
   * @param array $raw
   *
   * @return FacebookClient
   */
  public function getClientMock($expectedEndpoint, $expectedMethod = 'GET', array $expectedParams = [], $raw = '')
  {
    return m::mock('Facebook\FacebookClient[handle]')
      ->shouldReceive('handle')
      ->once()
      ->with(m::on(function($request) use ($expectedEndpoint, $expectedMethod, $expectedParams) {
        if (!$request instanceof FacebookRequest) {
          return false;
        }

        if ($expectedEndpoint !== $request->getEndpoint()) {
          return false;
        }

        if ($expectedMethod !== $request->getMethod()) {
          return false;
        }

        $params = $request->getParameters();
        if ($params !== $expectedParams) {
          return false;
        }

        return true;
      }))
      ->andReturnUsing(function($request) use ($raw) {
        return m::mock('Facebook\Entities\FacebookResponse', [
          $request,
          $raw
        ])->makePartial();
      })
      ->getMock();
  }

  /**
   * @return FacebookClient
   */
  public function getTestClient()
  {
    return (new FacebookTestHelper())->getClient();
  }

  /**
   * @return FacebookApp
   */
  public function getTestApp()
  {
    return (new FacebookTestHelper())->getApp();
  }

  /**
   * @return AccessToken
   */
  public function getTestAccessToken()
  {
    return (new FacebookTestHelper())->getAccessToken();
  }

}
