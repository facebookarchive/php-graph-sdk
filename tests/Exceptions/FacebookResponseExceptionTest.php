<?php

use Mockery as m;
use Facebook\Exceptions\FacebookResponseException;

class FacebookResponseExceptionTest extends PHPUnit_Framework_TestCase
{

  public function tearDown()
  {
    m::close();
  }

  public function makeResponseMock(array $params)
  {
    $responseMock = m::mock('Facebook\Entities\Response');
    $responseMock
      ->shouldReceive('getDecodedBody')
      ->times(3)
      ->andReturn($params);

    return $responseMock;
  }

  public function testAuthorizationExceptions()
  {
    $params = [
      'error' => [
        'code' => 100,
        'message' => 'errmsg',
        'error_subcode' => 0,
        'type' => 'exception'
      ],
    ];
    $json = json_encode($params);

    $responseEntityMock = m::mock('Facebook\Entities\Response');
    $responseEntityMock
      ->shouldReceive('getDecodedBody')
      ->times(5)
      ->andReturn($params);
    $responseEntityMock
      ->shouldReceive('getBody')
      ->once()
      ->andReturn($json);

    $responseEntityMock
      ->shouldReceive('getHttpStatusCode')
      ->once()
      ->andReturn(401);

    $exception = FacebookResponseException::create($responseEntityMock);
    $this->assertInstanceOf('Facebook\Exceptions\FacebookAuthorizationException', $exception);
    $this->assertEquals(100, $exception->getCode());
    $this->assertEquals(0, $exception->getSubErrorCode());
    $this->assertEquals('exception', $exception->getErrorType());
    $this->assertEquals('errmsg', $exception->getMessage());
    $this->assertEquals($json, $exception->getRawResponse());
    $this->assertEquals(401, $exception->getHttpStatusCode());
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
    $responseEntityMock = $this->makeResponseMock($params);

    $exception = FacebookResponseException::create($responseEntityMock);
    $this->assertInstanceOf('Facebook\Exceptions\FacebookServerException', $exception);


    $params['error']['code'] = 2;
    $responseEntityMock = $this->makeResponseMock($params);

    $exception = FacebookResponseException::create($responseEntityMock);
    $this->assertInstanceOf('Facebook\Exceptions\FacebookServerException', $exception);
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
    $responseEntityMock = $this->makeResponseMock($params);
    $exception = FacebookResponseException::create($responseEntityMock);
    $this->assertInstanceOf('Facebook\Exceptions\FacebookThrottleException', $exception);

    $params['error']['code'] = 17;
    $responseEntityMock = $this->makeResponseMock($params);
    $exception = FacebookResponseException::create($responseEntityMock);
    $this->assertInstanceOf('Facebook\Exceptions\FacebookThrottleException', $exception);

    $params['error']['code'] = 341;
    $responseEntityMock = $this->makeResponseMock($params);
    $exception = FacebookResponseException::create($responseEntityMock);
    $this->assertInstanceOf('Facebook\Exceptions\FacebookThrottleException', $exception);
  }

  public function testUserIssueExceptions()
  {
    $params = array(
      'error' => array(
        'code' => 230,
        'message' => 'errmsg',
        'error_subcode' => 459,
        'type' => 'exception'
      )
    );
    $responseEntityMock = $this->makeResponseMock($params);
    $exception = FacebookResponseException::create($responseEntityMock);
    $this->assertInstanceOf('Facebook\Exceptions\FacebookAuthorizationException', $exception);

    $params['error']['error_subcode'] = 464;
    $responseEntityMock = $this->makeResponseMock($params);
    $exception = FacebookResponseException::create($responseEntityMock);
    $this->assertInstanceOf('Facebook\Exceptions\FacebookAuthorizationException', $exception);
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
    $responseEntityMock = $this->makeResponseMock($params);
    $exception = FacebookResponseException::create($responseEntityMock);
    $this->assertInstanceOf('Facebook\Exceptions\FacebookPermissionException', $exception);

    $params['error']['code'] = 200;
    $responseEntityMock = $this->makeResponseMock($params);
    $exception = FacebookResponseException::create($responseEntityMock);
    $this->assertInstanceOf('Facebook\Exceptions\FacebookPermissionException', $exception);

    $params['error']['code'] = 250;
    $responseEntityMock = $this->makeResponseMock($params);
    $exception = FacebookResponseException::create($responseEntityMock);
    $this->assertInstanceOf('Facebook\Exceptions\FacebookPermissionException', $exception);

    $params['error']['code'] = 299;
    $responseEntityMock = $this->makeResponseMock($params);
    $exception = FacebookResponseException::create($responseEntityMock);
    $this->assertInstanceOf('Facebook\Exceptions\FacebookPermissionException', $exception);
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
    $responseEntityMock = $this->makeResponseMock($params);
    $exception = FacebookResponseException::create($responseEntityMock);
    $this->assertInstanceOf('Facebook\Exceptions\FacebookClientException', $exception);
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
    $responseEntityMock = $this->makeResponseMock($params);
    $exception = FacebookResponseException::create($responseEntityMock);
    $this->assertInstanceOf('Facebook\Exceptions\FacebookOtherException', $exception);
  }

}
