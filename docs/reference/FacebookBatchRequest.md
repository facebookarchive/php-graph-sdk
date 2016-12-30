# FacebookBatchRequest for the Facebook SDK for PHP

Represents a batch request that will be sent to the Graph API.

## Facebook\FacebookBatchRequest

You can instantiate a new `FacebookBatchRequest` entity directly by sending the arguments to the constructor or
by using the [`Facebook\Facebook::newBatchRequest()`](Facebook.md#newBatchRequest) factory method.

```php
use Facebook\FacebookBatchRequest;

$request = new FacebookBatchRequest(
  Facebook\FacebookApp $app,
  array $requests,
  string|null $accessToken,
  string|null $graphVersion
);
```

The `$requests` array is an array of [`Facebook\FacebookRequest`'s](FacebookRequest.md) to be sent as a batch request.

The `FacebookBatchRequest` entity does not actually make any calls to the Graph API, but instead just represents a batch request that can be sent to the Graph API later. The batch request can be sent by using [`Facebook\Facebook::sendBatchRequest()`](Facebook.md#sendbatchrequest) or [`Facebook\FacebookClient::sendBatchRequest()`](FacebookClient.md#sendbatchrequest.md).

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
```

## Instance Methods

Since the `Facebook\FacebookBatchRequest` is extended from the [`Facebook\FacebookRequest`](FacebookRequest.md) entity, all the methods are inherited.

### add()
```php
public add(
    array|Facebook\FacebookBatchRequest $request,
    string|null $name
)
```
Adds a request to be sent in the batch request. The `$request` can be a single [`Facebook\FacebookRequest`](FacebookRequest.md) or an array of `Facebook\FacebookRequest`'s.

The `$name` argument is optional and is used to identify the request in the batch.

### getRequests()
```php
public array getRequests()
```
Returns the array of [`Facebook\FacebookRequest`'s](FacebookRequest.md) to be sent in the batch request.

## Array Access

Since `Facebook\FacebookBatchRequest` implements `\IteratorAggregate` and `\ArrayAccess`, the requests can be accessed via array syntax and can also be iterated over.

```php
$fb = new Facebook\Facebook(/* . . . */);
$requests = [
  'foo' => $fb->request('GET', '/me'),
  'bar' => $fb->request('POST', '/me/feed', [/* */]),
];
$batchRequest = new Facebook\FacebookBatchRequest($fb->getApp(), $requests, '{access-token}');

var_dump($batchRequest[0]);
/*
array(2) {
  'name' => string(3) "foo"
  'request' => class Facebook\FacebookRequest
  . . .
*/
```
