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
namespace Facebook\Url;

/**
 * Class FacebookUrlManipulator
 * @package Facebook
 */
class FacebookUrlManipulator
{

  /**
   * Remove params from a URL.
   *
   * @param string $url The URL to filter.
   * @param array $paramsToFilter The params to filter from the URL.
   *
   * @return string The URL with the params removed.
   */
  public static function removeParamsFromUrl($url, array $paramsToFilter)
  {
    $parts = parse_url($url);

    $query = '';
    if (isset($parts['query'])) {
      $params = [];
      parse_str($parts['query'], $params);

      // Remove query params
      foreach ($paramsToFilter as $paramName) {
        unset($params[$paramName]);
      }

      if (count($params) > 0) {
        $query = '?' . http_build_query($params, null, '&');
      }
    }

    $port = isset($parts['port']) ? ':' . $parts['port'] : '';
    $path = isset($parts['path']) ? $parts['path'] : '';
    $fragment = isset($parts['fragment']) ? '#' . $parts['fragment'] : '';

    return $parts['scheme'] . '://' . $parts['host'] . $port . $path . $query . $fragment;
  }

}
