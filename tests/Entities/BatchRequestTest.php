<?php

use Facebook\Facebook;
use Facebook\Entities\BatchRequest;

class BatchRequestTest extends PHPUnit_Framework_TestCase
{

  public function testAMissingAccessTokenWillNotFallBackToDefault()
  {
    Facebook::setDefaultAccessToken('foo_access_token');

    $request = new BatchRequest();

    $accessToken = $request->getAccessToken();

    $this->assertEquals(null, $accessToken);
  }

  public function testAMissingAccessTokenWillNotThrow()
  {
    $request = new BatchRequest();

    $request->validateAccessToken();
  }

}
