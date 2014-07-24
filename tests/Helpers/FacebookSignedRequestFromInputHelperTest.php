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
namespace Facebook\Tests\Helpers;

use Mockery as m;
use Facebook\Entities\FacebookApp;
use Facebook\Helpers\FacebookSignedRequestFromInputHelper;

class FooSignedRequestHelper extends FacebookSignedRequestFromInputHelper {
  public function getRawSignedRequest() {
    return null;
  }
}

class FacebookSignedRequestFromInputHelperTest extends \PHPUnit_Framework_TestCase
{

  /**
   * @var FooSignedRequestHelper
   */
  protected $helper;

  public $rawSignedRequestAuthorizedWithAccessToken = 'vdZXlVEQ5NTRRTFvJ7Jeo_kP4SKnBDvbNP0fEYKS0Sg=.eyJvYXV0aF90b2tlbiI6ImZvb190b2tlbiIsImFsZ29yaXRobSI6IkhNQUMtU0hBMjU2IiwiaXNzdWVkX2F0IjoxNDAyNTUxMDMxLCJ1c2VyX2lkIjoiMTIzIn0=';
  public $rawSignedRequestAuthorizedWithCode = 'oBtmZlsFguNQvGRETDYQQu1-PhwcArgbBBEK4urbpRA=.eyJjb2RlIjoiZm9vX2NvZGUiLCJhbGdvcml0aG0iOiJITUFDLVNIQTI1NiIsImlzc3VlZF9hdCI6MTQwNjMxMDc1MiwidXNlcl9pZCI6IjEyMyJ9';
  public $rawSignedRequestUnauthorized = 'KPlyhz-whtYAhHWr15N5TkbS_avz-2rUJFpFkfXKC88=.eyJhbGdvcml0aG0iOiJITUFDLVNIQTI1NiIsImlzc3VlZF9hdCI6MTQwMjU1MTA4Nn0=';

  public function setUp()
  {
    $app = new FacebookApp('123', 'foo_app_secret');
    $this->helper = new FooSignedRequestHelper($app);
  }

  public function testSignedRequestDataCanBeRetrievedFromGetData()
  {
    $_GET['signed_request'] = 'foo_signed_request';

    $rawSignedRequest = $this->helper->getRawSignedRequestFromGet();

    $this->assertEquals('foo_signed_request', $rawSignedRequest);
  }

  public function testSignedRequestDataCanBeRetrievedFromPostData()
  {
    $_POST['signed_request'] = 'foo_signed_request';

    $rawSignedRequest = $this->helper->getRawSignedRequestFromPost();

    $this->assertEquals('foo_signed_request', $rawSignedRequest);
  }

  public function testSignedRequestDataCanBeRetrievedFromCookieData()
  {
    $_COOKIE['fbsr_123'] = 'foo_signed_request';

    $rawSignedRequest = $this->helper->getRawSignedRequestFromCookie();

    $this->assertEquals('foo_signed_request', $rawSignedRequest);
  }

  public function testAccessTokenWillBeNullWhenAUserHasNotYetAuthorizedTheApp()
  {
    $client = m::mock('Facebook\FacebookClient');
    $client
      ->shouldReceive('sendRequest')
      ->never();

    $this->helper->instantiateSignedRequest($this->rawSignedRequestUnauthorized);
    $accessToken = $this->helper->getAccessToken($client);

    $this->assertNull($accessToken);
  }

  public function testAnAccessTokenCanBeInstantiatedWhenRedirectReturnsAnAccessToken()
  {
    $client = m::mock('Facebook\FacebookClient');
    $client
      ->shouldReceive('sendRequest')
      ->never();

    $this->helper->instantiateSignedRequest($this->rawSignedRequestAuthorizedWithAccessToken);
    $accessToken = $this->helper->getAccessToken($client);

    $this->assertInstanceOf('Facebook\Entities\AccessToken', $accessToken);
    $this->assertEquals('foo_token', (string) $accessToken);
  }

  public function testAnAccessTokenCanBeInstantiatedWhenRedirectReturnsACode()
  {
    $response = m::mock('Facebook\Entities\FacebookResponse');
    $response
      ->shouldReceive('getDecodedBody')
      ->once()
      ->andReturn([
          'access_token' => 'access_token_from_code',
          'expires' => 555,
        ]);
    $client = m::mock('Facebook\FacebookClient');
    $client
      ->shouldReceive('sendRequest')
      ->with(m::type('Facebook\Entities\FacebookRequest'))
      ->once()
      ->andReturn($response);

    $this->helper->instantiateSignedRequest($this->rawSignedRequestAuthorizedWithCode);
    $accessToken = $this->helper->getAccessToken($client);

    $this->assertInstanceOf('Facebook\Entities\AccessToken', $accessToken);
    $this->assertEquals('access_token_from_code', (string) $accessToken);
  }

}
