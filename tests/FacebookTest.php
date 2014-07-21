<?php

use Mockery as m;
use Facebook\Facebook;

class FacebookTest extends PHPUnit_Framework_TestCase
{
  public function setUp()
  {
    FacebookTestHelper::resetTestCredentials();
  }

  public function tearDown()
  {
    m::close();
  }

  public function testNewRequestFactoryReturnsAFacebookRequestObject()
  {
    $request = Facebook::newRequest('foo_token');
    $requestEntity = $request->getCurrentRequest();

    $this->assertInstanceOf('Facebook\Http\FacebookRequest', $request);
    $this->assertInstanceOf('Facebook\Entities\Request', $requestEntity);
    $this->assertEquals('foo_token', $requestEntity->getAccessToken());
  }

  public function testNewBatchRequestFactoryReturnsAFacebookBatchRequestObject()
  {
    $request = Facebook::newBatchRequest('foo_token');

    $this->assertInstanceOf('Facebook\Http\FacebookBatchRequest', $request);
  }

  /**
   * @group integration
   */
  public function testGetsTheLoggedInTestUsersProfile()
  {
    $testUserAccessToken = FacebookTestHelper::$testUserAccessToken;
    $request = Facebook::newRequest($testUserAccessToken);
    $graphObject = $request->get('/me');

    $this->assertInstanceOf('Facebook\GraphNodes\GraphObject', $graphObject);
    $this->assertNotNull($graphObject->getProperty('id'));
    $this->assertNotNull($graphObject->getProperty('name'));
  }

  /**
   * @group integration
   */
  public function testCanPostATestUserToGraphAndThenDeleteIt()
  {
    // Create a test user
    $params = [
      'name' => 'Foo User',
    ];

    $appAccessToken = Facebook::getAppAccessToken();
    $appId = FacebookTestCredentials::$appId;
    $request = Facebook::newRequest($appAccessToken);
    $graphObject = $request->post('/' . $appId . '/accounts/test-users', $params);

    $this->assertInstanceOf('Facebook\GraphNodes\GraphObject', $graphObject);
    $userId = $graphObject->getProperty('id');
    $this->assertNotNull($userId);

    // Delete test user
    $request = Facebook::newRequest($appAccessToken);
    $graphObject = $request->delete('/' . $userId);

    $this->assertInstanceOf('Facebook\GraphNodes\GraphObject', $graphObject);
    $this->assertTrue($graphObject['was_successful']);
  }

}
