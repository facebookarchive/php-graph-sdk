# Upload Photos to a User's Profile

This example covers uploading a photo to the current User's profile using the Graph API and the Facebook SDK for PHP.

It assumes that you've already acquired an access token using one of the helper classes found [here](../reference.md).  The access token must have the `publish_actions` permission for this to work.

For more information, see the documentation for [`Facebook\Facebook`](../reference/Facebook.md), [`Facebook\FileUpload\File`](../reference/File.md), [`Facebook\Response`](../reference/Response.md), [`Facebook\GraphNode\GraphNode`](../reference/GraphNode.md), [`Facebook\Exception\SDKException`](../reference/SDKException.md) and [`Facebook\Exception\ResponseException`](../reference/ResponseException.md).

## Example

```php
$fb = new Facebook\Facebook([
  'app_id' => '{app-id}',
  'app_secret' => '{app-secret}',
  'default_graph_version' => 'v2.10',
  ]);

$data = [
  'message' => 'My awesome photo upload example.',
  'source' => $fb->fileToUpload('/path/to/photo.jpg'),
];

try {
  // Returns a `Facebook\Response` object
  $response = $fb->post('/me/photos', $data, '{access-token}');
} catch(Facebook\Exception\ResponseException $e) {
  echo 'Graph returned an error: ' . $e->getMessage();
  exit;
} catch(Facebook\Exception\SDKException $e) {
  echo 'Facebook SDK returned an error: ' . $e->getMessage();
  exit;
}

$graphNode = $response->getGraphNode();

echo 'Photo ID: ' . $graphNode['id'];
```

Note that the `message` field must come from the user, as pre-filled content is forbidden by the [Platform Policies](https://developers.intern.facebook.com/policy/#control) (2.3).
