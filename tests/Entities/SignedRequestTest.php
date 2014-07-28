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

use Mockery as m;
use Facebook\Entities\SignedRequest;

class SignedRequestTest extends \PHPUnit_Framework_TestCase
{
  protected $fakeApp;

  protected function setUp()
  {
    $this->fakeApp = m::mock('Facebook\Entities\FacebookApp', ['foo_app_id', 'foo_app_secret'])->makePartial();
  }

  /**
   * @expectedException Facebook\Exceptions\FacebookSDKException
   * @expectedExceptionMessage Malformed signed request.
   */
  public function testThatConstructorThrowAnExceptionOnInvalidFormat()
  {
    new SignedRequest($this->fakeApp, 'invalid_signed_request');
  }

  /**
   * @expectedException Facebook\Exceptions\FacebookSDKException
   * @expectedExceptionMessage Signed request contains malformed base64 encoding.
   */
  public function testThatConstructorThrowAnExceptionOnInvalidBase64InSig()
  {
    new SignedRequest($this->fakeApp, 'foo!.foo');
  }

  /**
   * @expectedException Facebook\Exceptions\FacebookSDKException
   * @expectedExceptionMessage Signed request has an invalid signature.
   */
  public function testThatConstructorThrowAnExceptionWhenSignatureDontMatch()
  {
    // Payload is {}
    // Secret is foo_app_secret
    new SignedRequest($this->fakeApp, 'fake_signature.e30=');
  }

  /**
   * @expectedException Facebook\Exceptions\FacebookSDKException
   * @expectedExceptionMessage Signed request contains malformed base64 encoding.
   */
  public function testThatConstructorThrowAnExceptionOnInvalidBase64InPayload()
  {
    new SignedRequest($this->fakeApp, 'X1hlZfmdY2S46nJmQcwy3EINPp9Wmb8O/KeKCP6d4Vs=.foo!');
  }

  /**
   * @expectedException Facebook\Exceptions\FacebookSDKException
   * @expectedExceptionMessage Signed request has malformed encoded payload data.
   */
  public function testThatConstructorThrowAnExceptionWhenPayloadNotAnArray()
  {
    // Payload is false
    // Secret is foo_app_secret
    new SignedRequest($this->fakeApp, '5DBJNwmH_yDs0U2hh_mFN0Ak9jeS9wOC0tTUVFjxL9M=.ZmFsc2U=');
  }

  /**
   * @expectedException Facebook\Exceptions\FacebookSDKException
   * @expectedExceptionMessage Signed request is using the wrong algorithm.
   */
  public function testThatConstructorThrowAnExceptionWhenPayloadAsNoAlgorithm()
  {
    // Payload is {"foo":"bar"}
    // Secret is foo_app_secret
    new SignedRequest($this->fakeApp, '5xd-RDCU-sElaRQZAneUOtjBkV9edbnIL1NjqJMoXpU=.eyJmb28iOiJiYXIifQ==');
  }

  /**
   * @expectedException Facebook\Exceptions\FacebookSDKException
   * @expectedExceptionMessage Signed request is using the wrong algorithm.
   */
  public function testThatConstructorThrowAnExceptionWhenPayloadAsWrongAlgorithm()
  {
    // Payload is {"algorithm":"FOO-ALGORITHM"}
    // Secret is foo_app_secret
    new SignedRequest($this->fakeApp, 'T6yWrksHFBsAi-cgJp2KLPqsiNSSexU3czlBiRaaeTs=.eyJhbGdvcml0aG0iOiJGT08tQUxHT1JJVEhNIn0=');
  }

  /**
   * @expectedException Facebook\Exceptions\FacebookSDKException
   * @expectedExceptionMessage Signed request did not pass CSRF validation.
   */
  public function testThatConstructorThrowAnExceptionWhenPayloadAsWrongState()
  {
    // Payload is {"algorithm":"HMAC-SHA256","state":"foo_state"}
    // Secret is foo_app_secret
    new SignedRequest(
      $this->fakeApp,
      'C2nM6RwvQ4Q5vEPrtKsdPdMjxi1b5U2N4C9sDqAuuU4=.eyJhbGdvcml0aG0iOiJITUFDLVNIQTI1NiIsInN0YXRlIjoiZm9vX3N0YXRlIn0=',
      'wrong_state'
    );
  }

  public function testARawSignedRequestCanBeInjectedIntoTheConstructorToInstantiateANewEntity()
  {
    $expectedPayloadData = array(
      'oauth_token' => 'foo_token',
      'algorithm' => 'HMAC-SHA256',
      'issued_at' => 321,
      'code' => 'foo_code',
      'state' => 'foo_state',
      'user_id' => 123,
      'foo' => 'bar',
    );

    $signedRequest = new SignedRequest(
      $this->fakeApp,
      'U0_O1MqqNKUt32633zAkdd2Ce-jGVgRgJeRauyx_zC8=.eyJvYXV0aF90b2tlbiI6ImZvb190b2tlbiIsImFsZ29yaXRobSI6IkhNQUMtU0hBMjU2IiwiaXNzdWVkX2F0IjozMjEsImNvZGUiOiJmb29fY29kZSIsInN0YXRlIjoiZm9vX3N0YXRlIiwidXNlcl9pZCI6MTIzLCJmb28iOiJiYXIifQ==',
      'foo_state'
    );

    $this->assertInstanceOf('\Facebook\Entities\SignedRequest', $signedRequest);
    $this->assertEquals($expectedPayloadData, $signedRequest->getPayload());
    $this->assertEquals(123, $signedRequest->getUserId());
  }

}
