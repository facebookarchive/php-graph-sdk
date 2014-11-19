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
namespace Facebook\Tests\Url;

use Facebook\Url\FacebookUrlManipulator;

class FacebookUrlManipulatorTest extends \PHPUnit_Framework_TestCase
{

  /**
   * @dataProvider provideUris
   */
  public function testParamsGetRemovedFromAUrl($dirtyUrl, $expectedCleanUrl)
  {
    $removeParams = [
      'state',
      'code',
      'error',
      'error_reason',
      'error_description',
      'error_code',
      ];
    $currentUri = FacebookUrlManipulator::removeParamsFromUrl($dirtyUrl, $removeParams);
    $this->assertEquals($expectedCleanUrl, $currentUri);
  }

  public function provideUris()
  {
    return [
      [
        'http://localhost/something?state=0000&foo=bar&code=abcd',
        'http://localhost/something?foo=bar',
      ],
      [
        'https://localhost/something?state=0000&foo=bar&code=abcd',
        'https://localhost/something?foo=bar',
      ],
      [
        'http://localhost/something?state=0000&foo=bar&error=abcd&error_reason=abcd&error_description=abcd&error_code=1',
        'http://localhost/something?foo=bar',
      ],
      [
        'https://localhost/something?state=0000&foo=bar&error=abcd&error_reason=abcd&error_description=abcd&error_code=1',
        'https://localhost/something?foo=bar',
      ],
      [
        'http://localhost/something?state=0000&foo=bar&error=abcd',
        'http://localhost/something?foo=bar',
      ],
      [
        'https://localhost/something?state=0000&foo=bar&error=abcd',
        'https://localhost/something?foo=bar',
      ],
      [
        'https://localhost:1337/something?state=0000&foo=bar&error=abcd',
        'https://localhost:1337/something?foo=bar',
      ],
      [
        'https://localhost:1337/something?state=0000&code=foo',
        'https://localhost:1337/something',
      ],
      [
        'https://localhost/something/?state=0000&code=foo&foo=bar',
        'https://localhost/something/?foo=bar',
      ],
      [
        'https://localhost/something/?state=0000&code=foo',
        'https://localhost/something/',
      ],
    ];
  }

}
