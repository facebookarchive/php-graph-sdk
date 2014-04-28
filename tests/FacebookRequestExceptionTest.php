<?php

use Facebook\FacebookRequestException;
use Facebook\FacebookAuthorizationException;
use Facebook\FacebookOtherException;
use Facebook\FacebookServerException;
use Facebook\FacebookPermissionException;
use Facebook\FacebookClientException;
use Facebook\FacebookThrottleException;
use Facebook\FacebookSession;

class FacebookRequestExceptionTest extends PHPUnit_Framework_TestCase
{

  public static function setUpBeforeClass()
  {
    FacebookTestHelper::setUpBeforeClass();
  }

  public function testAuthorizationExceptions()
  {
    $params = array(
      'error' => array(
        'code' => 100,
        'message' => 'errmsg',
        'error_subcode' => 0,
        'type' => 'exception'
      )
    );
    $json = json_encode($params);
    $exception = FacebookRequestException::create($json, $params, 401);
    $this->assertTrue($exception instanceof FacebookAuthorizationException);
    $this->assertEquals(100, $exception->getCode());
    $this->assertEquals(0, $exception->getSubErrorCode());
    $this->assertEquals('exception', $exception->getErrorType());
    $this->assertEquals('errmsg', $exception->getMessage());
    $this->assertEquals($json, $exception->getRawResponse());
    $this->assertEquals(401, $exception->getHttpStatusCode());

    $params['error']['code'] = 102;
    $json = json_encode($params);
    $exception = FacebookRequestException::create($json, $params, 401);
    $this->assertTrue($exception instanceof FacebookAuthorizationException);
    $this->assertEquals(102, $exception->getCode());

    $params['error']['code'] = 190;
    $json = json_encode($params);
    $exception = FacebookRequestException::create($json, $params, 401);
    $this->assertTrue($exception instanceof FacebookAuthorizationException);
    $this->assertEquals(190, $exception->getCode());
  }

  public function testServerExceptions()
  {
    $params = array(
      'error' => array(
        'code' => 1,
        'message' => 'errmsg',
        'error_subcode' => 0,
        'type' => 'exception'
      )
    );
    $json = json_encode($params);
    $exception = FacebookRequestException::create($json, $params, 500);
    $this->assertTrue($exception instanceof FacebookServerException);
    $this->assertEquals(1, $exception->getCode());
    $this->assertEquals(0, $exception->getSubErrorCode());
    $this->assertEquals('exception', $exception->getErrorType());
    $this->assertEquals('errmsg', $exception->getMessage());
    $this->assertEquals($json, $exception->getRawResponse());
    $this->assertEquals(500, $exception->getHttpStatusCode());

    $params['error']['code'] = 2;
    $json = json_encode($params);
    $exception = FacebookRequestException::create($json, $params, 401);
    $this->assertTrue($exception instanceof FacebookServerException);
    $this->assertEquals(2, $exception->getCode());
  }

  public function testThrottleExceptions()
  {
    $params = array(
      'error' => array(
        'code' => 4,
        'message' => 'errmsg',
        'error_subcode' => 0,
        'type' => 'exception'
      )
    );
    $json = json_encode($params);
    $exception = FacebookRequestException::create($json, $params, 401);
    $this->assertTrue($exception instanceof FacebookThrottleException);
    $this->assertEquals(4, $exception->getCode());
    $this->assertEquals(0, $exception->getSubErrorCode());
    $this->assertEquals('exception', $exception->getErrorType());
    $this->assertEquals('errmsg', $exception->getMessage());
    $this->assertEquals($json, $exception->getRawResponse());
    $this->assertEquals(401, $exception->getHttpStatusCode());

    $params['error']['code'] = 17;
    $json = json_encode($params);
    $exception = FacebookRequestException::create($json, $params, 401);
    $this->assertTrue($exception instanceof FacebookThrottleException);
    $this->assertEquals(17, $exception->getCode());

    $params['error']['code'] = 341;
    $json = json_encode($params);
    $exception = FacebookRequestException::create($json, $params, 401);
    $this->assertTrue($exception instanceof FacebookThrottleException);
    $this->assertEquals(341, $exception->getCode());
  }

  public function testPermissionExceptions()
  {
    $params = array(
      'error' => array(
        'code' => 10,
        'message' => 'errmsg',
        'error_subcode' => 0,
        'type' => 'exception'
      )
    );
    $json = json_encode($params);
    $exception = FacebookRequestException::create($json, $params, 401);
    $this->assertTrue($exception instanceof FacebookPermissionException);
    $this->assertEquals(10, $exception->getCode());
    $this->assertEquals(0, $exception->getSubErrorCode());
    $this->assertEquals('exception', $exception->getErrorType());
    $this->assertEquals('errmsg', $exception->getMessage());
    $this->assertEquals($json, $exception->getRawResponse());
    $this->assertEquals(401, $exception->getHttpStatusCode());

    $params['error']['code'] = 200;
    $json = json_encode($params);
    $exception = FacebookRequestException::create($json, $params, 401);
    $this->assertTrue($exception instanceof FacebookPermissionException);
    $this->assertEquals(200, $exception->getCode());

    $params['error']['code'] = 250;
    $json = json_encode($params);
    $exception = FacebookRequestException::create($json, $params, 401);
    $this->assertTrue($exception instanceof FacebookPermissionException);
    $this->assertEquals(250, $exception->getCode());

    $params['error']['code'] = 299;
    $json = json_encode($params);
    $exception = FacebookRequestException::create($json, $params, 401);
    $this->assertTrue($exception instanceof FacebookPermissionException);
    $this->assertEquals(299, $exception->getCode());
  }

  public function testClientExceptions()
  {
    $params = array(
      'error' => array(
        'code' => 506,
        'message' => 'errmsg',
        'error_subcode' => 0,
        'type' => 'exception'
      )
    );
    $json = json_encode($params);
    $exception = FacebookRequestException::create($json, $params, 401);
    $this->assertTrue($exception instanceof FacebookClientException);
    $this->assertEquals(506, $exception->getCode());
    $this->assertEquals(0, $exception->getSubErrorCode());
    $this->assertEquals('exception', $exception->getErrorType());
    $this->assertEquals('errmsg', $exception->getMessage());
    $this->assertEquals($json, $exception->getRawResponse());
    $this->assertEquals(401, $exception->getHttpStatusCode());
  }

  public function testOtherException()
  {
    $params = array(
      'error' => array(
        'code' => 42,
        'message' => 'ship love',
        'error_subcode' => 0,
        'type' => 'feature'
      )
    );
    $json = json_encode($params);
    $exception = FacebookRequestException::create($json, $params, 200);
    $this->assertTrue($exception instanceof FacebookOtherException);
    $this->assertEquals(42, $exception->getCode());
    $this->assertEquals(0, $exception->getSubErrorCode());
    $this->assertEquals('feature', $exception->getErrorType());
    $this->assertEquals('ship love', $exception->getMessage());
    $this->assertEquals($json, $exception->getRawResponse());
    $this->assertEquals(200, $exception->getHttpStatusCode());
  }

  public function testValidateThrowsException()
  {
    $bogusSession = new FacebookSession('invalid-token');
    $this->setExpectedException(
      'Facebook\\FacebookSDKException', 'Session has expired'
    );
    $bogusSession->validate();
  }

  public function testInvalidCredentialsException()
  {
    $bogusSession = new FacebookSession('invalid-token');
    $this->setExpectedException(
      'Facebook\\FacebookAuthorizationException', 'Invalid OAuth access token'
    );
    $bogusSession->validate('invalid-app-id', 'invalid-app-secret');
  }

}