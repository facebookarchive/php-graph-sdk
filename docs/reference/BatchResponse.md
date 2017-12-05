# FacebookBatchResponse for the Facebook SDK for PHP

Represents a batch response returned from the Graph API.

## Facebook\BatchResponse

After sending a batch request to the Graph API, the response will be returned in the form of a `Facebook\BatchResponse` entity.

Usage:

```php
$fb = new Facebook\Facebook(/* . . . */);
$requests = [
  $fb->request('GET', '/me'),
  $fb->request('POST', '/me/feed', [/* */]),
];

// Send the batch request to Graph
try {
  $batchResponse = $fb->sendBatchRequest($requests, '{access-token}');
} catch(Facebook\Exception\ResponseException $e) {
  // When Graph returns an error
  echo 'Graph returned an error: ' . $e->getMessage();
  exit;
} catch(Facebook\Exception\SDKException $e) {
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
// class Facebook\BatchResponse . . .
```

## Instance Methods

Since the `Facebook\BatchResponse` is extended from the [`Facebook\Response`](Response.md) entity, all the methods are inherited.

### getResponses()
```php
public array getResponses()
```
Returns the array of [`Facebook\Response`](Response.md) entities that were returned from Graph.

## Array Access

Since `Facebook\BatchResponse` implements `\IteratorAggregate` and `\ArrayAccess`, the responses can be accessed via array syntax and can also be iterated over.

```php
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
// class Facebook\Response . . .
```
