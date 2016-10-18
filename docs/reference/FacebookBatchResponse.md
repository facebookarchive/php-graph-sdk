# FacebookBatchResponse for the Facebook SDK for PHP

Represents a batch response returned from the Graph API.

## Facebook\FacebookBatchResponse

After sending a batch request to the Graph API, the response will be returned in the form of a `Facebook\FacebookBatchResponse` entity.

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
```

## Instance Methods

Since the `Facebook\FacebookBatchResponse` is extended from the [`Facebook\FacebookResponse`](FacebookResponse.md) entity, all the methods are inherited.

### getResponses()
```php
public array getResponses()
```
Returns the array of [`Facebook\FacebookResponse`](FacebookResponse.md) entities that were returned from Graph.

## Array Access

Since `Facebook\FacebookBatchResponse` implements `\IteratorAggregate` and `\ArrayAccess`, the responses can be accessed via array syntax and can also be iterated over.

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
// class Facebook\FacebookResponse . . .
```
