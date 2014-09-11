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
namespace Facebook\Tests\Entities;

use Facebook\Entities\SignedRequest;

class SignedRequestTest extends \PHPUnit_Framework_TestCase
{

  public $appSecret = 'foo_app_secret';

  public $rawSignedRequest = 'U0_O1MqqNKUt32633zAkdd2Ce-jGVgRgJeRauyx_zC8=.eyJvYXV0aF90b2tlbiI6ImZvb190b2tlbiIsImFsZ29yaXRobSI6IkhNQUMtU0hBMjU2IiwiaXNzdWVkX2F0IjozMjEsImNvZGUiOiJmb29fY29kZSIsInN0YXRlIjoiZm9vX3N0YXRlIiwidXNlcl9pZCI6MTIzLCJmb28iOiJiYXIifQ==';

  public $payloadData = [
    'oauth_token' => 'foo_token',
    'algorithm' => 'HMAC-SHA256',
    'issued_at' => 321,
    'code' => 'foo_code',
    'state' => 'foo_state',
    'user_id' => 123,
    'foo' => 'bar',
  ];

  public function testValidSignedRequestsWillPassFormattingValidation()
  {
    $sr = SignedRequest::make($this->payloadData, $this->appSecret);
    SignedRequest::validateFormat($sr);
  }

  /**
   * @expectedException \Facebook\Exceptions\FacebookSDKException
   */
  public function testInvalidSignedRequestsWillFailFormattingValidation()
  {
    SignedRequest::validateFormat('invalid_signed_request');
  }

  public function testSignatureAndPayloadCanBeSeparatedInSignedRequests()
  {
    list($sig, $payload) = SignedRequest::split('sig.payload');

    $this->assertEquals('sig', $sig);
    $this->assertEquals('payload', $payload);
  }

  public function testBase64EncodingIsUrlSafe()
  {
    $encodedData = SignedRequest::base64UrlEncode('aijkoprstADIJKLOPQTUVX1256!)]-:;"<>?.|~');

    $this->assertEquals('YWlqa29wcnN0QURJSktMT1BRVFVWWDEyNTYhKV0tOjsiPD4_Lnx-', $encodedData);
  }

  public function testAUrlSafeBase64EncodedStringCanBeDecoded()
  {
    $decodedData = SignedRequest::base64UrlDecode('YWlqa29wcnN0QURJSktMT1BRVFVWWDEyNTYhKV0tOjsiPD4/Lnx+');

    $this->assertEquals('aijkoprstADIJKLOPQTUVX1256!)]-:;"<>?.|~', $decodedData);
  }

  public function testAValidEncodedSignatureCanBeDecoded()
  {
    $decodedSig = SignedRequest::decodeSignature('c2ln');

    $this->assertEquals('sig', $decodedSig);
  }

  /**
   * @expectedException \Facebook\Exceptions\FacebookSDKException
   */
  public function testAnImproperlyEncodedSignatureWillThrowAnException()
  {
    SignedRequest::decodeSignature('foo!');
  }

  public function testAValidEncodedPayloadCanBeDecoded()
  {
    $decodedPayload = SignedRequest::decodePayload('WyJwYXlsb2FkIl0=');

    $this->assertEquals(['payload'], $decodedPayload);
  }

  /**
   * @expectedException \Facebook\Exceptions\FacebookSDKException
   */
  public function testAnImproperlyEncodedPayloadWillThrowAnException()
  {
    SignedRequest::decodePayload('foo!');
  }

  public function testSignedRequestDataMustContainTheHmacSha256Algorithm()
  {
    SignedRequest::validateAlgorithm($this->payloadData);
  }

  /**
   * @expectedException \Facebook\Exceptions\FacebookSDKException
   */
  public function testNonApprovedAlgorithmsWillThrowAnException()
  {
    $signedRequestData = $this->payloadData;
    $signedRequestData['algorithm'] = 'FOO-ALGORITHM';
    SignedRequest::validateAlgorithm($signedRequestData);
  }

  public function testASignatureHashCanBeGeneratedFromBase64EncodedData()
  {
    $hashedSig = SignedRequest::hashSignature('WyJwYXlsb2FkIl0=', $this->appSecret);

    $expectedSig = base64_decode('bFofyO2sERX73y8uvuX26SLodv0mZ+Zk18d8b3zhD+s=');
    $this->assertEquals($expectedSig, $hashedSig);
  }

  public function testTwoBinaryStringsCanBeComparedForSignatureValidation()
  {
    $hashedSig = base64_decode('bFofyO2sERX73y8uvuX26SLodv0mZ+Zk18d8b3zhD+s=');
    SignedRequest::validateSignature($hashedSig, $hashedSig);
  }

  /**
   * @expectedException \Facebook\Exceptions\FacebookSDKException
   */
  public function testNonSameBinaryStringsWillThrowAnExceptionForSignatureValidation()
  {
    $hashedSig1 = base64_decode('bFofyO2sERX73y8uvuX26SLodv0mZ+Zk18d8b3zhD+s=');
    $hashedSig2 = base64_decode('GJy4HzkRtCeZA0cJjdZJtGfovcdxgl/AERI20S4MY7c=');
    SignedRequest::validateSignature($hashedSig1, $hashedSig2);
  }

  public function testASignedRequestWillPassCsrfValidation()
  {
    SignedRequest::validateCsrf($this->payloadData, 'foo_state');
  }

  /**
   * @expectedException \Facebook\Exceptions\FacebookSDKException
   */
  public function testASignedRequestWithIncorrectCsrfDataWillThrowAnException()
  {
    SignedRequest::validateCsrf($this->payloadData, 'invalid_foo_state');
  }

  public function testARawSignedRequestCanBeValidatedAndDecoded()
  {
    $payload = SignedRequest::parse($this->rawSignedRequest, 'foo_state', $this->appSecret);

    $this->assertEquals($this->payloadData, $payload);
  }

  public function testARawSignedRequestCanBeInjectedIntoTheConstructorToInstantiateANewEntity()
  {
    $signedRequest = new SignedRequest($this->rawSignedRequest, 'foo_state', $this->appSecret);

    $rawSignedRequest = $signedRequest->getRawSignedRequest();
    $payloadData = $signedRequest->getPayload();
    $userId = $signedRequest->getUserId();
    $hasOAuthData = $signedRequest->hasOAuthData();

    $this->assertInstanceOf('\Facebook\Entities\SignedRequest', $signedRequest);
    $this->assertEquals($this->rawSignedRequest, $rawSignedRequest);
    $this->assertEquals($this->payloadData, $payloadData);
    $this->assertEquals(123, $userId);
    $this->assertTrue($hasOAuthData);
  }

}
