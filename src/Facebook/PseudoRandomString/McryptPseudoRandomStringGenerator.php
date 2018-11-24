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
namespace Facebook\PseudoRandomString;

use Facebook\Exceptions\FacebookSDKException;

class McryptPseudoRandomStringGenerator implements PseudoRandomStringGeneratorInterface
{
    use PseudoRandomStringGeneratorTrait;

    /**
     * @const string The error message when generating the string fails.
     */
    const ERROR_MESSAGE = 'Unable to generate a cryptographically secure pseudo-random string from mcrypt_create_iv(). ';

    /**
     * @throws FacebookSDKException
     */
    public function __construct()
    {
        if (version_compare( PHP_VERSION, '7.1', '>=' )) {
            if (!function_exists('random_bytes')) {
                throw new FacebookSDKException(
                    static::ERROR_MESSAGE .
                    'The function random_bytes() does not exist.'
                );
            }   
        } else {
            if (!function_exists('mcrypt_create_iv')) {
                throw new FacebookSDKException(
                    static::ERROR_MESSAGE .
                    'The function mcrypt_create_iv() does not exist.'
                );
            } 
        }
    }

    /**
     * @inheritdoc
     */
    public function getPseudoRandomString($length)
    {
        $this->validateLength($length);

        if (version_compare( PHP_VERSION, '7.1', '>=' )) {
            $binaryString = random_bytes($length);
        } else {
            $binaryString = mcrypt_create_iv($length, MCRYPT_DEV_URANDOM);
        }

        if ($binaryString === false) {
            throw new FacebookSDKException(
                static::ERROR_MESSAGE .
                'mcrypt_create_iv() or random_bytes() returned an error.'
            );
        }

        return $this->binToHex($binaryString, $length);
    }
}
