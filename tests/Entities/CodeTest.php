<?php

use Mockery as m;
use Facebook\Entities\Code;
use Facebook\Entities\FacebookRequest;

class CodeTest extends \PHPUnit_Framework_TestCase
{
  protected $fakeApp;

  protected function setUp()
  {
    $this->fakeApp = m::mock('Facebook\Entities\FacebookApp', ['foo_app_id', 'foo_app_secret'])->makePartial();
  }

  public function testGetApp()
  {
    $code = new Code($this->fakeApp, 'foo_code');

    $this->assertSame($this->fakeApp, $code->getApp());
  }

  public function testGetValue()
  {
    $accessToken = new Code($this->fakeApp, 'foo_code');

    $this->assertEquals('foo_code', $accessToken->getValue());
  }

  public function testGetAccessToken()
  {
    $codeValue = 'foo_code';
    $redirectUri = 'http://localhost/';
    $accessTokenValue = 'foo_token';
    $accessTokenExpires = time() + (60 * 60 * 24);

    $clientMock = m::mock('Facebook\FacebookClient[handle]')
      ->shouldReceive('handle')
      ->once()
      ->with(m::on(function($request) use ($codeValue, $redirectUri) {
        if (!$request instanceof FacebookRequest) {
          return false;
        }

        if ('/oauth/access_token' !== $request->getEndpoint()) {
          return false;
        }

        if ('GET' !== $request->getMethod()) {
          return false;
        }

        $params = $request->getParameters();
        $expectedParams = array(
          'client_id' => $this->fakeApp->getId(),
          'client_secret' => $this->fakeApp->getSecret(),
          'code' => $codeValue,
          'redirect_uri' => $redirectUri,
        );
        if ($params !== $expectedParams) {
          return false;
        }

        return true;
      }))
      ->andReturnUsing(function($request) use ($accessTokenValue, $accessTokenExpires) {
        return m::mock('Facebook\Entities\FacebookResponse', [
          $request,
          'access_token='.$accessTokenValue.'&expires='.$accessTokenExpires
        ])->makePartial();
      })
      ->getMock()
      ->makePartial();

    $code = new Code($this->fakeApp, $codeValue);
    $accessToken = $code->getAccessToken($clientMock, $redirectUri);

    $this->assertInstanceOf('Facebook\Entities\AccessToken', $accessToken);
    $this->assertEquals($accessTokenValue, $accessToken->getValue());
  }

}
