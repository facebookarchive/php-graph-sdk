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

    public function testGracefullyHandlesUrlAppending()
    {
        $params = [];
        $url = 'https://www.foo.com/';
        $processed_url = FacebookUrlManipulator::appendParamsToUrl($url, $params);
        $this->assertEquals('https://www.foo.com/', $processed_url);

        $params = [
            'access_token' => 'foo',
        ];
        $url = 'https://www.foo.com/';
        $processed_url = FacebookUrlManipulator::appendParamsToUrl($url, $params);
        $this->assertEquals('https://www.foo.com/?access_token=foo', $processed_url);

        $params = [
            'access_token' => 'foo',
            'bar' => 'baz',
        ];
        $url = 'https://www.foo.com/?foo=bar';
        $processed_url = FacebookUrlManipulator::appendParamsToUrl($url, $params);
        $this->assertEquals('https://www.foo.com/?access_token=foo&bar=baz&foo=bar', $processed_url);

        $params = [
            'access_token' => 'foo',
        ];
        $url = 'https://www.foo.com/?foo=bar&access_token=bar';
        $processed_url = FacebookUrlManipulator::appendParamsToUrl($url, $params);
        $this->assertEquals('https://www.foo.com/?access_token=bar&foo=bar', $processed_url);
    }

    public function testSlashesAreProperlyPrepended()
    {
        $slashTestOne = FacebookUrlManipulator::forceSlashPrefix('foo');
        $slashTestTwo = FacebookUrlManipulator::forceSlashPrefix('/foo');
        $slashTestThree = FacebookUrlManipulator::forceSlashPrefix('foo/bar');
        $slashTestFour = FacebookUrlManipulator::forceSlashPrefix('/foo/bar');
        $slashTestFive = FacebookUrlManipulator::forceSlashPrefix(null);
        $slashTestSix = FacebookUrlManipulator::forceSlashPrefix('');

        $this->assertEquals('/foo', $slashTestOne);
        $this->assertEquals('/foo', $slashTestTwo);
        $this->assertEquals('/foo/bar', $slashTestThree);
        $this->assertEquals('/foo/bar', $slashTestFour);
        $this->assertEquals(null, $slashTestFive);
        $this->assertEquals('', $slashTestSix);
    }

    public function testParamsCanBeReturnedAsArray()
    {
        $paramsOne = FacebookUrlManipulator::getParamsAsArray('/foo');
        $paramsTwo = FacebookUrlManipulator::getParamsAsArray('/foo?one=1&two=2');
        $paramsThree = FacebookUrlManipulator::getParamsAsArray('https://www.foo.com');
        $paramsFour = FacebookUrlManipulator::getParamsAsArray('https://www.foo.com/?');
        $paramsFive = FacebookUrlManipulator::getParamsAsArray('https://www.foo.com/?foo=bar');

        $this->assertEquals([], $paramsOne);
        $this->assertEquals(['one' => '1', 'two' => '2'], $paramsTwo);
        $this->assertEquals([], $paramsThree);
        $this->assertEquals([], $paramsFour);
        $this->assertEquals(['foo' => 'bar'], $paramsFive);
    }

    /**
     * @dataProvider provideMergableEndpoints
     */
    public function testParamsCanBeMergedOntoUrlProperly($urlOne, $urlTwo, $expected)
    {
        $result = FacebookUrlManipulator::mergeUrlParams($urlOne, $urlTwo);

        $this->assertEquals($result, $expected);
    }

    public function provideMergableEndpoints()
    {
        return [
            [
                'https://www.foo.com/?foo=ignore_foo&dance=fun',
                '/me?foo=keep_foo',
                '/me?dance=fun&foo=keep_foo',
            ],
            [
                'https://www.bar.com?',
                'https://foo.com?foo=bar',
                'https://foo.com?foo=bar',
            ],
            [
                'you',
                'me',
                'me',
            ],
            [
                '/1234?swing=fun',
                '/1337?bar=baz&west=coast',
                '/1337?bar=baz&swing=fun&west=coast',
            ],
        ];
    }

    public function testGraphUrlsCanBeTrimmed()
    {
        $fullGraphUrl = 'https://graph.facebook.com/';
        $baseGraphUrl = FacebookUrlManipulator::baseGraphUrlEndpoint($fullGraphUrl);
        $this->assertEquals('/', $baseGraphUrl);

        $fullGraphUrl = 'https://graph.facebook.com/v1.0/';
        $baseGraphUrl = FacebookUrlManipulator::baseGraphUrlEndpoint($fullGraphUrl);
        $this->assertEquals('/', $baseGraphUrl);

        $fullGraphUrl = 'https://graph.facebook.com/me';
        $baseGraphUrl = FacebookUrlManipulator::baseGraphUrlEndpoint($fullGraphUrl);
        $this->assertEquals('/me', $baseGraphUrl);

        $fullGraphUrl = 'https://graph.beta.facebook.com/me';
        $baseGraphUrl = FacebookUrlManipulator::baseGraphUrlEndpoint($fullGraphUrl);
        $this->assertEquals('/me', $baseGraphUrl);

        $fullGraphUrl = 'https://whatever-they-want.facebook.com/v2.1/me';
        $baseGraphUrl = FacebookUrlManipulator::baseGraphUrlEndpoint($fullGraphUrl);
        $this->assertEquals('/me', $baseGraphUrl);

        $fullGraphUrl = 'https://graph.facebook.com/v5.301/1233?foo=bar';
        $baseGraphUrl = FacebookUrlManipulator::baseGraphUrlEndpoint($fullGraphUrl);
        $this->assertEquals('/1233?foo=bar', $baseGraphUrl);
    }
}
