<card>
# FacebookBatchResponse for the Facebook SDK for PHP

Represents a batch response returned from the Graph API.
</card>

<card>
## Facebook\FacebookBatchResponse {#overview}

After sending a batch request to the Graph API, the response will be returned in the form of a `Facebook\FacebookBatchResponse` entity.

Usage:

~~~~
$fb = new Facebook\Facebook(/* . . . */);
$requests = [
  $fb->request('GET', '/me'),
  $fb->request('POST', '/me/feed', [/* */]),
];

// Send the batch request to Graph
try {
  $batchResponse = $fb->sendBatchRequest($requests, '{access-token}');
} catch(Facebook\Exceptions\FacebookResponseException $e) {
  // When Graph returns an error
  echo 'Graph returned an error: ' . $e->getMessage();
  exit;
} catch(Facebook\Exceptions\FacebookSDKException $e) {
  // When validation fails or other local issues
  echo 'Facebook SDK returned an error: ' . $e->getMessage();
  exit;
}

foreach ($batchResponse as $key => $response) {
  if ($response->isError()) {
    $error = $response->getThrownException();
    echo $key . ' error: ' . $error->getMessage();
  } else {
    // Success
  }
}

var_dump($batchResponse);
// class Facebook\FacebookBatchResponse . . .
~~~~
</card>

<card>
## Instance Methods {#instance-methods}

Since the `Facebook\FacebookBatchResponse` is extended from the [`Facebook\FacebookResponse`](/docs/php/FacebookResponse) entity, all the methods are inherited.

### getResponses() {#get-responses}
~~~~
public array getResponses()
~~~~
Returns the array of [`Facebook\FacebookResponse`](/docs/php/FacebookResponse) entities that were returned from Graph.
</card>

<card>
## Array Access {#array-access}

Since `Facebook\FacebookBatchResponse` implements `\IteratorAggregate` and `\ArrayAccess`, the responses can be accessed via array syntax and can also be iterated over.

~~~~
$requests = [
  'foo' => $fb->request('GET', '/me'),
  'bar' => $fb->request('POST', '/me/feed', [/* */]),
];
$batchResponse = $fb->sendBatchRequest($requests);

foreach ($batchResponse as $key => $response) {
  if ($response->isError()) {
    $error = $response->getThrownException();
    echo $key . ' error: ' . $error->getMessage();
  } else {
    // Success
  }
}

var_dump($batchResponse['foo']);
// class Facebook\FacebookResponse . . .
~~~~
</card>
