<card>
# Batch Request Example

This example covers sending a batch request with the Facebook SDK for PHP.
</card>

<card>
## Example {#example}

The following example assumes we have the following permissions granted from the user: `user_likes`, `user_events`, `user_photos`, `publish_actions`. The example makes use of [JSONPath to reference specific batch operations](https://developers.facebook.com/docs/graph-api/making-multiple-requests/#operations).

~~~~
$fb = new Facebook\Facebook([
  'app_id' => '{app-id}',
  'app_secret' => '{app-secret}',
  'default_graph_version' => 'v2.6',
  ]);

// Since all the requests will be sent on behalf of the same user,
// we'll set the default fallback access token here.
$fb->setDefaultAccessToken('user-access-token');

/**
 * Generate some requests and then send them in a batch request.
 */

// Get the name of the logged in user
$requestUserName = $fb->request('GET', '/me?fields=id,name');

// Get user likes
$requestUserLikes = $fb->request('GET', '/me/likes?fields=id,name&limit=1');

// Get user events
$requestUserEvents = $fb->request('GET', '/me/events?fields=id,name&limit=2');

// Post a status update with reference to the user's name
$message = 'My name is {result=user-profile:$.name}.' . "\n\n";
$message .= 'I like this page: {result=user-likes:$.data.0.name}.' . "\n\n";
$message .= 'My next 2 events are {result=user-events:$.data.*.name}.';
$statusUpdate = ['message' => $message];
$requestPostToFeed = $fb->request('POST', '/me/feed', $statusUpdate);

// Get user photos
$requestUserPhotos = $fb->request('GET', '/me/photos?fields=id,source,name&limit=2');

$batch = [
    'user-profile' => $requestUserName,
    'user-likes' => $requestUserLikes,
    'user-events' => $requestUserEvents,
    'post-to-feed' => $requestPostToFeed,
    'user-photos' => $requestUserPhotos,
    ];

echo '<h1>Make a batch request</h1>' . "\n\n";

try {
  $responses = $fb->sendBatchRequest($batch);
} catch(Facebook\Exceptions\FacebookResponseException $e) {
  // When Graph returns an error
  echo 'Graph returned an error: ' . $e->getMessage();
  exit;
} catch(Facebook\Exceptions\FacebookSDKException $e) {
  // When validation fails or other local issues
  echo 'Facebook SDK returned an error: ' . $e->getMessage();
  exit;
}

foreach ($responses as $key => $response) {
  if ($response->isError()) {
    $e = $response->getThrownException();
    echo '<p>Error! Facebook SDK Said: ' . $e->getMessage() . "\n\n";
    echo '<p>Graph Said: ' . "\n\n";
    var_dump($e->getResponse());
  } else {
    echo "<p>(" . $key . ") HTTP status code: " . $response->getHttpStatusCode() . "<br />\n";
    echo "Response: " . $response->getBody() . "</p>\n\n";
    echo "<hr />\n\n";
  }
}
~~~~

There five requests being made in this batch requests.

- Get the user's full `name` and `id`.
- Get one thing the user likes (which is a [Page node](https://developers.facebook.com/docs/graph-api/reference/page)).
- Get two events the user has been invited to (which are [Event nodes](https://developers.facebook.com/docs/graph-api/reference/event)).
- Compose a message using the data obtained from the 3 requests above and post it on the user's timeline.
- Get two photos from the user.

If the request was successful, the user should have a new status update similar to this:

~~~~
My name is Foo User.

I like this page: Facebook Developers.

My next 2 events are House Warming Party,Some Foo Event.
~~~~

It should also contain a response containing two photos from the user.

%FB(devsite:markdown-wiki:info-card {
  content: "The response object should return a `null` response for any request that was pointed to with JSONPath as is [the behaviour of the batch functionality of the Graph API](https://developers.facebook.com/docs/graph-api/making-multiple-requests/#operations).",
  type: 'warning',
})
</card>

<card>
## Multiple User Example {#multiple-user-example}

Since the requests sent in a batch are unrelated by default, we can make requests on behalf of multiple users and pages in the same batch request.

~~~~
$fb = new Facebook\Facebook([
  'app_id' => '{app-id}',
  'app_secret' => '{app-secret}',
  'default_graph_version' => 'v2.6',
  ]);

$batch = [
    $fb->request('GET', '/me?fields=id,name', 'user-access-token-one'),
    $fb->request('GET', '/me?fields=id,name', 'user-access-token-two'),
    $fb->request('GET', '/me?fields=id,name', 'page-access-token-one'),
    $fb->request('GET', '/me?fields=id,name', 'page-access-token-two'),
    ];

try {
  $responses = $fb->sendBatchRequest($batch);
} catch(Facebook\Exceptions\FacebookResponseException $e) {
  // When Graph returns an error
  echo 'Graph returned an error: ' . $e->getMessage();
  exit;
} catch(Facebook\Exceptions\FacebookSDKException $e) {
  // When validation fails or other local issues
  echo 'Facebook SDK returned an error: ' . $e->getMessage();
  exit;
}

foreach ($responses as $key => $response) {
  if ($response->isError()) {
    $e = $response->getThrownException();
    echo '<p>Error! Facebook SDK Said: ' . $e->getMessage() . "\n\n";
    echo '<p>Graph Said: ' . "\n\n";
    var_dump($e->getResponse());
  } else {
    echo "<p>(" . $key . ") HTTP status code: " . $response->getHttpStatusCode() . "<br />\n";
    echo "Response: " . $response->getBody() . "</p>\n\n";
    echo "<hr />\n\n";
  }
}
~~~~
</card>
