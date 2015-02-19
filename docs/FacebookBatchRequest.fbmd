<card>
# FacebookBatchRequest for the Facebook SDK for PHP

Represents a batch request that will be sent to the Graph API.
</card>

<card>
## Facebook\FacebookBatchRequest {#overview}

You can instantiate a new `FacebookBatchRequest` entity directly by sending the arguments to the constructor.

~~~~
use Facebook\FacebookBatchRequest;

$request = new FacebookBatchRequest(
  Facebook\FacebookApp $app,
  array $requests,
  string|null $accessToken,
  string|null $graphVersion
);
~~~~

The `$requests` array is an array of [`Facebook\FacebookRequest`'s](/docs/php/FacebookRequest) to be sent as a batch request.

The `FacebookBatchRequest` entity does not actually make any calls to the Graph API, but instead just represents a batch request that can be sent to the Graph API later. The batch request can be sent by using [`Facebook\Facebook::sendBatchRequest()`](/docs/php/Facebook#send-batch-request) or [`Facebook\FacebookClient::sendBatchRequest()`](/docs/php/FacebookClient#send-batch-request).

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
~~~~
</card>

<card>
## Instance Methods {#instance-methods}

Since the `Facebook\FacebookBatchRequest` is extended from the [`Facebook\FacebookRequest`](/docs/php/FacebookRequest) entity, all the methods are inherited.

### add() {#add}
~~~~
public add(
  array|Facebook\FacebookBatchRequest $request,
  string|null $name
  )
~~~~
Adds a request to be sent in the batch request. The `$request` can be a single [`Facebook\FacebookRequest`](/docs/php/FacebookRequest) or an array of `Facebook\FacebookRequest`'s.

The `$name` argument is optional and is used to identify the request in the batch.
</card>

<card>
### getRequests() {#get-requests}
~~~~
public array getRequests()
~~~~
Returns the array of [`Facebook\FacebookRequest`'s](/docs/php/FacebookRequest) to be sent in the batch request.
</card>

<card>
## Array Access {#array-access}

Since `Facebook\FacebookBatchRequest` implements `\IteratorAggregate` and `\ArrayAccess`, the requests can be accessed via array syntax and can also be iterated over.

~~~~
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
~~~~
</card>
