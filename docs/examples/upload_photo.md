# Upload Photos to a User's Profile

This example covers uploading a photo to the current User's profile using the Graph API and the Facebook SDK for PHP.

It assumes that you've already acquired an access token using one of the helper classes found [here](/docs/reference.md#helpers).  The access token must have the `publish_actions` permission for this to work.

For more information, see the documentation for [`Facebook\Facebook`](/docs/reference/Facebook.md), [`Facebook\FileUpload\FacebookFile`](/docs/reference/FacebookFile.md), [`Facebook\FacebookResponse`](/docs/reference/FacebookResponse.md), [`Facebook\GraphNodes\GraphNode`](/docs/reference/GraphNode.md), [`Facebook\Exceptions\FacebookSDKException`](/docs/reference/FacebookSDKException.md) and [`Facebook\Exceptions\FacebookResponseException`](/docs/reference/FacebookResponseException.md).

## Example

```php
$fb = new Facebook\Facebook([
  'app_id' => '{app-id}',
  'app_secret' => '{app-secret}',
  'default_graph_version' => 'v2.6',
  ]);

$data = [
  'message' => 'My awesome photo upload example.',
  'source' => $fb->fileToUpload('/path/to/photo.jpg'),
];

try {
  // Returns a `Facebook\FacebookResponse` object
  $response = $fb->post('/me/photos', $data, '{access-token}');
} catch(Facebook\Exceptions\FacebookResponseException $e) {
  echo 'Graph returned an error: ' . $e->getMessage();
  exit;
} catch(Facebook\Exceptions\FacebookSDKException $e) {
  echo 'Facebook SDK returned an error: ' . $e->getMessage();
  exit;
}

$graphNode = $response->getGraphNode();

echo 'Photo ID: ' . $graphNode['id'];
```

Note that the `message` field must come from the user, as pre-filled content is forbidden by the [Platform Policies](https://developers.intern.facebook.com/policy/#control) (2.3).
