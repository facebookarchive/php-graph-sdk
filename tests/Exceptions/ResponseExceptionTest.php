<?php

declare(strict_types=1);
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

namespace Facebook\Tests\Exceptions;

use Facebook\Application;
use Facebook\Exceptions\FacebookAuthenticationException;
use Facebook\Exceptions\FacebookAuthorizationException;
use Facebook\Exceptions\FacebookClientException;
use Facebook\Exceptions\FacebookOtherException;
use Facebook\Exceptions\FacebookServerException;
use Facebook\Exceptions\FacebookThrottleException;
use Facebook\Request;
use Facebook\Response;
use Facebook\Exceptions\FacebookResponseException;
use PHPUnit\Framework\TestCase;

/**
 * Class ResponseExceptionTest
 */
class ResponseExceptionTest extends TestCase
{

    /**
     * @var Request
     */
    protected Request $request;

    protected function setUp(): void
    {
        $this->request = new Request(new Application('123', 'foo'));
    }

    public function testAuthenticationExceptions()
    {
        $params = [
            'error' => [
                'code' => 100,
                'message' => 'errmsg',
                'error_subcode' => 0,
                'type' => 'exception'
            ],
        ];

        $response = new Response($this->request, json_encode($params), 401);
        $exception = FacebookResponseException::create($response);
        static::assertInstanceOf(FacebookAuthenticationException::class, $exception->getPrevious());
        static::assertEquals(100, $exception->getCode());
        static::assertEquals(0, $exception->getSubErrorCode());
        static::assertEquals('exception', $exception->getErrorType());
        static::assertEquals('errmsg', $exception->getMessage());
        static::assertEquals(json_encode($params), $exception->getRawResponse());
        static::assertEquals(401, $exception->getHttpStatusCode());

        $params['error']['code'] = 102;
        $response = new Response($this->request, json_encode($params), 401);
        $exception = FacebookResponseException::create($response);
        static::assertInstanceOf(FacebookAuthenticationException::class, $exception->getPrevious());
        static::assertEquals(102, $exception->getCode());

        $params['error']['code'] = 190;
        $response = new Response($this->request, json_encode($params), 401);
        $exception = FacebookResponseException::create($response);
        static::assertInstanceOf(FacebookAuthenticationException::class, $exception->getPrevious());
        static::assertEquals(190, $exception->getCode());

        $params['error']['type'] = 'OAuthException';
        $params['error']['code'] = 0;
        $params['error']['error_subcode'] = 458;
        $response = new Response($this->request, json_encode($params), 401);
        $exception = FacebookResponseException::create($response);
        static::assertInstanceOf(FacebookAuthenticationException::class, $exception->getPrevious());
        static::assertEquals(458, $exception->getSubErrorCode());

        $params['error']['error_subcode'] = 460;
        $response = new Response($this->request, json_encode($params), 401);
        $exception = FacebookResponseException::create($response);
        static::assertInstanceOf(FacebookAuthenticationException::class, $exception->getPrevious());
        static::assertEquals(460, $exception->getSubErrorCode());

        $params['error']['error_subcode'] = 463;
        $response = new Response($this->request, json_encode($params), 401);
        $exception = FacebookResponseException::create($response);
        static::assertInstanceOf(FacebookAuthenticationException::class, $exception->getPrevious());
        static::assertEquals(463, $exception->getSubErrorCode());

        $params['error']['error_subcode'] = 467;
        $response = new Response($this->request, json_encode($params), 401);
        $exception = FacebookResponseException::create($response);
        static::assertInstanceOf(FacebookAuthenticationException::class, $exception->getPrevious());
        static::assertEquals(467, $exception->getSubErrorCode());

        $params['error']['error_subcode'] = 0;
        $response = new Response($this->request, json_encode($params), 401);
        $exception = FacebookResponseException::create($response);
        static::assertInstanceOf(FacebookAuthenticationException::class, $exception->getPrevious());
        static::assertEquals(0, $exception->getSubErrorCode());
    }

    public function testServerExceptions()
    {
        $params = [
            'error' => [
                'code' => 1,
                'message' => 'errmsg',
                'error_subcode' => 0,
                'type' => 'exception'
            ],
        ];

        $response = new Response($this->request, json_encode($params), 500);
        $exception = FacebookResponseException::create($response);
        static::assertInstanceOf(FacebookServerException::class, $exception->getPrevious());
        static::assertEquals(1, $exception->getCode());
        static::assertEquals(0, $exception->getSubErrorCode());
        static::assertEquals('exception', $exception->getErrorType());
        static::assertEquals('errmsg', $exception->getMessage());
        static::assertEquals(json_encode($params), $exception->getRawResponse());
        static::assertEquals(500, $exception->getHttpStatusCode());

        $params['error']['code'] = 2;
        $response = new Response($this->request, json_encode($params), 500);
        $exception = FacebookResponseException::create($response);
        static::assertInstanceOf(FacebookServerException::class, $exception->getPrevious());
        static::assertEquals(2, $exception->getCode());
    }

    public function testThrottleExceptions()
    {
        $params = [
            'error' => [
                'code' => 4,
                'message' => 'errmsg',
                'error_subcode' => 0,
                'type' => 'exception'
            ],
        ];
        $response = new Response($this->request, json_encode($params), 401);
        $exception = FacebookResponseException::create($response);
        static::assertInstanceOf(FacebookThrottleException::class, $exception->getPrevious());
        static::assertEquals(4, $exception->getCode());
        static::assertEquals(0, $exception->getSubErrorCode());
        static::assertEquals('exception', $exception->getErrorType());
        static::assertEquals('errmsg', $exception->getMessage());
        static::assertEquals(json_encode($params), $exception->getRawResponse());
        static::assertEquals(401, $exception->getHttpStatusCode());

        $params['error']['code'] = 17;
        $response = new Response($this->request, json_encode($params), 401);
        $exception = FacebookResponseException::create($response);
        static::assertInstanceOf(FacebookThrottleException::class, $exception->getPrevious());
        static::assertEquals(17, $exception->getCode());

        $params['error']['code'] = 341;
        $response = new Response($this->request, json_encode($params), 401);
        $exception = FacebookResponseException::create($response);
        static::assertInstanceOf(FacebookThrottleException::class, $exception->getPrevious());
        static::assertEquals(341, $exception->getCode());
    }

    public function testUserIssueExceptions()
    {
        $params = [
            'error' => [
                'code' => 230,
                'message' => 'errmsg',
                'error_subcode' => 459,
                'type' => 'exception'
            ],
        ];
        $response = new Response($this->request, json_encode($params), 401);
        $exception = FacebookResponseException::create($response);
        static::assertInstanceOf(FacebookAuthenticationException::class, $exception->getPrevious());
        static::assertEquals(230, $exception->getCode());
        static::assertEquals(459, $exception->getSubErrorCode());
        static::assertEquals('exception', $exception->getErrorType());
        static::assertEquals('errmsg', $exception->getMessage());
        static::assertEquals(json_encode($params), $exception->getRawResponse());
        static::assertEquals(401, $exception->getHttpStatusCode());

        $params['error']['error_subcode'] = 464;
        $response = new Response($this->request, json_encode($params), 401);
        $exception = FacebookResponseException::create($response);
        static::assertInstanceOf(FacebookAuthenticationException::class, $exception->getPrevious());
        static::assertEquals(464, $exception->getSubErrorCode());
    }

    public function testAuthorizationExceptions()
    {
        $params = [
            'error' => [
                'code' => 10,
                'message' => 'errmsg',
                'error_subcode' => 0,
                'type' => 'exception'
            ],
        ];
        $response = new Response($this->request, json_encode($params), 401);
        $exception = FacebookResponseException::create($response);
        static::assertInstanceOf(FacebookAuthorizationException::class, $exception->getPrevious());
        static::assertEquals(10, $exception->getCode());
        static::assertEquals(0, $exception->getSubErrorCode());
        static::assertEquals('exception', $exception->getErrorType());
        static::assertEquals('errmsg', $exception->getMessage());
        static::assertEquals(json_encode($params), $exception->getRawResponse());
        static::assertEquals(401, $exception->getHttpStatusCode());

        $params['error']['code'] = 200;
        $response = new Response($this->request, json_encode($params), 401);
        $exception = FacebookResponseException::create($response);
        static::assertInstanceOf(FacebookAuthorizationException::class, $exception->getPrevious());
        static::assertEquals(200, $exception->getCode());

        $params['error']['code'] = 250;
        $response = new Response($this->request, json_encode($params), 401);
        $exception = FacebookResponseException::create($response);
        static::assertInstanceOf(FacebookAuthorizationException::class, $exception->getPrevious());
        static::assertEquals(250, $exception->getCode());

        $params['error']['code'] = 299;
        $response = new Response($this->request, json_encode($params), 401);
        $exception = FacebookResponseException::create($response);
        static::assertInstanceOf(FacebookAuthorizationException::class, $exception->getPrevious());
        static::assertEquals(299, $exception->getCode());
    }

    public function testClientExceptions()
    {
        $params = [
            'error' => [
                'code' => 506,
                'message' => 'errmsg',
                'error_subcode' => 0,
                'type' => 'exception'
            ],
        ];
        $response = new Response($this->request, json_encode($params), 401);
        $exception = FacebookResponseException::create($response);
        static::assertInstanceOf(FacebookClientException::class, $exception->getPrevious());
        static::assertEquals(506, $exception->getCode());
        static::assertEquals(0, $exception->getSubErrorCode());
        static::assertEquals('exception', $exception->getErrorType());
        static::assertEquals('errmsg', $exception->getMessage());
        static::assertEquals(json_encode($params), $exception->getRawResponse());
        static::assertEquals(401, $exception->getHttpStatusCode());
    }

    public function testOtherException()
    {
        $params = [
            'error' => [
                'code' => 42,
                'message' => 'ship love',
                'error_subcode' => 0,
                'type' => 'feature'
            ],
        ];
        $response = new Response($this->request, json_encode($params), 200);
        $exception = FacebookResponseException::create($response);
        static::assertInstanceOf(FacebookOtherException::class, $exception->getPrevious());
        static::assertEquals(42, $exception->getCode());
        static::assertEquals(0, $exception->getSubErrorCode());
        static::assertEquals('feature', $exception->getErrorType());
        static::assertEquals('ship love', $exception->getMessage());
        static::assertEquals(json_encode($params), $exception->getRawResponse());
        static::assertEquals(200, $exception->getHttpStatusCode());
    }
}
