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
 * Interface FacebookPersistable
 * @package Facebook
 */
interface FacebookPersistable
{

  /**
   * setPersistentData - Stores the given key-value pair, so that future
   * calls to getPersistentData() for a given key return the related value.
   *
   * @param string $key   The name of the value we want to store
   * @param mixed $value  The data we want to store
   * @return void
   */
  public function setPersistentData($key, $value);

  /**
   * getPersistentData - Get the stored value for a given key which was
   * set with setPersistentData().
   *
   * @param string $key     The name of the value we want to retrieve
   * @param mixed $default  The default value that should be returned if
   *                        the desired key does not exist
   * @return mixed
   */
  public function getPersistentData($key, $default = null);

}
