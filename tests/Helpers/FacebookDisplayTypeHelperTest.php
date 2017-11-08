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

use Facebook\Helpers\FacebookDisplayTypeHelper;

class FacebookDisplayTypeHelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider displayTypeProvider
     *
     * @param string $displayType    The display type to be validated
     * @param bool   $expectedResult The expected validation result
     */
    public function testDisplayTypeValidation($displayType, $expectedResult)
    {
        $this->assertEquals($expectedResult, FacebookDisplayTypeHelper::isValidDisplayType($displayType));
    }
    
    /**
     * @return array
     */
    public function displayTypeProvider()
    {
        return [
            [FacebookDisplayTypeHelper::DISPLAY_TYPE_ASYNC, true],
            [FacebookDisplayTypeHelper::DISPLAY_TYPE_IFRAME, true],
            [FacebookDisplayTypeHelper::DISPLAY_TYPE_PAGE, true],
            [FacebookDisplayTypeHelper::DISPLAY_TYPE_POPUP, true],
            [FacebookDisplayTypeHelper::DISPLAY_TYPE_TOUCH, true],
            [FacebookDisplayTypeHelper::DISPLAY_TYPE_WAP, true],
            ['unknown', false],
        ];
    }
}
