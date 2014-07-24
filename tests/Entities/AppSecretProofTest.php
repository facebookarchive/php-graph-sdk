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

use Facebook\Entities\AppSecretProof;

class AppSecretProofTest extends \PHPUnit_Framework_TestCase
{

  public function testAnAppSecretProofIsGeneratedAsExpected()
  {
    $appSecretProof = AppSecretProof::make('foo_access_token', 'foo_app_secret');

    $this->assertEquals('12f5dcbb7557d24b1d37fd180c45991c5999f325ece0af331c00a85d762f2b95', $appSecretProof);
  }

  public function testAnAppSecretProofEntityCanBeReturnedAsAString()
  {
    $appSecretProof = new AppSecretProof('foo_access_token', 'foo_app_secret');

    $this->assertEquals('12f5dcbb7557d24b1d37fd180c45991c5999f325ece0af331c00a85d762f2b95', (string) $appSecretProof);
  }

}
