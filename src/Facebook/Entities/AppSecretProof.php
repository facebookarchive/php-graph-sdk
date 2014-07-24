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
 * Class AppSecretProof
 * @package Facebook
 */
class AppSecretProof
{

  /**
   * @var string The access token to use for this proof.
   */
  protected $accessToken;

  /**
   * @var string|null The app secret.
   */
  protected $appSecret;

  /**
   * @var string The app secret proof.
   */
  protected $appSecretProof;

  /**
   * Creates a new Request entity.
   *
   * @param string $accessToken
   * @param string $appSecret
   */
  public function __construct($accessToken, $appSecret)
  {
    $this->accessToken = $accessToken;
    $this->appSecret = $appSecret;
  }

  /**
   * Generate and return the app secret proof value for an access token.
   *
   * @param string $accessToken The access token as a string.
   * @param string $appSecret The app secret.
   *
   * @return string The app secret proof.
   */
  public static function make($accessToken, $appSecret)
  {
    return hash_hmac('sha256', $accessToken, $appSecret);
  }

  /**
   * Convert the entity to a string by creating an app secret proof.
   *
   * @return string The app secret proof.
   */
  public function __toString()
  {
    if ($this->appSecretProof) {
      return $this->appSecretProof;
    }

    return $this->appSecretProof = static::make($this->accessToken, $this->appSecret);
  }

}
