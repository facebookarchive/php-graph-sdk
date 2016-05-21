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
use Facebook\Helpers\FacebookCanvasHelper;

class FacebookCanvasHelperTest extends \PHPUnit_Framework_TestCase
{
    public $rawSignedRequestAuthorized = 'vdZXlVEQ5NTRRTFvJ7Jeo_kP4SKnBDvbNP0fEYKS0Sg=.eyJvYXV0aF90b2tlbiI6ImZvb190b2tlbiIsImFsZ29yaXRobSI6IkhNQUMtU0hBMjU2IiwiaXNzdWVkX2F0IjoxNDAyNTUxMDMxLCJ1c2VyX2lkIjoiMTIzIn0=';

    /**
     * @var FacebookCanvasHelper
     */
    protected $helper;

    protected function setUp()
    {
        $app = new FacebookApp('123', 'foo_app_secret');
        $this->helper = new FacebookCanvasHelper($app, new FacebookClient());
    }

    public function testSignedRequestDataCanBeRetrievedFromPostData()
    {
        $_POST['signed_request'] = $this->rawSignedRequestAuthorized;

        $rawSignedRequest = $this->helper->getRawSignedRequest();

        $this->assertEquals($this->rawSignedRequestAuthorized, $rawSignedRequest);
    }
}
