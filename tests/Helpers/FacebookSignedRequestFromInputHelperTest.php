<?php
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

use Facebook\FacebookApp;
use Facebook\Tests\Fixtures\FooSignedRequestHelper;
use Facebook\Tests\Fixtures\FooSignedRequestHelperFacebookClient;

class FacebookSignedRequestFromInputHelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FooSignedRequestHelper
     */
    protected $helper;

    public $rawSignedRequestAuthorizedWithAccessToken = 'vdZXlVEQ5NTRRTFvJ7Jeo_kP4SKnBDvbNP0fEYKS0Sg=.eyJvYXV0aF90b2tlbiI6ImZvb190b2tlbiIsImFsZ29yaXRobSI6IkhNQUMtU0hBMjU2IiwiaXNzdWVkX2F0IjoxNDAyNTUxMDMxLCJ1c2VyX2lkIjoiMTIzIn0=';
    public $rawSignedRequestAuthorizedWithCode = 'oBtmZlsFguNQvGRETDYQQu1-PhwcArgbBBEK4urbpRA=.eyJjb2RlIjoiZm9vX2NvZGUiLCJhbGdvcml0aG0iOiJITUFDLVNIQTI1NiIsImlzc3VlZF9hdCI6MTQwNjMxMDc1MiwidXNlcl9pZCI6IjEyMyJ9';
    public $rawSignedRequestUnauthorized = 'KPlyhz-whtYAhHWr15N5TkbS_avz-2rUJFpFkfXKC88=.eyJhbGdvcml0aG0iOiJITUFDLVNIQTI1NiIsImlzc3VlZF9hdCI6MTQwMjU1MTA4Nn0=';

    protected function setUp()
    {
        $app = new FacebookApp('123', 'foo_app_secret');
        $this->helper = new FooSignedRequestHelper($app, new FooSignedRequestHelperFacebookClient());
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
        $this->helper->instantiateSignedRequest($this->rawSignedRequestUnauthorized);
        $accessToken = $this->helper->getAccessToken();

        $this->assertNull($accessToken);
    }

    public function testAnAccessTokenCanBeInstantiatedWhenRedirectReturnsAnAccessToken()
    {
        $this->helper->instantiateSignedRequest($this->rawSignedRequestAuthorizedWithAccessToken);
        $accessToken = $this->helper->getAccessToken();

        $this->assertInstanceOf('Facebook\Authentication\AccessToken', $accessToken);
        $this->assertEquals('foo_token', $accessToken->getValue());
    }

    public function testAnAccessTokenCanBeInstantiatedWhenRedirectReturnsACode()
    {
        $this->helper->instantiateSignedRequest($this->rawSignedRequestAuthorizedWithCode);
        $accessToken = $this->helper->getAccessToken();

        $this->assertInstanceOf('Facebook\Authentication\AccessToken', $accessToken);
        $this->assertEquals('foo_access_token_from:foo_code', $accessToken->getValue());
    }
}
