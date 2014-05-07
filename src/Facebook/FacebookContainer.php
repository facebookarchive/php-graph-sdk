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
namespace Facebook;

/**
 * Class FacebookContainer
 * @package Facebook
 */
class FacebookContainer
{

  /**
   * @var FacebookPersistable Persistent data handler
   */
  private static $persistentDataHandler;

  /**
   * setPersistentDataHandler - Returns an instance of the persistent
   * data handler
   *
   * @param FacebookPersistable
   */
  public static function setPersistentDataHandler(FacebookPersistable $handler)
  {
    static::$persistentDataHandler = $handler;
  }

  /**
   * getPersistentDataHandler - Returns an instance of the persistent
   * data handler
   *
   * @return FacebookPersistable
   */
  public static function getPersistentDataHandler()
  {
    if (isset(static::$persistentDataHandler)) return static::$persistentDataHandler;

    return static::$persistentDataHandler = new FacebookPersistentDataHandler();
  }

}
