# Batch Request Example

This example covers sending a batch request with the Facebook SDK for PHP.

## Example

The following example assumes we have the following permissions granted from the user: `user_likes`, `user_events`, `user_photos`, `publish_actions`. The example makes use of [JSONPath to reference specific batch operations](https://developers.facebook.com/docs/graph-api/making-multiple-requests/#operations).

```php
<?php
$fb = new Facebook\Facebook([
    'app_id' => '{app-id}',
    'app_secret' => '{app-secret}',
    'default_graph_version' => 'v2.10',
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
} catch (Facebook\Exceptions\FacebookResponseException $e) {
    // When Graph returns an error
    echo 'Graph returned an error: ' . $e->getMessage();
    exit;
} catch (Facebook\Exceptions\FacebookSDKException $e) {
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

```

There five requests being made in this batch requests.

- Get the user's full `name` and `id`.
- Get one thing the user likes (which is a [Page node](https://developers.facebook.com/docs/graph-api/reference/page)).
- Get two events the user has been invited to (which are [Event nodes](https://developers.facebook.com/docs/graph-api/reference/event)).
- Compose a message using the data obtained from the 3 requests above and post it on the user's timeline.
- Get two photos from the user.

If the request was successful, the user should have a new status update similar to this:

```
My name is Foo User.

I like this page: Facebook Developers.

My next 2 events are House Warming Party,Some Foo Event.
```

It should also contain a response containing two photos from the user.

> **Warning:** The response object should return a `null` response for any request that was pointed to with JSONPath as is [the behaviour of the batch functionality of the Graph API](https://developers.facebook.com/docs/graph-api/making-multiple-requests/#operations). If we want to receive the response anyway we have to set the `omit_response_on_success` option to `false`. [See the example below](#force-response-example).

## Force Response Example

The following example is a subset of the [first example](#example). We will only use the `user-events` and `post-to-feed` requests of the [first example](#example), but in this case we will force the server to return the response of the `user-events` request.

```php
<?php
$fb = new Facebook\Facebook([
    'app_id' => '{app-id}',
    'app_secret' => '{app-secret}',
    'default_graph_version' => 'v2.10',
]);

// Since all the requests will be sent on behalf of the same user,
// we'll set the default fallback access token here.
$fb->setDefaultAccessToken('user-access-token');

// Get user events
$requestUserEvents = $fb->request('GET', '/me/events?fields=id,name&limit=2');

// Post a status update with reference to the user's events
$message = 'My next 2 events are {result=user-events:$.data.*.name}.';
$statusUpdate = ['message' => $message];
$requestPostToFeed = $fb->request('POST', '/me/feed', $statusUpdate);

// Create an empty batch request
$batch = $fb->newBatchRequest();

// Populate the batch request
// Set the 'omit_response_on_success' option to false to force the server return the response
$batch->add($requestUserEvents, [
    "name" => "user-events",
    "omit_response_on_success" => false
]);
$batch->add($requestPostToFeed, "post-to-feed");

// Send the batch request
try {
    $responses = $fb->getClient()->sendBatchRequest($batch);
} catch (Facebook\Exceptions\FacebookResponseException $e) {
    // When Graph returns an error
    echo 'Graph returned an error: ' . $e->getMessage();
    exit;
} catch (Facebook\Exceptions\FacebookSDKException $e) {
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

```

## Explicit Dependency Example

In the following example we will make two requests.
* One to post a status update on the user's feed
* and one to receive the last post of the user (which should be the one that we posted with first request).

Since we want the second request to be executed after the first one is completed, we have to set the `depends_on` option of the second request to point to the name of the first request. We assume that we have the following options granted from the user: `user_posts`, `publish_actions`.

```php
<?php
$fb = new Facebook\Facebook([
    'app_id' => '{app-id}',
    'app_secret' => '{app-secret}',
    'default_graph_version' => 'v2.10',
]);

// Since all the requests will be sent on behalf of the same user,
// we'll set the default fallback access token here.
$fb->setDefaultAccessToken('user-access-token');

// Post a status update to the user's feed
$message = 'Random status update';
$statusUpdate = ['message' => $message];
$requestPostToFeed = $fb->request('POST', '/me/feed', $statusUpdate);

// Get last post of the user
$requestLastPost = $fb->request('GET', '/me/feed?limit=1');

// Create an empty batch request
$batch = $fb->newBatchRequest();

// Populate the batch request
$batch->add($requestPostToFeed, "post-to-feed");

// Set the 'depends_on' property to point to the first request
$batch->add($requestLastPost, [
    "name" => "last-post",
    "depends_on" => "post-to-feed"
]);

// Send the batch request
try {
    $responses = $fb->getClient()->sendBatchRequest($batch);
} catch (Facebook\Exceptions\FacebookResponseException $e) {
    // When Graph returns an error
    echo 'Graph returned an error: ' . $e->getMessage();
    exit;
} catch (Facebook\Exceptions\FacebookSDKException $e) {
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
```

> **Warning:** The response object should return a `null` response for any request that was pointed to with the `depends_on` option as is [the behaviour of the batch functionality of the Graph API](https://developers.facebook.com/docs/graph-api/making-multiple-requests/#operations). If we want to receive the response anyway we have to set the `omit_response_on_success` option to `false`. [See example](#force-response-example).

## Multiple User Example

Since the requests sent in a batch are unrelated by default, we can make requests on behalf of multiple users and pages in the same batch request.

```php
<?php
$fb = new Facebook\Facebook([
    'app_id' => '{app-id}',
    'app_secret' => '{app-secret}',
    'default_graph_version' => 'v2.10',
]);

$batch = [
    $fb->request('GET', '/me?fields=id,name', 'user-access-token-one'),
    $fb->request('GET', '/me?fields=id,name', 'user-access-token-two'),
    $fb->request('GET', '/me?fields=id,name', 'page-access-token-one'),
    $fb->request('GET', '/me?fields=id,name', 'page-access-token-two'),
];

try {
    $responses = $fb->sendBatchRequest($batch);
} catch (Facebook\Exceptions\FacebookResponseException $e) {
    // When Graph returns an error
    echo 'Graph returned an error: ' . $e->getMessage();
    exit;
} catch (Facebook\Exceptions\FacebookSDKException $e) {
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
```
