<?php

use Facebook\Entities\Response;

class ResponseTest extends PHPUnit_Framework_TestCase
{

  public function testAnEmptyResponseEntityCanInstantiate()
  {
    $response = new Response();

    $this->assertInstanceOf('Facebook\Entities\Response', $response);
  }

  public function testAnETagCanBeProperlyAccessed()
  {
    $response = new Response(200, ['ETag' => 'foo_tag']);

    $eTag = $response->getETag();

    $this->assertEquals('foo_tag', $eTag);
  }

  public function testAProperAppSecretProofCanBeGenerated()
  {
    $response = new Response(200, [], '', 'foo_token', 'foo_secret');

    $appSecretProof = $response->getAppSecretProof();

    $this->assertEquals('df4256903ba4e23636cc142117aa632133d75c642bd2a68955be1443bd14deb9', $appSecretProof);
  }

  public function testASuccessfulJsonResponseWillBeDecoded()
  {
    $graphResponseJson = '{"id":"123","name":"Foo"}';
    $response = new Response(200, [], $graphResponseJson);

    $decodedResponse = $response->getDecodedBody();
    $graphObject = $response->getCollection();

    $this->assertFalse($response->isError(), 'Did not expect Response to return an error.');
    $this->assertEquals([
      'id' => '123',
      'name' => 'Foo',
    ], $decodedResponse);
    $this->assertInstanceOf('Facebook\GraphNodes\GraphObject', $graphObject);
  }

  public function testASuccessfulUrlEncodedKeyValuePairResponseWillBeDecoded()
  {
    $graphResponseKeyValuePairs = 'id=123&name=Foo';
    $response = new Response(200, [], $graphResponseKeyValuePairs);

    $decodedResponse = $response->getDecodedBody();

    $this->assertFalse($response->isError(), 'Did not expect Response to return an error.');
    $this->assertEquals([
        'id' => '123',
        'name' => 'Foo',
      ], $decodedResponse);
  }

  public function testASuccessfulBooleanResponseWillBeDecoded()
  {
    $graphResponse = 'true';
    $response = new Response(200, [], $graphResponse);

    $decodedResponse = $response->getDecodedBody();

    $this->assertFalse($response->isError(), 'Did not expect Response to return an error.');
    $this->assertEquals(['was_successful' => true], $decodedResponse);
  }

  public function testErrorStatusCanBeCheckedWhenAnErrorResponseIsReturned()
  {
    $graphResponse = '{"error":{"message":"Foo error.","type":"OAuthException","code":190,"error_subcode":463}}';
    $response = new Response(200, [], $graphResponse);

    $exception = $response->getThrownException();

    $this->assertTrue($response->isError(), 'Expected Response to return an error.');
    $this->assertInstanceOf('Facebook\Exceptions\FacebookResponseException', $exception);
  }

}
