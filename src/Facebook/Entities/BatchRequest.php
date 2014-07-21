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
namespace Facebook\Entities;

/**
 * Class BatchRequest
 * @package Facebook
 */
class BatchRequest extends Request
{

  /**
   * @var string  The name of this request so that it can be referenced
   *              from another request in the batch.
   */
  protected $batchRequestName;

  /**
   * Set the name of this request.
   *
   * @param string|null $name
   *
   * @return BatchRequest
   */
  public function setBatchRequestName($name = null)
  {
    $this->batchRequestName = $name;

    return $this;
  }

  /**
   * Return the name of this request.
   *
   * @return string|null
   */
  public function getBatchRequestName()
  {
    return $this->batchRequestName;
  }

  /**
   * Return the access token for this request.
   *
   * @return string
   */
  public function getAccessToken()
  {
    return $this->accessToken;
  }

  /**
   * Individual requests on a batch do not require an access token.
   */
  public function validateAccessToken()
  {
    return;
  }

  /**
   * Checks if an access token has been set directly for this entity.
   *
   * @return boolean
   */
  public function hasAccessToken()
  {
    return !empty($this->accessToken);
  }

}
