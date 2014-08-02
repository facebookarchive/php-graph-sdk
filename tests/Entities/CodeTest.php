<?php

use Mockery as m;
use Facebook\Entities\Code;
use Facebook\Tests\FacebookTestCase;

class CodeTest extends FacebookTestCase
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

    $clientMock = $this->getClientMock(
      '/oauth/access_token',
      'GET',
      [
        'client_id' => $this->fakeApp->getId(),
        'client_secret' => $this->fakeApp->getSecret(),
        'code' => $codeValue,
        'redirect_uri' => $redirectUri
      ],
      'access_token='.$accessTokenValue.'&expires='.$accessTokenExpires
    );

    $code = new Code($this->fakeApp, $codeValue);
    $accessToken = $code->getAccessToken($clientMock, $redirectUri);

    $this->assertInstanceOf('Facebook\Entities\AccessToken', $accessToken);
    $this->assertEquals($accessTokenValue, $accessToken->getValue());
  }

}
