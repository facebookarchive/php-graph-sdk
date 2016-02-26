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
namespace Facebook\Tests;

use Facebook\FacebookApp;
use Facebook\SignedRequest;

class SignedRequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FacebookApp
     */
    protected $app;

    protected $rawSignature = 'U0_O1MqqNKUt32633zAkdd2Ce-jGVgRgJeRauyx_zC8=';
    protected $rawPayload = 'eyJvYXV0aF90b2tlbiI6ImZvb190b2tlbiIsImFsZ29yaXRobSI6IkhNQUMtU0hBMjU2IiwiaXNzdWVkX2F0IjozMjEsImNvZGUiOiJmb29fY29kZSIsInN0YXRlIjoiZm9vX3N0YXRlIiwidXNlcl9pZCI6MTIzLCJmb28iOiJiYXIifQ==';

    protected $payloadData = [
        'oauth_token' => 'foo_token',
        'algorithm' => 'HMAC-SHA256',
        'issued_at' => 321,
        'code' => 'foo_code',
        'state' => 'foo_state',
        'user_id' => 123,
        'foo' => 'bar',
    ];

    public function setUp()
    {
        $this->app = new FacebookApp('123', 'foo_app_secret');
    }

    public function testAValidSignedRequestCanBeCreated()
    {
        $sr = new SignedRequest($this->app);
        $rawSignedRequest = $sr->make($this->payloadData);

        $srTwo = new SignedRequest($this->app, $rawSignedRequest);
        $payload = $srTwo->getPayload();

        $expectedRawSignedRequest = $this->rawSignature . '.' . $this->rawPayload;
        $this->assertEquals($expectedRawSignedRequest, $rawSignedRequest);
        $this->assertEquals($this->payloadData, $payload);
    }

    /**
     * @expectedException \Facebook\Exceptions\FacebookSDKException
     */
    public function testInvalidSignedRequestsWillFailFormattingValidation()
    {
        new SignedRequest($this->app, 'invalid_signed_request');
    }

    public function testBase64EncodingIsUrlSafe()
    {
        $sr = new SignedRequest($this->app);
        $encodedData = $sr->base64UrlEncode('aijkoprstADIJKLOPQTUVX1256!)]-:;"<>?.|~');

        $this->assertEquals('YWlqa29wcnN0QURJSktMT1BRVFVWWDEyNTYhKV0tOjsiPD4_Lnx-', $encodedData);
    }

    public function testAUrlSafeBase64EncodedStringCanBeDecoded()
    {
        $sr = new SignedRequest($this->app);
        $decodedData = $sr->base64UrlDecode('YWlqa29wcnN0QURJSktMT1BRVFVWWDEyNTYhKV0tOjsiPD4/Lnx+');

        $this->assertEquals('aijkoprstADIJKLOPQTUVX1256!)]-:;"<>?.|~', $decodedData);
    }

    /**
     * @expectedException \Facebook\Exceptions\FacebookSDKException
     */
    public function testAnImproperlyEncodedSignatureWillThrowAnException()
    {
        new SignedRequest($this->app, 'foo_sig.' . $this->rawPayload);
    }

    /**
     * @expectedException \Facebook\Exceptions\FacebookSDKException
     */
    public function testAnImproperlyEncodedPayloadWillThrowAnException()
    {
        new SignedRequest($this->app, $this->rawSignature . '.foo_payload');
    }

    /**
     * @expectedException \Facebook\Exceptions\FacebookSDKException
     */
    public function testNonApprovedAlgorithmsWillThrowAnException()
    {
        $signedRequestData = $this->payloadData;
        $signedRequestData['algorithm'] = 'FOO-ALGORITHM';

        $sr = new SignedRequest($this->app);
        $rawSignedRequest = $sr->make($signedRequestData);

        new SignedRequest($this->app, $rawSignedRequest);
    }

    public function testAsRawSignedRequestCanBeValidatedAndDecoded()
    {
        $rawSignedRequest = $this->rawSignature . '.' . $this->rawPayload;
        $sr = new SignedRequest($this->app, $rawSignedRequest);

        $this->assertEquals($this->payloadData, $sr->getPayload());
    }

    public function testARawSignedRequestCanBeValidatedAndDecoded()
    {
        $rawSignedRequest = $this->rawSignature . '.' . $this->rawPayload;
        $sr = new SignedRequest($this->app, $rawSignedRequest);

        $this->assertEquals($sr->getPayload(), $this->payloadData);
        $this->assertEquals($sr->getRawSignedRequest(), $rawSignedRequest);
        $this->assertEquals(123, $sr->getUserId());
        $this->assertTrue($sr->hasOAuthData());
    }
}
