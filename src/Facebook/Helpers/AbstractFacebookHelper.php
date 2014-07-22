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
namespace Facebook\Helpers;

use Facebook\FacebookClient;
use Facebook\Entities\FacebookApp;
use Facebook\Entities\AccessToken;
use Facebook\HttpClients\FacebookHttpClientInterface;

/**
 * Class AbstractFacebookHelper
 * @package Facebook
 */
abstract class AbstractFacebookHelper
{
  /**
   * @var FacebookClient
   */
  protected $client;

  /**
   * @var FacebookApp $app
   */
  protected $app;

  /**
   * Constructs a helper
   *
   * @param FacebookClient $client
   * @param FacebookApp $app
   */
  public function __construct(FacebookClient $client, FacebookApp $app)
  {
    $this->client = $client;
    $this->app = $app;
  }

  /**
   * @param string $appId
   * @param string $appSecret
   * @param FacebookHttpClientInterface $httpClient
   * @param bool $useSecretProof
   * @param bool $useBeta
   *
   * @return AbstractFacebookHelper
   */
  public static function create($appId, $appSecret, FacebookHttpClientInterface $httpClient = null, $useSecretProof = true, $useBeta = false)
  {
    return new static(
      new FacebookClient($httpClient, $useSecretProof, $useBeta),
      new FacebookApp($appId, $appSecret)
    );
  }

  /**
   * @return AccessToken
   */
  abstract public function getAccessToken();

  /**
   * @return FacebookClient
   */
  final public function getClient()
  {
    return $this->client;
  }

  /**
   * @return FacebookApp
   */
  final public function getApp()
  {
    return $this->app;
  }

}
