# Facebook service class for the Facebook SDK for PHP

The Facebook SDK for PHP is made up of many components. The `Facebook\Facebook` service class provides an easy interface for working with all the components of the SDK.

## Facebook\Facebook

To instantiate a new `Facebook\Facebook` service, pass an array of configuration options to the constructor.

```php
$fb = new Facebook\Facebook([
  'app_id' => '{app-id}',
  'app_secret' => '{app-secret}',
  'default_graph_version' => 'v2.10',
  // . . .
  ]);
```

Usage:

```php
// Send a GET request
$response = $fb->get('/me');

// Send a POST request
$response = $fb->post('/me/feed', ['message' => 'Foo message']);

// Send a DELETE request
$response = $fb->delete('/{node-id}');
```

If you don't provide a `default_access_token` in the configuration options, or you wish to use a different access token than the default, you can explicitly pass the access token as an argument to the `get()`, `post()`, and `delete()` methods.

```php
$res = $fb->get('/me', '{access-token}');
$res = $fb->post('/me/feed', ['foo' => 'bar'], '{access-token}');
$res = $fb->delete('/{node-id}', '{access-token}');
```

## Configuration options

Although the `Facebook\Facebook` service tries to make the SDK as easy as possible to use, it also makes it easy to customize with configuration options.

Full configuration options list:

```php
$fb = new Facebook\Facebook([
  'app_id' => '{app-id}',
  'app_secret' => '{app-secret}',
  'default_access_token' => '{access-token}',
  'enable_beta_mode' => true,
  'default_graph_version' => 'v2.10',
  'http_client_handler' => 'guzzle',
  'persistent_data_handler' => 'memory',
  'url_detection_handler' => new MyUrlDetectionHandler(),
  'pseudo_random_string_generator' => new MyPseudoRandomStringGenerator(),
]);
```

### `app_id`
The ID of your Facebook app (required).

### `app_secret`
The secret of your Facebook app (required).

### `default_access_token`
The default fallback access token to use if one is not explicitly provided. The value can be of type `string` or `Facebook\AccessToken`. If any other value is provided an `InvalidArgumentException` will be thrown. Defaults to `null`.

### `enable_beta_mode`
Enable [beta mode](https://developers.facebook.com/docs/apps/beta-tier) so that request are made to the [https://graph.beta.facebook.com](https://graph.beta.facebook.com/) endpoint. Set to boolean `true` to enable or `false` to disable. Defaults to `false`.

### `default_graph_version`
Allows you to overwrite the default Graph version number set in `Facebook\Facebook::DEFAULT_GRAPH_VERSION`. Set this as a string as it would appear in the Graph url, e.g. `v2.10`. Defaults to the [latest version of Graph](https://developers.facebook.com/docs/apps/changelog).

### `http_client_handler`
Allows you to overwrite the default HTTP client.

By default, the SDK will try to use cURL as the HTTP client. If a cURL implementation cannot be found, it will fallback to a stream wrapper HTTP client. You can force either HTTP client implementations by setting this value to `curl` or `stream`.

If you wish to use Guzzle, you can set this value to `guzzle`, but it requires that you [install Guzzle](http://docs.guzzlephp.org/en/latest/) with composer.

If you wish to write your own HTTP client, you can code your HTTP client to the `Facebook\HttpClients\FacebookHttpClientInterface` and set this value to an instance of your custom client.

```php
$fb = new Facebook([
  'http_client_handler' => new MyCustomHttpClient(),
]);
```

If any other value is provided an `InvalidArgumentException` will be thrown.

### `persistent_data_handler`
Allows you to overwrite the default persistent data store.

By default, the SDK will try to use the native PHP session for the persistent data store. There is also an in-memory persistent data handler which is useful when running your script from the command line for example. You can force either implementation by setting this value to `session` or `memory`.

If you wish to write your own persistent data handler, you can code your persistent data handler to the [`Facebook\PersistentData\PersistentDataInterface`](PersistentDataInterface.md) and set the value of `persistent_data_handler` to an instance of your custom handler.

```php
$fb = new Facebook([
  'persistent_data_handler' => new MyCustomPersistentDataHandler(),
]);
```

If any other value is provided an `InvalidArgumentException` will be thrown.

### `url_detection_handler`
Allows you to overwrite the default URL detection logic.

The SDK will do its best to detect the proper current URL but this can sometimes get tricky if you have a very customized environment. You can write your own URL detection logic that implements the ['Facebook\Url\UrlDetectionInterface'](UrlDetectionInterface.md)` and set the value of `url_detection_handler` to an instance of your custom URL detector.

```php
$fb = new Facebook([
  'url_detection_handler' => new MyUrlDetectionHandler(),
]);
```

If any other value is provided an `InvalidArgumentException` will be thrown.

### `pseudo_random_string_generator`
Allows you to overwrite the default cryptographically secure pseudo-random string generator.

Generating random strings in PHP is easy but generating _cryptographically secure_ random strings is another matter. By default the SDK will attempt to detect a suitable to cryptographically secure random string generator for you. If a cryptographically secure method cannot be detected, a `Facebook\Exceptions\FacebookSDKException` will be thrown.

You can force a specific implementation of the CSPRSG's provided in the SDK by setting `pseudo_random_string_generator` to one of the following methods: `mcrypt`, `openssl` and `urandom`.

```php
$fb = new Facebook([
  'pseudo_random_string_generator' => 'openssl',
]);
```

You can write your own CSPRSG that implements the [`Facebook\PseudoRandomString\PseudoRandomStringGeneratorInterface`](PseudoRandomStringGeneratorInterface.md) and set the value of `pseudo_random_string_generator` to an instance of your custom generator.

```php
$fb = new Facebook([
  'pseudo_random_string_generator' => new MyPseudoRandomStringGenerator(),
]);
```

If any other value is provided an `InvalidArgumentException` will be thrown.

## Environment variables fallback

The only required configuration options are `app_id` and `app_secret`. However, the SDK will look to environment variables for the app ID and app secret.

To take advantage of this feature, simply set an environment variable named `FACEBOOK_APP_ID` with your Facebook app ID and set an environment variable named `FACEBOOK_APP_SECRET` with your Facebook app secret and you will be able to instantiate the `Facebook\Facebook` service without setting any configuration in the constructor.

```php
$fb = new Facebook\Facebook();
```

# Instance Methods

## getApp()
```php
public FacebookApp getApp()
```
Returns the instance of `Facebook\FacebookApp` for the instantiated service.

## getClient()
```php
public Facebook\FacebookClient getClient()
```
Returns the instance of [`Facebook\FacebookClient`](FacebookClient.md) for the instantiated service.

## getOAuth2Client()
```php
public Facebook\Authentication\OAuth2Client getOAuth2Client()
```
Returns an instance of `Facebook\Authentication\OAuth2Client`.

## getLastResponse()
```php
public Facebook\FacebookResponse|Facebook\FacebookBatchResponse|null getLastResponse()
```
Returns the last response received from the Graph API in the form of a `Facebook\FacebookResponse` or `Facebook\FacebookBatchResponse`.

## getUrlDetectionHandler()
```php
public Facebook\Url\UrlDetectionInterface getUrlDetectionHandler()
```
Returns an instance of [`Facebook\Url\UrlDetectionInterface`](UrlDetectionInterface.md).

## getDefaultAccessToken()
```php
public Facebook\Authentication\AccessToken|null getDefaultAccessToken()
```
Returns the default fallback [`AccessToken`](AccessToken.md) entity that is being used with every request to Graph. This value can be set with the configuration option `default_access_token` or by using `setDefaultAccessToken()`.

## setDefaultAccessToken()
```php
public setDefaultAccessToken(string|Facebook\AccessToken $accessToken)
```
Sets the default fallback access token to be use with all requests sent to Graph. The access token can be a string or an instance of [`AccessToken`](AccessToken.md).

```php
$fb->setDefaultAccessToken('{my-access-token}');

// . . . OR . . .

$accessToken = new Facebook\Authentication\AccessToken('{my-access-token}');
$fb->setDefaultAccessToken($accessToken);
```

This setting will overwrite the value from `default_access_token` option if it was passed to the `Facebook\Facebook` constructor.

## getDefaultGraphVersion()
```php
public string getDefaultGraphVersion()
```
Returns the default version of Graph. If the `default_graph_version` configuration option was not set, this will default to `Facebook\Facebook::DEFAULT_GRAPH_VERSION`.

## get()
```php
public Facebook\FacebookResponse get(
  string $endpoint,
  string|AccessToken|null $accessToken,
  string|null $eTag,
  string|null $graphVersion
)
```

Sends a GET request to Graph and returns a `Facebook\FacebookResponse`.

`$endpoint`
The url to send to Graph without the version prefix (required).

```php
$fb->get('/me');
```

`$accessToken`
The access token (as a string or `AccessToken` entity) to use for the request. If none is provided, the SDK will assume the value from the `default_access_token` configuration option if it was set.

`$eTag`
[Graph supports eTags](https://developers.facebook.com/docs/marketing-api/etags). Set this to the eTag from a previous request to get a `304 Not Modified` response if the data has not changed.

`$graphVersion`
This will overwrite the Graph version that was set in the `default_graph_version` configuration option.

## post()
```php
public Facebook\FacebookResponse post(
  string $endpoint,
  array $params,
  string|AccessToken|null $accessToken,
  string|null $eTag,
  string|null $graphVersion
)
```

Sends a POST request to Graph and returns a `Facebook\FacebookResponse`.

The arguments are the same as `get()` above with the exception of `$params`.

`$params`
The associative array of params you want to send in the body of the POST request.

```php
$response = $fb->post('/me/feed', ['message' => 'Foo message']);
```

## delete()
```php
public Facebook\FacebookResponse delete(
  string $endpoint,
  array $params,
  string|AccessToken|null $accessToken,
  string|null $eTag,
  string|null $graphVersion
)
```

Sends a DELETE request to Graph and returns a `Facebook\FacebookResponse`.

The arguments are the same as `post()` above.

```php
$response = $fb->delete('/{node-id}', ['object' => '1234']);
```

## request()
```php
public Facebook\FacebookRequest request(
  string $method,
  string $endpoint,
  array $params,
  string|AccessToken|null $accessToken,
  string|null $eTag,
  string|null $graphVersion
)
```

Instantiates a new `Facebook\FacebookRequest` entity **but does not send the request to Graph**. This is useful for creating a number of requests to be sent later in a batch request (see `sendBatchRequest()` below).

The arguments are the same as `post()` above with the exception of `$method`.

`$method`
The HTTP request verb to use for this request. This can be set to any verb that the `$graphVersion` of Graph supports, e.g. `GET`, `POST`, `DELETE`, etc.

```php
$request = $fb->request('GET', '/{node-id}');
```

## sendRequest()
```php
public Facebook\FacebookResponse sendRequest(
  string $method,
  string $endpoint,
  array $params,
  string|AccessToken|null $accessToken,
  string|null $eTag,
  string|null $graphVersion
)
```

Sends a request to the Graph API.

```php
$response = $fb->sendRequest('GET', '/me', [], '{access-token}', 'eTag', 'v2.10');
```

## sendBatchRequest()
```php
public Facebook\FacebookBatchResponse sendBatchRequest(
  array $requests,
  string|AccessToken|null $accessToken,
  string|null $graphVersion
)
```

Sends an array of `Facebook\FacebookRequest` entities as a batch request to Graph.

The `$accessToken` and `$graphVersion` arguments are the same as `get()` above.

`$requests`
An array of `Facebook\FacebookRequest` entities. This can be a numeric or associative array but every value of the array has to be an instance of `Facebook\FacebookRequest`.

If the requests are sent as an associative array, the key will be used as the `name` of the request so that it can be referenced by another request. See more on [batch request naming and using JSONPath](https://developers.facebook.com/docs/graph-api/making-multiple-requests).

```php
$requests = [
  'me' => $fb->request('GET', '/me'),
  'you' => $fb->request('GET', '/1337', [], '{user-b-access-token}'),
  'my_post' => $fb->request('POST', '/1337/feed', ['message' => 'Hi!']),
];
$batchResponse = $fb->sendBatchRequest($requests);
```

[See a full batch example](../examples/batch_request.md).

## newBatchRequest()
```php
public Facebook\FacebookBatchRequest newBatchRequest(
    string|AccessToken|null $accessToken,
    string|null $graphVersion
)
```

Instantiates an empty `Facebook\FacebookBatchRequest`.
To populate it use the [`Facebook\FacebookBatchRequest::add()`](FacebookBatchRequest.md#add) method.

The `$accessToken` and `$graphVersion` arguments are the same as `get()` above.
If any of the requests contained in the batch request does not have either the `$accessToken` or the `$graphVersion` set,
it fallbacks to the values provided in the instantiation of the batch request.

[See a full batch example](../examples/batch_request.md).

## getRedirectLoginHelper()
```php
public Facebook\Helpers\FacebookRedirectLoginHelper getRedirectLoginHelper()
```

Returns a [`Facebook\Helpers\FacebookRedirectLoginHelper`](FacebookRedirectLoginHelper.md) which is used to generate a "Login with Facebook" link and obtain an access token from a redirect.

```php
$helper = $fb->getRedirectLoginHelper();
```

## getJavaScriptHelper()
```php
public Facebook\Helpers\FacebookJavaScriptHelper getJavaScriptHelper()
```

Returns a [`Facebook\Helpers\FacebookJavaScriptHelper`](FacebookJavaScriptHelper.md) which is used to access the signed request stored in the cookie set by the SDK for JavaScript.

```php
$helper = $fb->getJavaScriptHelper();
```

## getCanvasHelper()
```php
public Facebook\Helpers\FacebookCanvasHelper getCanvasHelper()
```

Returns a [`Facebook\Helpers\FacebookCanvasHelper`](FacebookCanvasHelper.md) which is used to access the signed request that is `POST`ed to canvas apps.

```php
$helper = $fb->getCanvasHelper();
```

## getPageTabHelper()
```php
public Facebook\Helpers\FacebookPageTabHelper getPageTabHelper()
```

Returns a [`Facebook\Helpers\FacebookPageTabHelper`](FacebookPageTabHelper.md) which is used to access the signed request that is `POST`ed to canvas apps and provides a number of helper methods useful for apps living in a page tab context.

```php
$helper = $fb->getPageTabHelper();
```

## next()
```php
public Facebook\GraphNodes\GraphEdge|null next(Facebook\GraphNodes\GraphEdge $graphEdge)
```

Requests and returns the next page of results in a [`Facebook\GraphNodes\GraphEdge`](GraphEdge.md) collection. If the next page returns no results, `null` will be returned.

```php
// Iterate over 5 pages max
$maxPages = 5;

// Get a list of photo nodes from the /photos edge
$response = $fb->get('/me/photos?fields=id,source,likes&limit=5');

$photosEdge = $response->getGraphEdge();

if (count($photosEdge) > 0) {
  $pageCount = 0;
  do {
    foreach ($photosEdge as $photo) {
      var_dump($photo->asArray());

      // Deep pagination is supported on child GraphEdge's
      $likes = $photo['likes'];
      do {
        echo '<p>Likes:</p>' . "\n\n";
        var_dump($likes->asArray());
      } while ($likes = $fb->next($likes));
    }
    $pageCount++;
  } while ($pageCount < $maxPages && $photosEdge = $fb->next($photosEdge));
}
```

## previous()
```php
public Facebook\GraphNodes\GraphEdge|null previous(Facebook\GraphNodes\GraphEdge $graphEdge)
```

Requests and returns the previous page of results in a `Facebook\GraphNodes\GraphEdge` collection. Functions just like `next()` above, but in the opposite direction of pagination.

## fileToUpload()
```php
public Facebook\FileUpload\FacebookFile fileToUpload(string $pathToFile)
```

When a valid path to a local or remote file is provided, `fileToUpload()` will returns a `FacebookFile` entity that can be used in the params in a POST request to Graph.

```php
// Upload a photo for a user
$data = [
  'message' => 'A neat photo upload example. Neat.',
  'source' => $fb->fileToUpload('/path/to/photo.jpg'),
];

try {
  $response = $fb->post('/me/photos', $data);
} catch(FacebookSDKException $e) {
  echo 'Error: ' . $e->getMessage();
  exit;
}

$graphNode = $response->getGraphNode();

echo 'Photo ID: ' . $graphNode['id'];
```

## videoToUpload()
```php
public Facebook\FileUpload\FacebookVideo videoToUpload(string $pathToVideoFile)
```

Uploading videos to Graph requires that you send the request to `https://graph-video.facebook.com` instead of the normal `https://graph.facebook.com` host name. When you use `videoToUpload()` to upload a video, the SDK for PHP will automatically point the request to the `graph-video.facebook.com` host name for you.

```php
// Upload a video for a user
$data = [
  'title' => 'My awesome video',
  'description' => 'More info about my awesome video.',
  'source' => $fb->videoToUpload('/path/to/video.mp4'),
];

try {
  $response = $fb->post('/me/videos', $data);
} catch(FacebookSDKException $e) {
  echo 'Error: ' . $e->getMessage();
  exit;
}

$graphNode = $response->getGraphNode();

echo 'Video ID: ' . $graphNode['id'];
```

## uploadVideo()
```php
public array videoToUpload(
  string $target,
  string $pathToFile,
  array $metadata = [],
  string|Facebook\AccessToken $accessToken = null,
  int $maxTransferTries = 5,
  string $graphVersion = null
  )
```

Functionality to [upload video files in chunks](https://developers.facebook.com/docs/graph-api/video-uploads#resumable) was added to the Graph API in v2.3. The `uploadVideo()` method provides an easy API to take advantage of this new feature.

### Parameters

`$target`
The ID or alias of the target node. This can be a user ID, page ID, event ID, group ID or `me`.

`$pathToFile`
The absolute or relative path to the video file to upload.

`$metadata`
All the metadata associated with the [Video node](https://developers.facebook.com/docs/graph-api/reference/video).

`$accessToken`
The access token to use for this request. Falls back to the default access token if one exists.

`$maxTransferTries`
During the [transfer phase](https://developers.facebook.com/docs/graph-api/video-uploads#transfer) an upload can fail for a number of reasons. If the Graph API responds with an error that is resumable, the PHP SDK will retry uploading the chunk automatically. By default the PHP SDK will try to upload each chunk five times before throwing a `FacebookResponseException`.

`$graphVersion`
The version of the Graph API to use. The resumable upload feature did not become available until Graph v2.3.

### Return Value

The array that is returned will contain two keys; `video_id` with the ID of the video node, and `success` with a boolean value that represents a successful or failed transfer.

### Example

```php
// Upload a video for a user (chunked)
$data = [
  'title' => 'My awesome video',
  'description' => 'More info about my awesome video.',
];

try {
  $response = $fb->uploadVideo('me', '/path/to/video.mp4', $data, '{user-access-token}');
} catch(Facebook\Exceptions\FacebookSDKException $e) {
  echo 'Error: ' . $e->getMessage();
  exit;
}

echo 'Video ID: ' . $response['video_id'];
```
