# Post Links Using the Graph API

This example covers posting a link to the current user's timeline using the Graph API and Facebook SDK for PHP.

It assumes that you've already obtained an access token from one of the helpers found [here](../reference.md). The access token must have the `publish_actions` permission for this to work.

For more information, see the documentation for [`Facebook\Facebook`](../reference/Facebook.md), [`Facebook\FacebookResponse`](../reference/FacebookResponse.md), [`Facebook\GraphNodes\GraphNode`](../reference/GraphNode.md), [`Facebook\Exceptions\FacebookSDKException`](../reference/FacebookSDKException.md) and [`Facebook\Exceptions\FacebookResponseException`](../reference/FacebookResponseException.md).

## Example

```php
$fb = new Facebook\Facebook([
  'app_id' => '{app-id}',
  'app_secret' => '{app-secret}',
  'default_graph_version' => 'v2.10',
  ]);

$linkData = [
  'link' => 'http://www.example.com',
  'message' => 'User provided message',
  ];

try {
  // Returns a `Facebook\FacebookResponse` object
  $response = $fb->post('/me/feed', $linkData, '{access-token}');
} catch(Facebook\Exceptions\FacebookResponseException $e) {
  echo 'Graph returned an error: ' . $e->getMessage();
  exit;
} catch(Facebook\Exceptions\FacebookSDKException $e) {
  echo 'Facebook SDK returned an error: ' . $e->getMessage();
  exit;
}

$graphNode = $response->getGraphNode();

echo 'Posted with id: ' . $graphNode['id'];
```

Note that the 'message' field must come from the user, as pre-filled content is forbidden by the [Platform Policies](https://developers.intern.facebook.com/policy/#control) (2.3).
