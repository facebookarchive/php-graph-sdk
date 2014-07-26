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
use Facebook\Entities\FacebookBatchedRequest;

class FacebookBatchedRequestTest extends \PHPUnit_Framework_TestCase
{
  protected $fakeRequest;

  protected function setUp()
  {
    $this->fakeRequest = m::mock('Facebook\Entities\FacebookRequest', ['/me'])->makePartial();
  }

  public function testConstructorDefaults()
  {
    $request = new FacebookBatchedRequest($this->fakeRequest);

    $this->assertEquals('', $request->getName());
    $this->assertEquals('', $request->getDependsOn());
    $this->assertTrue($request->isOmitResponseOnSuccess());
  }

  public function testGetName()
  {
    $request = new FacebookBatchedRequest($this->fakeRequest, 'req_name');

    $this->assertEquals('req_name', $request->getName());
  }

  public function testGetDependsOn()
  {
    $request = new FacebookBatchedRequest($this->fakeRequest, '', 'req_name');

    $this->assertEquals('req_name', $request->getDependsOn());
  }

  public function testGetOmitResponseOnSuccess()
  {
    $request = new FacebookBatchedRequest($this->fakeRequest, '', '',false);

    $this->assertFalse($request->isOmitResponseOnSuccess());
  }

}
