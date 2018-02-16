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
namespace Facebook\Helpers;

/**
 * Class FacebookDisplayTypeHelper
 *
 * Non-instantiable display type enumeration
 *
 * @package Facebook
 */
abstract class FacebookDisplayTypeHelper
{
    const DISPLAY_TYPE_ASYNC = 'async';
    const DISPLAY_TYPE_IFRAME = 'iframe';
    const DISPLAY_TYPE_PAGE = 'page';
    const DISPLAY_TYPE_POPUP = 'popup';
    const DISPLAY_TYPE_TOUCH = 'touch';
    const DISPLAY_TYPE_WAP = 'wap';

    /**
     * Checks if the given display type is a valid one
     *
     * @param string $displayType The display type to be validated
     *
     * @return bool
     */
    public static function isValidDisplayType($displayType)
    {
        return in_array($displayType, self::getDisplayTypes());
    }

    /**
     * @return array
     */
    private static function getDisplayTypes()
    {
        return [
            self::DISPLAY_TYPE_ASYNC,
            self::DISPLAY_TYPE_IFRAME,
            self::DISPLAY_TYPE_PAGE,
            self::DISPLAY_TYPE_POPUP,
            self::DISPLAY_TYPE_TOUCH,
            self::DISPLAY_TYPE_WAP,
        ];
    }
}
