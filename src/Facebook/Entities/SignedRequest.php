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

use Facebook\FacebookClient;
use Facebook\Entities\FacebookApp;
use Facebook\Entities\Code;
use Facebook\Exceptions\FacebookSDKException;

/**
 * Class SignedRequest
 * @package Facebook
 */
class SignedRequest implements \ArrayAccess
{
  /**
   * @var FacebookApp
   */
  protected $app;

  /**
   * @var string
   */
  protected $value;

  /**
   * @var array
   */
  protected $payload;

  /**
   * Instantiate a new SignedRequest entity.
   *
   * @param FacebookApp $app
   * @param string $value The raw signed request.
   * @param string|null $state random string to prevent CSRF.
   */
  public function __construct(FacebookApp $app, $value, $state = null)
  {
    $this->app = $app;
    $this->value = $value;
    $this->payload = $this->parse($value, $state);
  }

  /**
   * Returns the parsed signed request data.
   *
   * @return array|null
   */
  public function getPayload()
  {
    return $this->payload;
  }

  /**
   * Returns a property from the signed request data if available.
   *
   * @param string $key
   * @param mixed|null $default
   *
   * @return mixed|null
   */
  public function get($key, $default = null)
  {
    if (isset($this->payload[$key])) {
      return $this->payload[$key];
    }
    return $default;
  }

  /**
   * Returns user_id from signed request data if available.
   *
   * @return string|null
   */
  public function getUserId()
  {
    return $this->get('user_id');
  }

  /**
   * getAccessToken - Returns a AccessToken for this signed request.
   *
   * @param FacebookClient|null $client
   *
   * @return AccessToken|null
   */
  public function getAccessToken(FacebookClient $client = null)
  {
    if ($this->get('oauth_token')) {
      return new AccessToken($this->app, $this->get('oauth_token'), $this->get('expires', 0));
    }

    if ($this->get('code')) {
      return (new Code($this->app, $this->get('code')))->getAccessToken($client, '');
    }
  }

  /**
   * Creates a signed request from an array of data.
   *
   * @param FacebookApp $app
   * @param array $payload
   *
   * @return string
   */
  public static function make(FacebookApp $app, array $payload)
  {
    $payload['algorithm'] = 'HMAC-SHA256';
    $payload['issued_at'] = time();
    $encodedPayload = static::base64UrlEncode(json_encode($payload));

    $hashedSig = static::hashSignature($encodedPayload, $app->getSecret());
    $encodedSig = static::base64UrlEncode($hashedSig);

    return new static($app, $encodedSig.'.'.$encodedPayload);
  }

  /**
   * Validates and decodes a signed request and returns
   * the payload as an array.
   *
   * @param string $value
   * @param string|null $state
   *
   * @return array
   */
  protected function parse($value, $state = null)
  {
    list($encodedSig, $encodedPayload) = $this->split($value);

    // Signature validation
    $sig = $this->decodeSignature($encodedSig);
    $hashedSig = static::hashSignature($encodedPayload, $this->app->getSecret());
    $this->validateSignature($hashedSig, $sig);

    // Payload validation
    $data = $this->decodePayload($encodedPayload);
    $this->validateAlgorithm($data);
    if ($state) {
      $this->validateCsrf($data, $state);
    }

    return $data;
  }

  /**
   * Validates the format of a signed request.
   *
   * @param string $value
   *
   * @throws FacebookSDKException
   */
  protected function validateFormat($value)
  {
    if (false === strpos($value, '.')) {
      throw new FacebookSDKException(
        'Malformed signed request.', 606
      );
    }
  }

  /**
   * Decodes a raw valid signed request.
   *
   * @param string $value
   *
   * @returns array
   */
  protected function split($value)
  {
    $this->validateFormat($value);

    return explode('.', $value, 2);
  }

  /**
   * Decodes the raw signature from a signed request.
   *
   * @param string $encodedSig
   *
   * @returns string
   *
   * @throws FacebookSDKException
   */
  protected function decodeSignature($encodedSig)
  {
    $sig = $this->base64UrlDecode($encodedSig);

    if (!$sig) {
      throw new FacebookSDKException(
        'Signed request has malformed encoded signature data.', 607
      );
    }

    return $sig;
  }

  /**
   * Decodes the raw payload from a signed request.
   *
   * @param string $encodedPayload
   *
   * @returns array
   *
   * @throws FacebookSDKException
   */
  protected function decodePayload($encodedPayload)
  {
    $payload = $this->base64UrlDecode($encodedPayload);

    if ($payload) {
      $payload = json_decode($payload, true);
    }

    if (!is_array($payload)) {
      throw new FacebookSDKException(
        'Signed request has malformed encoded payload data.', 607
      );
    }

    return $payload;
  }

  /**
   * Validates the algorithm used in a signed request.
   *
   * @param array $data
   *
   * @throws FacebookSDKException
   */
  protected function validateAlgorithm(array $data)
  {
    if (!isset($data['algorithm']) || $data['algorithm'] !== 'HMAC-SHA256') {
      throw new FacebookSDKException(
        'Signed request is using the wrong algorithm.', 605
      );
    }
  }

  /**
   * Hashes the signature used in a signed request.
   *
   * @param string $encodedData
   * @param string|null $appSecret
   *
   * @return string
   *
   * @throws FacebookSDKException
   */
  protected static function hashSignature($encodedData, $appSecret)
  {
    $hashedSig = hash_hmac(
      'sha256', $encodedData, $appSecret, $raw_output = true
    );

    if (!$hashedSig) {
      throw new FacebookSDKException(
        'Unable to hash signature from encoded payload data.', 602
      );
    }

    return $hashedSig;
  }

  /**
   * Validates the signature used in a signed request.
   *
   * @param string $hashedSig
   * @param string $sig
   *
   * @throws FacebookSDKException
   */
  protected function validateSignature($hashedSig, $sig)
  {
    if (mb_strlen($hashedSig) === mb_strlen($sig)) {
      $validate = 0;
      for ($i = 0; $i < mb_strlen($sig); $i++) {
        $validate |= ord($hashedSig[$i]) ^ ord($sig[$i]);
      }
      if ($validate === 0) {
        return;
      }
    }

    throw new FacebookSDKException(
      'Signed request has an invalid signature.', 602
    );
  }

  /**
   * Validates a signed request against CSRF.
   *
   * @param array $data
   * @param string $state
   *
   * @throws FacebookSDKException
   */
  protected function validateCsrf(array $data, $state)
  {
    if (!isset($data['state']) || $data['state'] !== $state) {
      throw new FacebookSDKException(
        'Signed request did not pass CSRF validation.', 604
      );
    }
  }

  /**
   * Base64 decoding which replaces characters:
   *   + instead of -
   *   / instead of _
   * @link http://en.wikipedia.org/wiki/Base64#URL_applications
   *
   * @param string $input base64 url encoded input
   *
   * @return string decoded string
   */
  protected function base64UrlDecode($input)
  {
    $urlDecodedBase64 = strtr($input, '-_', '+/');
    $this->validateBase64($urlDecodedBase64);

    return base64_decode($urlDecodedBase64);
  }

  /**
   * Base64 encoding which replaces characters:
   *   + instead of -
   *   / instead of _
   * @link http://en.wikipedia.org/wiki/Base64#URL_applications
   *
   * @param string $input string to encode
   *
   * @return string base64 url encoded input
   */
  protected function base64UrlEncode($input)
  {
    return strtr(base64_encode($input), '+/', '-_');
  }

  /**
   * Validates a base64 string.
   *
   * @param string $input base64 value to validate
   *
   * @throws FacebookSDKException
   */
  protected function validateBase64($input)
  {
    $pattern = '/^[a-zA-Z0-9\/\r\n+]*={0,2}$/';
    if (preg_match($pattern, $input)) {
      return;
    }

    throw new FacebookSDKException(
      'Signed request contains malformed base64 encoding.', 608
    );
  }

  public function offsetExists($offset)
  {
    return isset($this->payload[$offset]);
  }

  public function offsetGet($offset)
  {
    return $this->payload[$offset];
  }

  public function offsetSet($offset, $value)
  {
    throw new FacebookSDKException('You cannot set a payload value to a signed request');
  }

  public function offsetUnset($offset)
  {
    throw new FacebookSDKException('You cannot unset a payload value of a signed request');
  }

}
