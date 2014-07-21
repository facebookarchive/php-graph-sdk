<?php

use Facebook\Entities\BatchResponse;

class BatchResponseTest extends PHPUnit_Framework_TestCase
{

  public function testASuccessfulJsonBatchResponseWillBeDecoded()
  {
    $graphResponseJson = '[';
    // Single Graph object.
    $graphResponseJson .= '{"code":200,"headers":[{"name":"Connection","value":"close"},{"name":"Last-Modified","value":"2013-12-24T00:34:20+0000"},{"name":"Facebook-API-Version","value":"v2.0"},{"name":"ETag","value":"\"fooTag\""},{"name":"Content-Type","value":"text\/javascript; charset=UTF-8"},{"name":"Pragma","value":"no-cache"},{"name":"Access-Control-Allow-Origin","value":"*"},{"name":"Cache-Control","value":"private, no-cache, no-store, must-revalidate"},{"name":"Expires","value":"Sat, 01 Jan 2000 00:00:00 GMT"}],"body":"{\"id\":\"123\",\"name\":\"Foo McBar\",\"updated_time\":\"2013-12-24T00:34:20+0000\",\"verified\":true}"}';
    // Paginated list of Graph objects.
    $graphResponseJson .= ',{"code":200,"headers":[{"name":"Connection","value":"close"},{"name":"Facebook-API-Version","value":"v1.0"},{"name":"ETag","value":"\"barTag\""},{"name":"Content-Type","value":"text\/javascript; charset=UTF-8"},{"name":"Pragma","value":"no-cache"},{"name":"Access-Control-Allow-Origin","value":"*"},{"name":"Cache-Control","value":"private, no-cache, no-store, must-revalidate"},{"name":"Expires","value":"Sat, 01 Jan 2000 00:00:00 GMT"}],"body":"{\"data\":[{\"id\":\"1337\",\"story\":\"Foo story.\"},{\"id\":\"1338\",\"story\":\"Bar story.\"}],\"paging\":{\"previous\":\"previous_url\",\"next\":\"next_url\"}}"}';
    // Endpoint not found.
    $graphResponseJson .= ',{"code":404,"headers":[{"name":"Connection","value":"close"},{"name":"WWW-Authenticate","value":"OAuth \"Facebook Platform\" \"not_found\" \"(#803) Cannot query users by their username (foo)\""},{"name":"Facebook-API-Version","value":"v2.0"},{"name":"Content-Type","value":"text\/javascript; charset=UTF-8"},{"name":"Pragma","value":"no-cache"},{"name":"Access-Control-Allow-Origin","value":"*"},{"name":"Cache-Control","value":"no-store"},{"name":"Expires","value":"Sat, 01 Jan 2000 00:00:00 GMT"}],"body":"{\"error\":{\"message\":\"(#803) Cannot query users by their username (foo)\",\"type\":\"OAuthException\",\"code\":803}}"}';
    // Invalid access token.
    $graphResponseJson .= ',{"code":400,"headers":[{"name":"Connection","value":"close"},{"name":"Expires","value":"Sat, 01 Jan 2000 00:00:00 GMT"},{"name":"Cache-Control","value":"no-store"},{"name":"Access-Control-Allow-Origin","value":"*"},{"name":"Pragma","value":"no-cache"},{"name":"Content-Type","value":"text\/javascript; charset=UTF-8"},{"name":"WWW-Authenticate","value":"OAuth \"Facebook Platform\" \"invalid_token\" \"Invalid OAuth access token.\""}],"body":"{\"error\":{\"message\":\"Invalid OAuth access token.\",\"type\":\"OAuthException\",\"code\":190}}"}';
    // After POST operation.
    $graphResponseJson .= ',{"code":200,"headers":[{"name":"Connection","value":"close"},{"name":"Expires","value":"Sat, 01 Jan 2000 00:00:00 GMT"},{"name":"Cache-Control","value":"private, no-cache, no-store, must-revalidate"},{"name":"Access-Control-Allow-Origin","value":"*"},{"name":"Pragma","value":"no-cache"},{"name":"Content-Type","value":"text\/javascript; charset=UTF-8"},{"name":"Facebook-API-Version","value":"v2.0"}],"body":"{\"id\":\"123_1337\"}"}';
    // After DELETE operation.
    $graphResponseJson .= ',{"code":200,"headers":[{"name":"Connection","value":"close"},{"name":"Expires","value":"Sat, 01 Jan 2000 00:00:00 GMT"},{"name":"Cache-Control","value":"private, no-cache, no-store, must-revalidate"},{"name":"Access-Control-Allow-Origin","value":"*"},{"name":"Pragma","value":"no-cache"},{"name":"Content-Type","value":"text\/javascript; charset=UTF-8"},{"name":"Facebook-API-Version","value":"v2.0"}],"body":"true"}';
    $graphResponseJson .= ']';
    $batchResponse = new BatchResponse(200, [], $graphResponseJson);

    $decodedResponses = $batchResponse->getResponses();

    // Single Graph object.
    $this->assertFalse($decodedResponses[0]->isError(), 'Did not expect Response to return an error for single Graph object.');
    $this->assertInstanceOf('Facebook\GraphNodes\GraphObject', $decodedResponses[0]->getCollection());
    // Paginated list of Graph objects.
    $this->assertFalse($decodedResponses[1]->isError(), 'Did not expect Response to return an error for paginated list of Graph objects.');
    $this->assertInstanceOf('Facebook\GraphNodes\GraphList', $decodedResponses[1]->getCollection());
    // Endpoint not found.
    $this->assertTrue($decodedResponses[2]->isError(), 'Expected Response to return an error for endpoint not found.');
    $this->assertInstanceOf('Facebook\Exceptions\FacebookResponseException', $decodedResponses[2]->getThrownException());
    // Invalid access token.
    $this->assertTrue($decodedResponses[3]->isError(), 'Expected Response to return an error for invalid access token.');
    $this->assertInstanceOf('Facebook\Exceptions\FacebookResponseException', $decodedResponses[3]->getThrownException());
    // After POST operation.
    $this->assertFalse($decodedResponses[4]->isError(), 'Did not expect Response to return an error for POST operation.');
    $this->assertInstanceOf('Facebook\GraphNodes\GraphObject', $decodedResponses[4]->getCollection());
    // After DELETE operation.
    $this->assertFalse($decodedResponses[5]->isError(), 'Did not expect Response to return an error for DELETE operation.');
    $this->assertInstanceOf('Facebook\GraphNodes\GraphObject', $decodedResponses[5]->getCollection());
  }

  public function testABatchResponseCanBeIteratedOver()
  {
    $graphResponseJson = '[';
    $graphResponseJson .= '{"code":200,"headers":[],"body":"{\"foo\":\"bar\"}"}';
    $graphResponseJson .= ',{"code":200,"headers":[],"body":"{\"foo\":\"bar\"}"}';
    $graphResponseJson .= ',{"code":200,"headers":[],"body":"{\"foo\":\"bar\"}"}';
    $graphResponseJson .= ']';
    $batchResponse = new BatchResponse(200, [], $graphResponseJson);

    $this->assertInstanceOf('IteratorAggregate', $batchResponse);

    foreach ($batchResponse as $responseEntity) {
      $this->assertInstanceOf('Facebook\Entities\Response', $responseEntity);
    }
  }

}
