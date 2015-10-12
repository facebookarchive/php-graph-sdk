<card>
# Batch File Upload Example

This example covers uploading files in a batch request with the Facebook SDK for PHP.
</card>

<card>
## Example {#example}

The Graph API supports [file uploads in batch requests](https://developers.facebook.com/docs/graph-api/making-multiple-requests#binary) and the Facebook PHP SDK does all the heavy lifting to make it super easy to upload photos and videos in a batch request.

The following example will upload two photos and one video.

~~~~
$fb = new Facebook\Facebook([
  'app_id' => '{app-id}',
  'app_secret' => '{app-secret}',
  'default_graph_version' => 'v2.5',
  ]);

// Since all the requests will be sent on behalf of the same user,
// we'll set the default fallback access token here.
$fb->setDefaultAccessToken('user-access-token');

$batch = [
  'photo-one' => $fb->request('POST', '/me/photos', [
      'message' => 'Foo photo',
      'source' => $fb->fileToUpload('/path/to/photo-one.jpg'),
    ]),
  'photo-two' => $fb->request('POST', '/me/photos', [
      'message' => 'Bar photo',
      'source' => $fb->fileToUpload('/path/to/photo-two.jpg'),
    ]),
  'video-one' => $fb->request('POST', '/me/videos', [
      'title' => 'Baz video',
      'description' => 'My neat baz video',
      'source' => $fb->videoToUpload('/path/to/video-one.mp4'),
    ]),
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
