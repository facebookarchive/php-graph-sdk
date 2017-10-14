# Video Upload Example

This example covers uploading & posting a video to a user's timeline with the Facebook SDK for PHP.

## Example

> **Warning:** Before you upload, check out the [video publishing options & requirements](https://developers.facebook.com/docs/graph-api/reference/video#publishing) for the specific video endpoint you want to publish to.

The following example will upload a video in chunks using the [resumable upload](https://developers.facebook.com/docs/graph-api/video-uploads#resumable) feature added in Graph v2.3.

```php
$fb = new Facebook\Facebook([
  'app_id' => '{app-id}',
  'app_secret' => '{app-secret}',
  'default_graph_version' => 'v2.10',
  ]);

$data = [
  'title' => 'My Foo Video',
  'description' => 'This video is full of foo and bar action.',
];

try {
  $response = $fb->uploadVideo('me', '/path/to/foo_bar.mp4', $data, '{user-access-token}');
} catch(Facebook\Exceptions\FacebookResponseException $e) {
  // When Graph returns an error
  echo 'Graph returned an error: ' . $e->getMessage();
  exit;
} catch(Facebook\Exceptions\FacebookSDKException $e) {
  // When validation fails or other local issues
  echo 'Facebook SDK returned an error: ' . $e->getMessage();
  exit;
}

echo 'Video ID: ' . $response['video_id'];
```

See more about the [`uploadVideo()` method](../reference/Facebook.md#uploadvideo).

For versions of Graph before v2.3, videos had to be uploaded in one request.

```php
$fb = new Facebook\Facebook([/* . . . */]);

$data = [
  'title' => 'My Foo Video',
  'description' => 'This video is full of foo and bar action.',
  'source' => $fb->videoToUpload('/path/to/foo_bar.mp4'),
];

try {
  $response = $fb->post('/me/videos', $data, 'user-access-token');
} catch(Facebook\Exceptions\FacebookResponseException $e) {
  // When Graph returns an error
  echo 'Graph returned an error: ' . $e->getMessage();
  exit;
} catch(Facebook\Exceptions\FacebookSDKException $e) {
  // When validation fails or other local issues
  echo 'Facebook SDK returned an error: ' . $e->getMessage();
  exit;
}

$graphNode = $response->getGraphNode();

echo 'Video ID: ' . $graphNode['id'];
```
