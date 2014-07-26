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

use Facebook\Entities\FacebookRequest;

/**
 * Class FacebookBatchedRequest
 * @package Facebook
 */
class FacebookBatchedRequest extends FacebookRequest
{
  /**
   * @var string|null
   */
  protected $name;

  /**
   * @var string
   */
  protected $dependsOn;

  /**
   * @var bool
   */
  protected $omitResponseOnSuccess;

  /**
   * Creates a new FacebookBatchedRequest entity.
   *
   * @param FacebookRequest $request
   * @param string $name
   * @param string $dependsOn
   * @param bool $omitResponseOnSuccess
   */
  public function __construct(
    FacebookRequest $request,
    $name = '',
    $dependsOn = '',
    $omitResponseOnSuccess = true
  )
  {
    parent::__construct(
      $request->getEndpoint(),
      $request->getMethod(),
      $request->getParameters(),
      $request->getAccessToken(),
      $request->getETag()
    );

    $this->name = $name;
    $this->dependsOn = $dependsOn;
    $this->omitResponseOnSuccess = (bool)$omitResponseOnSuccess;
  }

  /**
   * @return string
   */
  public function getName()
  {
    return $this->name;
  }

  /**
   * @return string
   */
  public function getDependsOn()
  {
    return $this->dependsOn;
  }

  /**
   * @return bool
   */
  public function isOmitResponseOnSuccess()
  {
    return $this->omitResponseOnSuccess;
  }

}